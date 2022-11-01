<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Dto\LikePostDto;
use App\Entity\MicroPost;
use App\Entity\User;
use App\Event\LikeNotifyByEmailEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/micro-post/{_locale<%app.supported_locales%>}", methods={"get"})
 */
class LikeController extends AbstractController
{
    private $entityManager;
    private $eventDispatcher;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/like/{uuid}", name="micro_post_like")
     */
    public function like(MicroPost $microPost, Request $request): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if ($errorResponse = $this->checkIfUserRegistered($currentUser)) {
            return $errorResponse;
        }

        $microPost->like($currentUser);
        $this->entityManager->flush();

        // Notify by email - creates the event and dispatches it
        $event = new LikeNotifyByEmailEvent($microPost, $currentUser, $request->getLocale());
        $this->eventDispatcher->dispatch($event, LikeNotifyByEmailEvent::NAME);

        $likeDto = new LikePostDto($microPost->getLikedBy()->count());

        return $this->json($likeDto);
    }

    /**
     * @Route("/unlike/{uuid}", name="micro_post_unlike")
     */
    public function unlike(MicroPost $microPost): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if ($errorResponse = $this->checkIfUserRegistered($currentUser)) {
            return $errorResponse;
        }

        $microPost->getLikedBy()->removeElement($currentUser);
        $this->entityManager->flush();
        $likeDto = new LikePostDto($microPost->getLikedBy()->count());

        return $this->json($likeDto);
    }

    private function checkIfUserRegistered(?User $user): ?JsonResponse
    {
        if (!$user instanceof User) {
            $dto = new class {
                public $redirect;
            };
            $dto->redirect = $this->generateUrl('micro_post_register');

            return $this->json($dto, Response::HTTP_UNAUTHORIZED);
        }

        return null;
    }
}
