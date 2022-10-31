<?php

namespace App\Controller\MicroPost;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Helper\FlashType;
use App\Security\ConfirmationTokenGeneratorInterface;
use App\Service\MicroPost\WelcomeMessageEmailServiceInterface;
use App\Service\WelcomeMessageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/micro-post/{_locale<%app.supported_locales%>}/register", name="micro_post_register")
     */
    public function register(
        Request                             $request,
        UserPasswordHasherInterface         $userPasswordHasher,
        EntityManagerInterface              $entityManager,
        WelcomeMessageInterface             $welcomeMessage,
        WelcomeMessageEmailServiceInterface $emailService,
        ConfirmationTokenGeneratorInterface $confirmationTokenGenerator
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $passwordPlain = $form->get('password')->getData();
            $passwordHash = $userPasswordHasher->hashPassword($user, $passwordPlain);
            $user->setPassword($passwordHash);
            $user->setRoles(User::ROLE_DEFAULT);
            $user->setConfirmationToken($confirmationTokenGenerator->getRandomSecureToken());
            $entityManager->persist($user);
            $entityManager->flush();
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
