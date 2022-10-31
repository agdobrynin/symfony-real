<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/micro-post/{_locale<%app.supported_locales%>}")
 */
class BloggerController extends AbstractController
{
    /**
     * @Route("/bloggers", methods={"get"}, name="micro_post_blogger_list")
     */
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('micro-post/blogger-list.html.twig', ['bloggers' => $userRepository->findAll()]);
    }
}