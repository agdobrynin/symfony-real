<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Entity\User;
use App\Entity\UserPreferences;
use App\Service\MicroPost\LocalesInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

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

    /**
     * @Route("/micro-post/locale_switcher/{locale}", name="micro_post_locale_switcher", methods={"get"})
     */
    public function localeSwitcher(
        string                 $locale,
        LocalesInterface       $locales,
        Request                $request,
        RouterInterface        $router,
        EntityManagerInterface $entityManager
    ): RedirectResponse
    {
        if (!\in_array($locale, $locales->getLocales())) {
            throw new NotFoundHttpException(sprintf('Locale "%s" not support', $locale));
        }

        if ($referer = $request->headers->get('referer')) {
            $refererPathInfo = Request::create($referer)->getPathInfo();
            $routeInfos = $router->match($refererPathInfo);
            $refererRoute = $routeInfos['_route'] ?? '';
            if ($refererRoute) {
                if ($this->getUser() instanceof User) {
                    /** @var User $currentUser */
                    $currentUser = $this->getUser();

                    if (null === $currentUser->getPreferences()) {
                        $preferences = new UserPreferences();
                        $preferences->setLocale($locale);
                        $currentUser->setPreferences($preferences);
                        $entityManager->persist($currentUser);
                        $entityManager->flush();
                    } elseif ($currentUser->getPreferences()->getLocale() !== $locale) {
                        $currentUser->getPreferences()->setLocale($locale);
                        $entityManager->persist($currentUser);
                        $entityManager->flush();
                    }
                }

                return $this->redirectToRoute($refererRoute, ['_locale' => $locale]);
            }
        }

        throw new \RuntimeException('Can not switch locale');
    }
}
