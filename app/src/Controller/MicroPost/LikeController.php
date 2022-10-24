<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Dto\LikePostDto;
use App\Entity\MicroPost;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/micro-post")
 */
class LikeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/like/{uuid}", name="micro_post_like")
     */
    public function like(MicroPost $microPost): JsonResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            return $this->json([], Response::HTTP_UNAUTHORIZED);
        }

        $microPost->like($currentUser);
        $this->entityManager->flush();

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

        if (!$currentUser instanceof User) {
            return $this->json([], Response::HTTP_UNAUTHORIZED);
        }

        $microPost->getLikedBy()->removeElement($currentUser);
        $this->entityManager->flush();
        $likeDto = new LikePostDto($microPost->getLikedBy()->count());

        return $this->json($likeDto);
    }
}
