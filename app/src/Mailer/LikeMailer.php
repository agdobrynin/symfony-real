<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Entity\MicroPost;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LikeMailer implements LikeMailerInterface
{
    private $mailer;
    private $adminEmail;
    private $translator;

    public function __construct(MailerInterface $mailer, string $adminEmail, TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->translator = $translator;
    }

    public function send(MicroPost $post, User $likedByUser, string $locale): bool
    {
        $postOwner = $post->getUser();
        $subject = $this->translator->trans('email.like.subject', ['%nick%' => $likedByUser->getNick()]);
        $templateHtml = sprintf('micro-post/email/like.%s.html.twig', $locale);
        $templateText = sprintf('micro-post/email/like.%s.text.twig', $locale);

        $email = (new TemplatedEmail())
            ->from($this->adminEmail)
            ->to($postOwner->getEmail())
            ->subject($subject)
            ->htmlTemplate($templateHtml)
            ->textTemplate($templateText)
            ->context([
                'userHello' => $postOwner->getNick(),
                'likeByUser' => $likedByUser,
                'createAt' => new \DateTime(),
                'post' => $post,
                'locale' => $locale,
            ]);

        $this->mailer->send($email);

        return true;
    }
}
