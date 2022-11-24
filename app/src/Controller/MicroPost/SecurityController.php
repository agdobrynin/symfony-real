<?php

namespace App\Controller\MicroPost;

use App\Entity\User;
use App\Form\ConfirmResendFormType;
use App\Form\LoginFormType;
use App\Helper\FlashType;
use App\Repository\UserRepository;
use App\Service\MicroPost\LocalesInterface;
use App\Service\MicroPost\User\UserServiceInterface;
use App\Service\MicroPost\WelcomeMessageEmailServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/micro-post/{_locale<%app.supported_locales%>}")
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="micro_post_login")
     */
    public function index(AuthenticationUtils $authenticationUtils, Security $security): Response
    {
        if ($security->getUser()) {
            return $this->redirectToRoute("micro_post_list");
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        $form = $this->createForm(LoginFormType::class);

        return $this->renderForm('@mp/login.html.twig', [
            'error' => $error,
            'loginForm' => $form
        ])->setStatusCode($error ? Response::HTTP_UNAUTHORIZED : Response::HTTP_OK);
    }

    /**
     * @Route("/success_auth", name="micro_post_success_auth", methods={"get"})
     */
    public function successAuth(Security $security, Request $request, LocalesInterface $locales): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        /** @var User $authUser */
        $authUser = $security->getUser();

        if ($authUser instanceof User) {
            $userLocale = $authUser->getPreferences()->getLocale() ?: $locales->getDefaultLocale();
            $request->setLocale($userLocale);

            return $this->redirectToRoute('micro_post_list', ['_locale' => $userLocale]);
        }

        return $this->redirectToRoute('micro_post_login');
    }

    /**
     * @Route(
     *     "/confirm/{token}",
     *     name="micro_post_confirm_registration",
     *     methods={"get"}
     * )
     */
    public function confirm(string $token, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->findOneBy(['confirmationToken' => $token]);
        $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;

        if ($user instanceof User) {
            $user->setConfirmationToken(null);
            $user->setIsActive(true);
            $entityManager->persist($user);
            $entityManager->flush();
            $statusCode = Response::HTTP_OK;
        }

        return $this->render('@mp/confirm.html.twig', compact('user'))
            ->setStatusCode($statusCode);
    }

    /**
     * @Route("/confirm_resend", name="micro_post_confirm_resend", methods={"get", "post"})
     */
    public function sendConfirmLink(
        Request                             $request,
        WelcomeMessageEmailServiceInterface $emailService,
        UserRepository                      $userRepository,
        TranslatorInterface                 $translator,
        UserServiceInterface                $userService
    )
    {
        $httpResponseStatus = Response::HTTP_OK;
        $form = $this->createForm(ConfirmResendFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneBy([
                'email' => $form->get('email')->getData(),
                'isActive' => false,
            ]);

            if ($user) {
                $userService->refreshConfirmToken($user);
                $emailService->send($user);
                $message = $translator->trans('confirm_token_resend.success');
                $this->addFlash(FlashType::SUCCESS, $message);

                return $this->redirectToRoute('micro_post_login');
            } else {
                $message = $translator->trans('confirm_token_resend.fail');
                $this->addFlash(FlashType::DANGER, $message);
                $httpResponseStatus = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        }

        return $this->renderForm('@mp/confirm-resend.html.twig', compact('form'))
            ->setStatusCode($httpResponseStatus);
    }

    /**
     * @Route("/logout", name="micro_post_logout", methods={"GET"})
     */
    public function logout(): void
    {
        // controller can be blank: it will never be called!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }
}
