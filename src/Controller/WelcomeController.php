<?php

namespace App\Controller;

use App\Service\WelcomeMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeController extends AbstractController
{
    private $welcomeMessage;

    public function __construct(WelcomeMessage $welcomeMessage)
    {
        $this->welcomeMessage = $welcomeMessage;
    }

    /**
     * @Route("/", methods={"get"}, name="controller_main")
     */
    public function main(Request $request): JsonResponse
    {
        $name = $request->get('name', 'Ivan');

        return $this->json(['message' => $this->welcomeMessage->welcomeMessage($name)]);
    }
}