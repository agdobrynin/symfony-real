<?php

namespace App\Controller\MicroPost;

use App\Form\LoginFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/micro-post")
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
     * @Route("/logout", name="micro_post_logout", methods={"GET"})
     */
    public function logout(): void
    {
        // controller can be blank: it will never be called!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }
}
