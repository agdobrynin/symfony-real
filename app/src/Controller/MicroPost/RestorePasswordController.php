<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Form\RestorePasswordChangeFormType;
use App\Form\RestorePasswordFormType;
use App\Helper\FlashType;
use App\Mailer\RestorePasswordMailerInterface;
use App\Repository\UserRepository;
use App\Service\MicroPost\User\UserServiceRestoredPasswordInterface;
use App\Service\MicroPost\User\UserServiceRestorePasswordTokenInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/micro-post/{_locale<%app.supported_locales%>}/restore/password")
 */
class RestorePasswordController extends AbstractController
{
    private $userRepository;
    private $translator;
    private $logger;

    public function __construct(UserRepository $userRepository, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * @Route("/form", name="micro_post_restore_password_form", methods={"get", "post"})
     *
     * @return Response|RedirectResponse
     */
    public function formRestore(
        Request                                  $request,
        RestorePasswordMailerInterface           $restorePasswordMailer,
        UserServiceRestorePasswordTokenInterface $userServiceRestorePasswordToken
    )
    {
        $form = $this->createForm(RestorePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $this->userRepository->findOneBy(['email' => $email]);

            if ($user) {
                $userServiceRestorePasswordToken->refreshAndUnsetAuthToken($user);
                $restorePasswordMailer->send($user);
            } else {
                $message = sprintf('Change restore password token by unknown email "%s"', $email);
                $this->logger->alert($message, [$request]);
            }

            $this->addFlash(FlashType::SUCCESS, $this->translator->trans('restore_password.success'));

            return $this->redirectToRoute('micro_post_list');
        }

        return $this->renderForm('@mp/restore-password.html.twig', compact('form'));
    }

    /**
     * @Route("/confirm/{token}", name="micro_post_restore_password_confirm", methods={"get", "post"})
     *
     * @return Response|RedirectResponse
     */
    public function confirmed(string $token, Request $request, UserServiceRestoredPasswordInterface $serviceRestoredPassword)
    {
        $user = $this->userRepository->findOneBy(['changePasswordToken' => $token]);

        if (null === $user) {
            $this->addFlash(FlashType::DANGER, $this->translator->trans('restore_password.fail'));
            $message = sprintf('Unknown restore password token "%s"', $token);
            $this->logger->alert($message, [$request]);

            return $this->redirectToRoute('micro_post_list');
        }

        $form = $this->createForm(RestorePasswordChangeFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $serviceRestoredPassword->updateAndUnsetAuthToken(
                $user,
                $form->get('password')->getData(),
                $token
            );
            $message = $this->translator->trans('restore_password.password_changed');
            $this->addFlash(FlashType::SUCCESS, $message);

            return $this->redirectToRoute('micro_post_login');
        }

        return $this->renderForm('@mp/restore-password-change.html.twig', compact('form', 'user'));
    }
}
