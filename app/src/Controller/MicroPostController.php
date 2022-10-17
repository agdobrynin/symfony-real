<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\MicroPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/micro-post")
 */
class MicroPostController extends AbstractController
{
    private $microPostRepository;

    public function __construct(MicroPostRepository $microPostRepository)
    {
        $this->microPostRepository = $microPostRepository;
    }
    /**
     * @Route("/", name="micro_post_list", methods={"get"})
     */
    public function index(): Response
    {
        return $this->render('micro-post/list.html.twig', ['posts' => $this->microPostRepository->findAll()]);
    }
}
