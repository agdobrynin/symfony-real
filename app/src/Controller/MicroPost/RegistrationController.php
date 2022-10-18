<?php

namespace App\Controller\MicroPost;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Helper\FlashType;
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
     * @Route("/micro-post/register", name="micro_post_register")
     */
    public function register(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager,
        WelcomeMessageInterface     $welcomeMessage
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            $message = $welcomeMessage->welcomeMessage($user->getUserIdentifier());

            $this->addFlash(FlashType::SUCCESS, $message);

            return $this->redirectToRoute('micro_post_list');
        }

        return $this->render('micro-post/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
