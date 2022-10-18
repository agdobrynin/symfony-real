<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MicroPost;
use App\Helper\FlashType;
use App\Repository\MicroPostRepository;
use Doctrine\ORM\EntityManagerInterface;
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
            $microPost = $form->getData();
            $this->em->persist($microPost);
            $this->em->flush();
            $this->addFlash(FlashType::SUCCESS, 'The micro post was added');

            return $this->redirectToRoute('micro_post_list');
        }

        return $this->renderForm('micro-post/add.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/edit/{uuid}", name="micro_post_edit", methods={"get", "post"})
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
            $this->addFlash(FlashType::INFO, 'The micro post was updated');

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

        $this->microPostRepository->remove($microPost, true);
        $this->addFlash(FlashType::SUCCESS, 'The micro post was deleted');

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
}
