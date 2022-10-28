<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/micro-post/notification")
 * @IsGranted(User::ROLE_USER)
 */
class NotificationController extends AbstractController
{
    private $em;
    private $notificationRepository;

    public function __construct(EntityManagerInterface $em, NotificationRepository $notificationRepository)
    {
        $this->em = $em;
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

        $dto->all = $this->notificationRepository->getCountUnseenNotificationByUser($currentUser);

        return $this->json($dto);
    }

    /**
     * @Route("/set-seen", name="micro_post_notification_set_seen", methods={"post"})
     */
    public function setSeenNotification(Request $request): JsonResponse
    {
        $ids = \json_decode($request->getContent(), false, 2, \JSON_THROW_ON_ERROR);
        $notifications = $this->notificationRepository->findBy([
            'user' => $this->getUser(),
            'id' => $ids
        ]);

        foreach ($notifications as $notification) {
            $notification->setSeen(true);
            $this->em->persist($notification);
        }

        $this->em->flush();

        return $this->json(null);
    }

    /**
     * @Route("/all", name="micro_post_notification_all", methods={"get"})
     */
    public function getAllNotification(): Response
    {
        $notifications = $this->notificationRepository->findBy([
            'seen' => false,
            'user' => $this->getUser(),
        ], ['id' => 'desc']);

        return $this->render('micro-post/user-notification.html.twig', compact('notifications'));
    }
}
