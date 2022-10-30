<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Helper\FlashType;
use App\Repository\MicroPostRepository;
use App\Repository\UserRepository;
use App\Security\Voter\MicroPostVoter;
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
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/micro-post")
 */
class MicroPostController extends AbstractController
{
    private $microPostRepository;
    private $em;
    private $userRepository;

    public function __construct(
        MicroPostRepository    $microPostRepository,
        EntityManagerInterface $em,
        UserRepository         $userRepository)
    {
        $this->microPostRepository = $microPostRepository;
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/", name="micro_post_list", methods={"get"})
     */
    public function index(): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        $followUser = [];

        if ($this->getUser() instanceof User) {
            $posts = $this->microPostRepository->findAllByUsers($currentUser->getFollowing());

            if (0 === count($posts)) {
                $followUser = $this->userRepository->getUsersWhoHaveMoreThen5PostsExcludeUser($currentUser);
            }
        } else {
            $posts = $this->microPostRepository->findBy([], ['date' => 'desc']);
        }

        return $this->render('micro-post/list.html.twig', compact('posts', 'followUser'));
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

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var MicroPost $microPost */
            $microPost = $form->getData();
            // Or inject TokenStorageInterface $tokenStorage and use it for fetch user
            $microPost->setUser($this->getUser());

            $this->em->persist($microPost);
            $this->em->flush();

            $message = $this->flashMessageWithPartOfContent('The micro post was added', $microPost);
            $this->addFlash(FlashType::SUCCESS, $message);

            return $this->redirectToRoute('micro_post_by_user', ['uuid' => $this->getUser()->getUUid()]);
        }

        return $this->renderForm('micro-post/add.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/edit/{uuid}", name="micro_post_edit", methods={"get", "post"})
     * @IsGranted(
     *     MicroPostVoter::EDIT_DEL_OWNER_OR_ADMIN,
     *     subject="microPost",
     *     message="Only the owner can edit a post."
     * )
     */
    public function edit(Request $request, ?MicroPost $microPost = null)
    {
        if (null === $microPost) {
            throw new NotFoundHttpException('Micro post not found for editing');
        }

        $form = $this->formMicroPost($microPost);
        $microPost->setDate(new \DateTime());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $microPost = $form->getData();
            $this->em->persist($microPost);
            $this->em->flush();

            $message = $this->flashMessageWithPartOfContent('The micro post was updated', $microPost);
            $this->addFlash(FlashType::INFO, $message);

            return $this->redirectToRoute('micro_post_view', ['uuid' => $microPost->getUUid()]);
        }

        return $this->renderForm('micro-post/add.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/del/{uuid}", name="micro_post_del", methods={"get"})
     * Granted access control in file security.yaml in section "access_control"
     */
    public function del(?MicroPost $microPost = null): RedirectResponse
    {
        if (null === $microPost) {
            throw new NotFoundHttpException('Micro post not found for deleting');
        }

        $this->denyAccessUnlessGranted(
            MicroPostVoter::EDIT_DEL_OWNER_OR_ADMIN,
            $microPost,
            'Only the owner can delete a post'
        );

        $this->microPostRepository->remove($microPost, true);

        $message = $this->flashMessageWithPartOfContent('The micro post was deleted', $microPost);
        $this->addFlash(FlashType::SUCCESS, $message);

        return $this->redirectToRoute('micro_post_list');
    }

    /**
     * @Route("/view/{uuid}", name="micro_post_view", methods={"get"})
     */
    public function view(?MicroPost $microPost = null): Response
    {
        if ($microPost) {
            return $this->render('micro-post/view.html.twig', ['post' => $microPost]);
        }

        throw new NotFoundHttpException('Micro post not found');
    }

    /**
     * @Route("/user/{uuid}", name="micro_post_by_user")
     */
    public function getPostByUser(?User $user = null): Response
    {
        if (null === $user) {
            throw new NotFoundHttpException('User not found');
        }

        $posts = $user->getPosts();

        return $this->render('micro-post/user-posts.html.twig', [
            'posts' => $posts,
            'user' => $user,
        ]);
    }

    private function formMicroPost(MicroPost $microPost): FormInterface
    {
        return $this->createFormBuilder($microPost)
            ->add('content', TextareaType::class, ['attr' => ['rows' => 5]])
            ->add('save', SubmitType::class, ['label' => 'Create post'])
            ->getForm();
    }

    private function flashMessageWithPartOfContent(string $mainPart, MicroPost $microPost): string
    {
        return sprintf('%s. [%s...]', $mainPart, mb_substr($microPost->getContent(), 0, 75));
    }
}
