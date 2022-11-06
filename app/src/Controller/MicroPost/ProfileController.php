<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Entity\User;
use App\Form\ProfileFormType;
use App\Form\ProfilePasswordFormType;
use App\Helper\FlashType;
use App\Mailer\EmailChangeMailerInterface;
use App\Mailer\PasswordChangeMailerInterface;
use App\Security\ConfirmationTokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/micro-post/{_locale<%app.supported_locales%>}/profile")
 * @IsGranted(User::ROLE_USER)
 */
class ProfileController extends AbstractController
{
    private $request;
    private $entityManager;
    private $translator;
    private $tokenStorage;
    private $tokenGenerator;

    public function __construct(
        RequestStack               $requestStack,
        EntityManagerInterface     $entityManager,
        TranslatorInterface        $translator,
        TokenStorageInterface      $tokenStorage,
        ConfirmationTokenGenerator $tokenGenerator
    )
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @Route("/edit", name="micro_post_profile_edit", methods={"get", "post"})
     * @return RedirectResponse|Response
     */
    public function profileEdit(EmailChangeMailerInterface $emailChangeMailer)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $currentUser);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            // If user change email - user profile set isActive = false,
            // logout and send email with confirmation token
            $previousEmail = $this->entityManager->getUnitOfWork()
                ->getOriginalEntityData($currentUser)['email'];

            if ($previousEmail !== $currentUser->getEmail()) {
                $currentUser->setIsActive(false);
                $currentUser->setConfirmationToken($this->tokenGenerator->getRandomSecureToken());
            }

            $currentUser->getPreferences()->setLocale($form->get('userLocale')->getData());
            $this->entityManager->persist($currentUser);
            $this->entityManager->flush();

            if ($previousEmail !== $currentUser->getEmail()) {
                $emailChangeMailer->send($currentUser);
                $this->tokenStorage->setToken();
                $message = $this->translator->trans('email.change_email.flush_message');
                $this->addFlash(FlashType::SUCCESS, $message);

                return $this->redirectToRoute('micro_post_login');
            }

            $newLocale = $currentUser->getPreferences()->getLocale();
            $message = $this->translator->trans('my_profile.success_update', [], null, $newLocale);

            $this->addFlash(FlashType::SUCCESS, $message);

            $this->request->setLocale($newLocale);

            return $this->redirectToRoute('micro_post_profile_view', ['_locale' => $newLocale]);
        }

        return $this->renderForm('micro-post/user-profile-edit.html.twig', [
            'profileForm' => $form,
        ]);
    }

    /**
     * @Route("/view", name="micro_post_profile_view", methods={"get"})
     */
    public function profileView(): Response
    {
        return $this->render('micro-post/user-profile-view.html.twig');
    }

    /**
     * @Route("/password", name="micro_post_profile_password", methods={"get", "post"})
     * @return Response|RedirectResponse
     */
    public function profilePassword(
        UserPasswordHasherInterface   $userPasswordHasher,
        PasswordChangeMailerInterface $passwordChangeMailer
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfilePasswordFormType::class, $user);
        $form->handleRequest($this->request);

        if ($form->isSubmitted()) {
            $currentPasswordPlain = $form->get('password')->getData();

            if (!$userPasswordHasher->isPasswordValid($user, $currentPasswordPlain)) {
                $message = $this->translator->trans('my_profile.password.messages.password_wrong');
                $form->get('password')->addError(new FormError($message));
            } else if ($form->isValid()) {
                $newPasswordPlain = $form->get('password_new')->getData();
                $passwordHash = $userPasswordHasher->hashPassword($user, $newPasswordPlain);

                $user->setPassword($passwordHash);
                $user->setIsActive(false);
                $user->setConfirmationToken($this->tokenGenerator->getRandomSecureToken());

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $passwordChangeMailer->send($user);

                $message = $this->translator->trans('my_profile.password.messages.password_changed');
                $this->addFlash(FlashType::SUCCESS, $message);
                $this->tokenStorage->setToken();

                return $this->redirectToRoute('micro_post_login');
            }
        }

        return $this->renderForm('micro-post/user-profile-password.html.twig', ['formPassword' => $form]);
    }
}
