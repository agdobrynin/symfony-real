<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Entity\User;
use App\Form\ProfileFormType;
use App\Helper\FlashType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/micro-post/{_locale<%app.supported_locales%>}/profile")
 * @IsGranted(User::ROLE_USER)
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("/edit", name="micro_post_profile_edit", methods={"get", "post"})
     * @return RedirectResponse|Response
     */
    public function profileEdit(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $currentUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentUser->getPreferences()->setLocale($form->get('userLocale')->getData());
            $entityManager->persist($currentUser);
            $entityManager->flush();
            $message = $translator->trans('my_profile.success_update', [], null, $currentUser->getPreferences()->getLocale());

            $this->addFlash(FlashType::SUCCESS, $message);
            $newLocale = $currentUser->getPreferences()->getLocale();
            $request->setLocale($newLocale);

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
     */
    public function profilePassword(): Response
    {
        return $this->render('micro-post/user-profile-password.html.twig');
    }
}
