<?php

namespace App\Controller\MicroPost;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Helper\FlashType;
use App\Service\MicroPost\User\UserServiceNewUserInterface;
use App\Service\MicroPost\WelcomeMessageEmailServiceInterface;
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
        UserServiceNewUserInterface         $userService,
        WelcomeMessageEmailServiceInterface $emailService
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordPlain = $form->get('password')->getData();
            $locale = $form->get('locale')->getData();
            $userService->addAndSetConfirmationToken($user, $passwordPlain, $locale);
            $message = 'For activate your login check your mailbox and click confirmation link!';

            $this->addFlash(FlashType::SUCCESS, $message);
            $emailService->send($user);

            return $this->redirectToRoute('micro_post_list');
        }

        return $this->renderForm('@mp/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
