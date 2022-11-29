<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Entity\User;
use App\Form\ProfileFormType;
use App\Form\ProfilePasswordFormType;
use App\Helper\FlashType;
use App\Mailer\EmailChangeMailerInterface;
use App\Mailer\PasswordChangeMailerInterface;
use App\Repository\Filter\SoftDeleteFilter;
use App\Service\MicroPost\User\Exception\UserWrongPasswordException;
use App\Service\MicroPost\User\GetOriginalEntityDataInterface;
use App\Service\MicroPost\User\UserServiceChangeEmailInterface;
use App\Service\MicroPost\User\UserServiceChangePasswordInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/micro-post/{_locale<%app.supported_locales%>}/profile")
 * @IsGranted(User::ROLE_USER)
 */
class ProfileController extends AbstractController
{
    private $request;
    private $translator;

    public function __construct(RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->translator = $translator;
    }

    /**
     * @Route("/edit", name="micro_post_profile_edit", methods={"get", "post"})
     * @return RedirectResponse|Response
     */
    public function profileEdit(
        EmailChangeMailerInterface      $emailChangeMailer,
        GetOriginalEntityDataInterface  $originalEntityData,
        EntityManagerInterface          $entityManager,
        UserServiceChangeEmailInterface $userService
    )
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $currentUser);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentUser->getPreferences()->setLocale($form->get('userLocale')->getData());
            $emailFromFrom = $form->get('email')->getData();
            $originalEmail = $originalEntityData->getOriginalValue($currentUser, 'email');

            if ($originalEmail !== $emailFromFrom) {
                $userService->changeAndResetAuthToken($currentUser, $emailFromFrom);
                $emailChangeMailer->send($currentUser);

                $message = $this->translator->trans('email.change_email.flush_message');
                $this->addFlash(FlashType::SUCCESS, $message);

                return $this->redirectToRoute('micro_post_login');
            }

            $entityManager->persist($currentUser);
            $entityManager->flush();

            $newLocale = $currentUser->getPreferences()->getLocale();
            $message = $this->translator->trans('my_profile.success_update', [], null, $newLocale);

            $this->addFlash(FlashType::SUCCESS, $message);

            $this->request->setLocale($newLocale);

            return $this->redirectToRoute('micro_post_profile_view', ['_locale' => $newLocale]);
        }

        return $this->renderForm('@mp/user-profile-edit.html.twig', [
            'profileForm' => $form,
        ]);
    }

    /**
     * @Route("/view", name="micro_post_profile_view", methods={"get"})
     */
    public function profileView(EntityManagerInterface $entityManager): Response
    {
        // always enable filter for comments
        $entityManager->getFilters()->enable(SoftDeleteFilter::NAME);

        return $this->render('@mp/user-profile-view.html.twig');
    }

    /**
     * @Route("/password", name="micro_post_profile_password", methods={"get", "post"})
     * @return Response|RedirectResponse
     */
    public function profilePassword(
        PasswordChangeMailerInterface      $passwordChangeMailer,
        UserServiceChangePasswordInterface $userServiceChangePassword
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfilePasswordFormType::class, $user);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $currentPasswordPlain = $form->get('password')->getData();
                $newPasswordPlain = $form->get('password_new')->getData();

                $userServiceChangePassword->changeAndResetAuthToken($user, $currentPasswordPlain, $newPasswordPlain);
                $passwordChangeMailer->send($user);

                $message = $this->translator->trans('my_profile.password.messages.password_changed');
                $this->addFlash(FlashType::SUCCESS, $message);

                return $this->redirectToRoute('micro_post_login');
            } catch (UserWrongPasswordException $exception) {
                $message = $this->translator->trans('my_profile.password.messages.password_wrong');
                $form->get('password')->addError(new FormError($message));
            }
        }

        return $this->renderForm('@mp/user-profile-password.html.twig', ['formPassword' => $form]);
    }
}
