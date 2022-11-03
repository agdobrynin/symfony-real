<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Service\MicroPost\LocalesInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LikeMailer implements LikeMailerInterface
{
    private $mailer;
    private $adminEmail;
    private $translator;
    private $locales;

    public function __construct(MailerInterface $mailer, string $adminEmail, TranslatorInterface $translator, LocalesInterface $locales)
    {
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->translator = $translator;
        $this->locales = $locales;
    }

    public function send(MicroPost $post, User $likedByUser): bool
    {
        $postOwner = $post->getUser();
        $locale = $postOwner->getPreferences()->getLocale() ?: $this->locales->getDefaultLocale();
        $subject = $this->translator->trans('email.like.subject', ['%nick%' => $likedByUser->getNick()], null, $locale);
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
