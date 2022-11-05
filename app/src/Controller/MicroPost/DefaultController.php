<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Service\MicroPost\LocalesInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private $locales;

    public function __construct(LocalesInterface $locales)
    {
        $this->locales = $locales;
    }

    /**
     * @Route("/micro-post", methods={"get"})
     */
    public function mailPage(): RedirectResponse
    {
        return $this->redirectToRoute(
            'micro_post_list',
            ['_locale' => $this->locales->getDefaultLocale()]
        );
    }
}
