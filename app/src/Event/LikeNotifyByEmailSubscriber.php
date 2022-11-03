<?php
declare(strict_types=1);

namespace App\Event;

use App\Mailer\LikeMailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LikeNotifyByEmailSubscriber implements EventSubscriberInterface
{
    private $likeMailer;

    public function __construct(LikeMailerInterface $likeMailer)
    {
        $this->likeMailer = $likeMailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LikeNotifyByEmailEvent::NAME => 'onLikeNotifyByEmail',
        ];
    }

    public function onLikeNotifyByEmail(LikeNotifyByEmailEvent $event)
    {
        $this->likeMailer->send($event->getMicroPost(), $event->getLikedByUser());
    }
}
