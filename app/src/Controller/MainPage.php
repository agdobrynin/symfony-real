<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @codeCoverageIgnore
 */
class MainPage extends AbstractController
{
    /**
     * @Route("/", methods={"get"}, name="main_page")
     */
    public function index(): Response
    {
        return $this->render('main_page.html.twig');
    }
}
