<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\WelcomeMessageInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class WelcomeHtmlController
{
    private $twig;
    private $welcomeMessage;

    public function __construct(Environment $twig, WelcomeMessageInterface $welcomeMessage)
    {
        $this->twig = $twig;
        $this->welcomeMessage = $welcomeMessage;
    }

    /**
     * @Route(
     *     "/html/{name}",
     *     methods={"get"},
     *     name="controller_main_html"
     * )
     */
    public function htmlPage(string $name = 'Ivan'): Response
    {
        $dto = $this->welcomeMessage->welcomeMessage($name);
        $html = $this->twig->render('welcome_message.html.twig', ['dto' => $dto]);

        return (new Response($html));
    }
}
