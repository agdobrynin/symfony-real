<?php
declare(strict_types=1);

namespace App\Event;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class LikeNotifyByEmailSubscriber implements EventSubscriberInterface
{
    private $mailer;
    private $adminEmail;

    public function __construct(MailerInterface $mailer, string $adminEmail)
    {
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LikeNotifyByEmailEvent::NAME => 'onLikeNotifyByEmail',
        ];
    }

    public function onLikeNotifyByEmail(LikeNotifyByEmailEvent $event)
    {
        $post = $event->getMicroPost();
        $postOwner = $post->getUser();
        $likedByUser = $event->getLikedByUser();

        $email = (new TemplatedEmail())
            ->from($this->adminEmail)
            ->to($postOwner->getEmail())
            ->subject('Hey! Your post like by ' . $likedByUser->getNick())
            ->htmlTemplate('micro-post/email/like.html.twig')
            ->textTemplate('micro-post/email/like.text.twig')
            ->context([
                'userHello' => $postOwner->getNick(),
                'likeByUser' => $likedByUser,
                'createAt' => new \DateTime(),
                'post' => $post,
            ]);

        $this->mailer->send($email);

    }
}
