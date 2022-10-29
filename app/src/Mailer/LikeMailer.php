<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Entity\MicroPost;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class LikeMailer implements LikeMailerInterface
{
    private $mailer;
    private $adminEmail;

    public function __construct(MailerInterface $mailer, string $adminEmail)
    {
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
    }

    public function send(MicroPost $post, User $likedByUser): bool
    {
        $postOwner = $post->getUser();;

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

        return true;
    }
}
