<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\ByUserNotificationRepository;
use App\Repository\LikeUnlikeNotificationRepository;
use App\Repository\NotificationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/micro-post/{_locale<%app.supported_locales%>}/notification")
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
     * @Route("/set-seen-all", name="micro_post_notification_set_seen_all", methods={"get"})
     */
    public function setSeenAllNotification(): RedirectResponse
    {
        $this->notificationRepository->setSeenAllNotificationByUser($this->getUser());

        return $this->redirectToRoute('micro_post_list');
    }

    /**
     * @Route("/all", name="micro_post_notification_all", methods={"get"})
     */
    public function getAllNotification(
        LikeUnlikeNotificationRepository $likeUnlikeNotificationRepository,
        ByUserNotificationRepository     $byUserNotificationRepository
    ): Response
    {
        $notificationsFollowUnfollow = $byUserNotificationRepository->getUnseenFollowUnfollowNotifyForUser($this->getUser());
        $likeUnlikeNotifications = $likeUnlikeNotificationRepository->getUnseenByUser($this->getUser());

        $notifications = array_merge($notificationsFollowUnfollow, $likeUnlikeNotifications);

        usort($notifications, static function (Notification $item1, Notification $item2) {
            return $item1->getId() < $item2->getId();
        });

        return $this->render('@mp/user-notification.html.twig', compact('notifications'));
    }
}
