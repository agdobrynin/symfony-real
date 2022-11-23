<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Service\MicroPost\GetBloggersServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function users(Request $request, GetBloggersServiceInterface $bloggersService): Response
    {
        $page = (int)$request->get('page', 1);
        $bloggersWithPaginator = $bloggersService->getBloggers($page);

        return $this->render('@mp/blogger-list.html.twig', compact('bloggersWithPaginator'));
    }
}
