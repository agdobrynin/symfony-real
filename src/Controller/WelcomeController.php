<?php

namespace App\Controller;

use App\Service\WelcomeMessageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class WelcomeController extends AbstractController
{
    private $welcomeMessage;
    private $normalizer;

    public function __construct(WelcomeMessageInterface $welcomeMessage, NormalizerInterface $normalizer)
    {
        $this->welcomeMessage = $welcomeMessage;
        $this->normalizer = $normalizer;
    }

    /**
     * @Route("/", methods={"get"}, name="controller_main")
     */
    public function main(Request $request): JsonResponse
    {
        $name = $request->get('name', 'Ivan');
        $dto = $this->welcomeMessage->welcomeMessage($name);

        return $this->json($this->normalizer->normalize($dto));
    }
}