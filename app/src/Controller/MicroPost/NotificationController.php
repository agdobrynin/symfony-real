<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Entity\User;
use App\Repository\NotificationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/micro-post/notification")
 * @IsGranted(User::ROLE_USER)
 */
class NotificationController extends AbstractController
{
    private $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * @Route("/unread-all", name="micro_post_notofication_undera_all", methods={"get"})
     */
    public function unreadCount(): JsonResponse
    {
        $dto = new class {
            public $all;
        };
        /** @var User $currentUser - this always User entity because this controller has security annotation */
        $currentUser = $this->getUser();

        $dto->all = $this->notificationRepository->findUnseenNotificationByUser($currentUser);

        return $this->json($dto);
    }
}
