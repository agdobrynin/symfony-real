<?php

namespace App\Controller\MicroPost;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/micro-post/{_locale<%app.supported_locales%>}")
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="micro_post_login")
     * @return Response|RedirectResponse
     */
    public function index(AuthenticationUtils $authenticationUtils, Security $security)
    {
        if ($security->getUser()) {
            return $this->redirectToRoute("micro_post_list");
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        $form = $this->createForm(LoginFormType::class);

        return $this->renderForm('micro-post/login.html.twig', [
            'error' => $error,
            'loginForm' => $form
        ]);
    }

    /**
     * @Route(
     *     "/confirm/{token}",
     *     name="micro_post_confirm_registraction",
     *     methods={"get"}
     * )
     */
    public function confirm(string $token, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->findOneBy(['confirmationToken' => $token]);

        if ($user instanceof User) {
            $user->setConfirmationToken(null);
            $user->setIsActive(true);
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render('micro-post/confirm.html.twig', compact('user'));
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
