<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeController extends AbstractController
{
    /**
     * @Route("/", methods={"get"}, name="controller_main")
     */
    public function main(Request $request): JsonResponse
    {
        $message = sprintf('Welcome %s', $request->get('name', 'Ivan'));

        return $this->json(['message' => $message]);
    }
}