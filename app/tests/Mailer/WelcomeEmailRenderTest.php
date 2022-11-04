<?php
declare(strict_types=1);

namespace App\Tests\Mailer;

use App\Entity\User;
use App\Entity\UserPreferences;
use App\Mailer\WelcomeMailer;
use App\Security\ConfirmationTokenGeneratorInterface;
use App\Service\MicroPost\LocalesInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class WelcomeEmailRenderTest extends KernelTestCase
{
    /** @var Environment */
    private $twig;
    /** @var RouterInterface */
    private $router;

    public function setUp(): void
    {
        parent::setUp();
        $this->twig = self::getContainer()->get(Environment::class);
        $this->router = self::getContainer()->get(RouterInterface::class);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testWelcomeEmailRender(string $locale, User $user, string $confirmToken): void
    {
        $template = sprintf(WelcomeMailer::TEMPLATE_TEXT_PATTERN, $user->getPreferences()->getLocale());
        $body = $this->twig->render($template, ['user' => $user, 'locale' => $user->getPreferences()->getLocale()]);

        $confirmPath = $this->router->generate('micro_post_confirm_registration', [
            'token' => $confirmToken,
            '_locale' => $user->getPreferences()->getLocale()
        ]);

        $this->assertStringContainsString($confirmPath, $body);
    }

    public function dataProvider(): array
    {
        /** @var LocalesInterface $locales */
        $locales = self::getContainer()->get(LocalesInterface::class);
        /** @var ConfirmationTokenGeneratorInterface $tokenGenerator */
        $tokenGenerator = self::getContainer()->get(ConfirmationTokenGeneratorInterface::class);
        $data = [];

        foreach ($locales->getLocales() as $locale) {
            $user = (new User())
                ->setNick('Superman')
                ->setEmail('superman@outlook.com');

            $preferences = (new UserPreferences())
                ->setLocale($locale);

            $user->setPreferences($preferences);

            $confirmToken = $tokenGenerator->getRandomSecureToken();
            $user->setConfirmationToken($confirmToken);
            $data[] = [$locale, $user, $confirmToken];
        }

        return $data;
    }
}
