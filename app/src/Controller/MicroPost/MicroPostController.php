<?php

declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Entity\MicroPost;
use App\Helper\FlashType;
use App\Repository\MicroPostRepository;
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

    public function __construct(MicroPostRepository $microPostRepository, EntityManagerInterface $em)
    {
        $this->microPostRepository = $microPostRepository;
        $this->em = $em;
    }

    /**
     * @Route("/", name="micro_post_list", methods={"get"})
     */
    public function index(): Response
    {
        $posts = $this->microPostRepository->findBy([], ['date' => 'desc']);

        return $this->render('micro-post/list.html.twig', ['posts' => $posts]);
    }

    /**
     * @Route("/add", name="micro_post_add", methods={"get", "post"})
     */
    public function add(Request $request)
    {
        $microPost = new MicroPost();
        $microPost->setDate(new \DateTime());
        $form = $this->formMicroPost($microPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var MicroPost $microPost */
            $microPost = $form->getData();
            $microPost->setUser($this->getUser());

            $this->em->persist($microPost);
            $this->em->flush();

            $message = $this->flashMessageWithPartOfContent('The micro post was added', $microPost);
            $this->addFlash(FlashType::SUCCESS, $message);

            return $this->redirectToRoute('micro_post_list');
        }

        return $this->renderForm('micro-post/add.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/edit/{uuid}", name="micro_post_edit", methods={"get", "post"})
     * @IsGranted(MicroPostVoter::EDIT_DEL_OWNER_OR_ADMIN, subject="microPost")
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

            return $this->redirectToRoute('micro_post_list');
        }

        return $this->renderForm('micro-post/add.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/del/{uuid}", name="micro_post_del", methods={"get"})
     */
    public function del(?MicroPost $microPost = null): RedirectResponse
    {
        if (null === $microPost) {
            throw new NotFoundHttpException('Micro post not found for deleting');
        }

        $this->denyAccessUnlessGranted(MicroPostVoter::EDIT_DEL_OWNER_OR_ADMIN, $microPost);

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
