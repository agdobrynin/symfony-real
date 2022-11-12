<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/micro-post/{_locale<%app.supported_locales%>}")
 * @IsGranted(User::ROLE_USER)
 */
class FollowerController extends AbstractController
{
    private $entityManager;
    /** @var User $currentUser */
    private $currentUser;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->currentUser = $security->getUser();
    }

    /**
     * @Route("/follow/{uuid}", methods={"get"}, name="micro-post-following")
     */
    public function follow(User $followUser): RedirectResponse
    {
        if ($followUser->getUuid() !== $this->currentUser->getUuid()) {
            $this->currentUser->follow($followUser);
            $this->entityManager->flush();
        }

        return $this->redirectTo($followUser);
    }

    /**
     * @Route("/unfollow/{uuid}", methods={"get"}, name="micro-post-unfollowing")
     */
    public function unfollow(User $unfollowUser): RedirectResponse
    {
        if ($unfollowUser->getUuid() !== $this->currentUser->getUuid()) {
            $this->currentUser->getFollowing()->removeElement($unfollowUser);
            $this->entityManager->flush();
        }

        return $this->redirectTo($unfollowUser);
    }

    private function redirectTo(User $user): RedirectResponse
    {
        return $this->redirectToRoute('micro_post_by_user', ['uuid' => $user->getUuid()]);
    }
}