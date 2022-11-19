<?php
declare(strict_types=1);

namespace App\Tests\Integration\Service\MicroPost;

use App\Entity\User;
use App\Security\ConfirmationTokenGenerator;
use App\Service\MicroPost\WelcomeMessageEmailServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;

class WelcomeMessageEmailServiceTest extends KernelTestCase
{
    use MailerAssertionsTrait;

    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    private $em;
    /**
     * @var \App\Repository\UserRepository
     */
    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->em->getRepository(User::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function testWelcomeMessageEmailService(): void
    {
        /** @var WelcomeMessageEmailServiceInterface $srv */
        $srv = self::getContainer()->get(WelcomeMessageEmailServiceInterface::class);
        $user = $this->userRepository->findOneBy([]);
        $confirmationToken = (new ConfirmationTokenGenerator())->getRandomSecureToken();
        $user->setConfirmationToken($confirmationToken);
        $srv->send($user);

        $email = self::getMailerMessage();

        $linkToConfirm = sprintf('/micro-post/%s/confirm/%s', $user->getPreferences()->getLocale(), $confirmationToken);
        self::assertEmailHtmlBodyContains($email, $linkToConfirm);
        self::assertEmailHtmlBodyContains($email, $user->getNick());
        self::assertEmailTextBodyContains($email, $linkToConfirm);
        self::assertEmailTextBodyContains($email, $user->getNick());

        $emailAsString = $email->toString();
        $headerMailToForRegExp = '/To:.*' . preg_quote($user->getEmail()) . '/i';
        self::assertMatchesRegularExpression($headerMailToForRegExp, $emailAsString);

        $adminEmail = self::getContainer()->getParameter('micropost.admin.email');
        $headerMailFromForRegExp = '/From:.*' . preg_quote($adminEmail) . '/i';
        self::assertMatchesRegularExpression($headerMailFromForRegExp, $emailAsString);
    }
}
