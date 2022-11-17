<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Dto\Exception\PaginatorDtoException;
use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Entity\User;
use App\Form\CommentFormType;
use App\Helper\FlashType;
use App\Repository\MicroPostRepository;
use App\Repository\UserRepository;
use App\Security\Voter\MicroPostVoter;
use App\Service\MicroPost\GetMicroPostCommentsServiceInterface;
use App\Service\MicroPost\GetMicroPostsServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/micro-post/{_locale<%app.supported_locales%>}")
 */
class MicroPostController extends AbstractController
{
    private $microPostRepository;
    private $em;
    private $userRepository;
    private $translator;

    public function __construct(
        MicroPostRepository    $microPostRepository,
        EntityManagerInterface $em,
        UserRepository         $userRepository,
        TranslatorInterface    $translator)
    {
        $this->microPostRepository = $microPostRepository;
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="micro_post_list", methods={"get"})
     */
    public function index(Request $request, GetMicroPostsServiceInterface $getMicroPostsService): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        $followUser = [];
        $page = (int)$request->get('page', 1);

        if ($this->getUser() instanceof User) {
            try {
                $microPostWithPaginationDto = $getMicroPostsService->findFollowingMicroPosts($currentUser, $page);
            } catch (PaginatorDtoException $exception) {
                throw new UnprocessableEntityHttpException($exception->getMessage());
            }

            if (0 === count($microPostWithPaginationDto->getPosts())) {
                $followUser = $this->userRepository->getUsersWhoHaveMoreThen5PostsExcludeUser($currentUser);
            }
        } else {
            try {
                $microPostWithPaginationDto = $getMicroPostsService->findLastMicroPosts($page);
            } catch (PaginatorDtoException $exception) {
                throw new UnprocessableEntityHttpException($exception->getMessage());
            }
        }

        return $this->render('@mp/list.html.twig', compact('microPostWithPaginationDto', 'followUser'));
    }

    /**
     * @Route("/add", name="micro_post_add", methods={"get", "post"})
     * @IsGranted(User::ROLE_USER)
     */
    public function add(Request $request)
    {
        $microPost = new MicroPost();
        $form = $this->formMicroPost($microPost);
        $form->handleRequest($request);
        $statusCode = Response::HTTP_OK;

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            } else {
                /** @var MicroPost $microPost */
                $microPost = $form->getData();
                // Or inject TokenStorageInterface $tokenStorage and use it for fetch user
                $microPost->setUser($this->getUser());

                $this->em->persist($microPost);
                $this->em->flush();

                $partOfMessage = $this->translator->trans('micro-post.form_edit_add_del.message.add');
                $message = $this->flashMessageWithPartOfContent($partOfMessage, $microPost);
                $this->addFlash(FlashType::SUCCESS, $message);

                return $this->redirectToRoute('micro_post_by_user', ['uuid' => $this->getUser()->getUUid()]);
            }
        }

        return $this->renderForm('@mp/add.html.twig', ['form' => $form])
            ->setStatusCode($statusCode);
    }

    /**
     * @Route("/edit/{uuid}", name="micro_post_edit", methods={"get", "post"})
     */
    public function edit(Request $request, ?MicroPost $microPost = null)
    {
        if (null === $microPost) {
            throw new NotFoundHttpException($this->translator->trans('micro-post.form_edit_add_del.message.not_found'));
        }

        $this->denyAccessUnlessGranted(
            MicroPostVoter::EDIT_DEL_OWNER_OR_ADMIN,
            $microPost,
            $this->translator->trans('micro-post.form_edit_add_del.message.edit_owner')
        );

        $form = $this->formMicroPost($microPost);
        $microPost->setDate(new \DateTime());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $microPost = $form->getData();
            $this->em->persist($microPost);
            $this->em->flush();

            $partOfMessage = $this->translator->trans('micro-post.form_edit_add_del.message.update');
            $message = $this->flashMessageWithPartOfContent($partOfMessage, $microPost);
            $this->addFlash(FlashType::INFO, $message);

            return $this->redirectToRoute('micro_post_view', ['uuid' => $microPost->getUUid()]);
        }

        return $this->renderForm('@mp/add.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/del/{uuid}", name="micro_post_del", methods={"get"})
     */
    public function del(?MicroPost $microPost = null): RedirectResponse
    {
        if (null === $microPost) {
            throw new NotFoundHttpException($this->translator->trans('micro-post.form_edit_add_del.message.not_found'));
        }

        $this->denyAccessUnlessGranted(
            MicroPostVoter::EDIT_DEL_OWNER_OR_ADMIN,
            $microPost,
            $this->translator->trans('micro-post.form_edit_add_del.message.del_owner')
        );

        $this->microPostRepository->remove($microPost, true);

        $partOfMessage = $this->translator->trans('micro-post.form_edit_add_del.message.del');
        $message = $this->flashMessageWithPartOfContent($partOfMessage, $microPost);
        $this->addFlash(FlashType::SUCCESS, $message);

        return $this->redirectToRoute('micro_post_list');
    }

    /**
     * @Route("/view/{uuid}", name="micro_post_view", methods={"get", "post"})
     * @return Response|RedirectResponse
     */
    public function view(Request $request, GetMicroPostCommentsServiceInterface $getMicroPostCommentsService, ?MicroPost $microPost = null)
    {
        if ($microPost) {
            $statusCode = Response::HTTP_OK;
            $form = null;

            if ($this->getUser() instanceof User) {
                $comment = (new Comment())->setPost($microPost)->setUser($this->getUser());
                $form = $this->createForm(CommentFormType::class, $comment);
                $form->handleRequest($request);

                if ($form->isSubmitted()) {
                    if ($form->isValid()) {
                        $this->em->persist($comment);
                        $this->em->flush();
                        // TODO translate it
                        $this->addFlash(FlashType::SUCCESS, 'Спасибо! Ваш комментарий добавлен');

                        return $this->redirectToRoute('micro_post_view', ['uuid' => $microPost->getUuid()]);
                    } else {
                        $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
                    }
                }
            }

            $page = (int)$request->get('page', 1);
            $commentsWithPaginatorDto = $getMicroPostCommentsService->getComments($page, $microPost);

            return $this->renderForm('@mp/view.html.twig', [
                'post' => $microPost,
                'form' => $form,
                'commentsWithPaginatorDto' => $commentsWithPaginatorDto
            ])
                ->setStatusCode($statusCode);
        }

        throw new NotFoundHttpException($this->translator->trans('micro-post.form_edit_add_del.message.not_found'));
    }

    /**
     * @Route("/user/{uuid}", name="micro_post_by_user")
     */
    public function getPostByUser(Request $request, GetMicroPostsServiceInterface $getMicroPostsService, ?User $user = null): Response
    {
        if (null === $user) {
            throw new NotFoundHttpException('User not found');
        }

        $page = (int)$request->get('page', 1);
        try {
            $microPostWithPaginationDto = $getMicroPostsService->findMicroPostsByUser($user, $page);
        } catch (PaginatorDtoException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        return $this->render('@mp/user-posts.html.twig', compact('microPostWithPaginationDto', 'user'));
    }

    private function formMicroPost(MicroPost $microPost): FormInterface
    {
        $fieldContentLength = $this->em
            ->getClassMetadata(MicroPost::class)
            ->fieldMappings['content']['length'] ?? 200;

        return $this->createFormBuilder($microPost)
            ->add('content', TextareaType::class, [
                'label' => 'micro-post.form_edit_add_del.content',
                'attr' => ['rows' => 5, 'maxlength' => $fieldContentLength]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'micro-post.form_edit_add_del.button_submit'
            ])
            ->getForm();
    }

    private function flashMessageWithPartOfContent(string $mainPart, MicroPost $microPost): string
    {
        return sprintf('%s. [%s...]', $mainPart, mb_substr($microPost->getContent(), 0, 75));
    }
}
