<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/micro-post", methods={"get"})
     */
    public function mailPage(): RedirectResponse
    {
        return $this->redirectToRoute('micro_post_list', ['_locale' => 'en']);
    }
}
