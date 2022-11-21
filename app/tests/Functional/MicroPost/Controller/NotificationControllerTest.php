<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\MicroPost;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use function Doctrine\ORM\QueryBuilder;

class NotificationControllerTest extends WebTestCase
{
    protected const URL_LOGIN_FORM = '/micro-post/en/login';
    protected const URL_GET_UNSEEN_NOTIFY = '/micro-post/en/notification/all';
    protected const URL_SET_SEEN_ALL_NOTIFY = '/micro-post/en/notification/set-seen-all';
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var \App\Repository\NotificationRepository|\Doctrine\ORM\EntityRepository
     */
    private $notificationRepository;
    /**
     * @var \App\Repository\UserRepository|\Doctrine\ORM\EntityRepository
     */
    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->notificationRepository = $this->em->getRepository(Notification::class);
        $this->userRepository = $this->em->getRepository(User::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }

    public function testNotificationPageWithNonAuthUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        // request with non auth user
        $client->request('GET', self::URL_GET_UNSEEN_NOTIFY);
        self::assertResponseRedirects();
        self::assertStringEndsWith(self::URL_LOGIN_FORM, $client->getResponse()->headers->get('Location'));
    }

    public function testNotificationPageWithCards(): void
    {
        $user = $this->getUserWithEmptyNotifications();

        $notification = $this->notificationRepository->getCountUnseenNotificationByUser($user);
        self::assertEquals(0, $notification);

        // add notification like
        /** @var MicroPost $microPost */
        $microPost = $user->getPosts()->first();
        $microPost->like($user);
        $this->em->flush();

        // find other user
        $user2 = $this->userRepository->createQueryBuilder('u')
            ->where('u != :user')
            ->setParameter(':user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        $microPost->like($user2);
        $this->em->flush();

        self::ensureKernelShutdown();
        $client = static::createClient();

        $client->loginUser($user);
        $crawler = $client->request('GET', self::URL_GET_UNSEEN_NOTIFY);
        self::assertResponseIsSuccessful();

        $notificationItems = $crawler->filter('main div.col-notification-item');
        self::assertEquals($microPost->getLikedBy()->count(), $notificationItems->count());

        $usersNickInHtml = array_reduce($microPost->getLikedBy()->toArray(), static function (array $carry, User $user) {
            $carry[] = $user->getEmoji() . '@' . $user->getNick();

            return $carry;
        }, []);

        // test who like post
        $notifyCards = $crawler->filter('main div.col-notification-item a.notify-by-user');

        for ($i = 0; $i < $notifyCards->count(); $i++) {
            self::assertContains(trim($notifyCards->getNode($i)->textContent), $usersNickInHtml);
        }

        // Click on button "set seen for all"
        $linkSetSeenForAll = $crawler
            ->filter('a[href="' . self::URL_SET_SEEN_ALL_NOTIFY . '"]')
            ->link();

        $client->request($linkSetSeenForAll->getMethod(), $linkSetSeenForAll->getUri());
        self::assertResponseRedirects();

        $notification = $this->notificationRepository->getCountUnseenNotificationByUser($user);
        self::assertEmpty($notification);
    }

    protected function getUserWithEmptyNotifications(): User
    {
        // delete all notification
        $user = $this->userRepository->findOneBy([]);

        $this->notificationRepository->createQueryBuilder('n')->delete()
            ->where('n.user = :user')
            ->setParameter(':user', $user)
            ->getQuery()->execute();

        return $user;
    }
}
