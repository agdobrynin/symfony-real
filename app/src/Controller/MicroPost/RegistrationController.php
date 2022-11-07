<?php

namespace App\Controller\MicroPost;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Helper\FlashType;
use App\Service\MicroPost\User\UserServiceInterface;
use App\Service\MicroPost\WelcomeMessageEmailServiceInterface;
use App\Service\WelcomeMessageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/micro-post/{_locale<%app.supported_locales%>}/register", name="micro_post_register")
     */
    public function register(
        Request                             $request,
        UserServiceInterface                $userService,
        WelcomeMessageInterface             $welcomeMessage,
        WelcomeMessageEmailServiceInterface $emailService
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordPlain = $form->get('password')->getData();
            $locale = $form->get('locale')->getData();
            $userService->new($user, $passwordPlain, $locale);
            $message = $welcomeMessage->welcomeMessage($user->getNick())->message;
            $message .= '. For activate your login check your mailbox and click confirmation link!';

            $this->addFlash(FlashType::SUCCESS, $message);
            $emailService->send($user);

            return $this->redirectToRoute('micro_post_list');
        }

        return $this->renderForm('micro-post/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
