<?php
declare(strict_types=1);

namespace App\Tests\Functional\MicroPost\Controller;

use App\Entity\FollowNotification;
use App\Entity\LikeNotification;
use App\Entity\MicroPost;
use App\Entity\Notification;
use App\Entity\UnlikeNotification;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotificationJsonControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    private $em;
    /**
     * @var \App\Repository\UserRepository
     */
    private $userRepository;
    /**
     * @var \App\Repository\NotificationRepository
     */
    private $notificationRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->em->getRepository(User::class);
        $this->notificationRepository = $this->em->getRepository(Notification::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }

    public function testUnreadNotification(): void
    {
        $userNotifier = $this->userRepository->findOneBy([]);
        $this->prepareNotification($userNotifier);

        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->loginUser($userNotifier);
        $client->jsonRequest('GET', '/micro-post/notification/unread-all');

        self::assertResponseIsSuccessful();
        self::assertEquals('{"all":2}', $client->getResponse()->getContent());
    }

    public function testSetSeenNotificationForEachNotification(): void
    {
        $userNotifier = $this->userRepository->findOneBy([]);

        $notification = $this->prepareNotification($userNotifier);
        //test count of unseen notification
        self::assertSameSize(
            $notification,
            $this->notificationRepository->findBy(['user' => $userNotifier, 'seen' => false])
        );

        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->loginUser($userNotifier);

        foreach ($notification as $item) {
            $client->jsonRequest('POST', '/micro-post/notification/set-seen-by-id', [$item->getId()]);
            self::assertResponseIsSuccessful();
            self::assertEquals('null', $client->getResponse()->getContent());
        }

        //test count of unseen notification
        self::assertSameSize(
            [],
            $this->notificationRepository->findBy(['user' => $userNotifier, 'seen' => false])
        );
    }

    /**
     * @return LikeNotification|UnlikeNotification|FollowNotification[]
     */
    protected function prepareNotification(User $userNotifier): array
    {
        // delete all notifications for userNotifier
        $this->notificationRepository->createQueryBuilder('n')
            ->delete()->where('n.user = :user')->setParameter(':user', $userNotifier)
            ->getQuery()->execute();

        $user = $this->getOtherUser($userNotifier);

        // Following or unfollowing to userNotifier
        $user->getFollowing()->contains($userNotifier)
            ? $user->getFollowing()->removeElement($userNotifier)
            : $user->follow($userNotifier);
        $this->em->persist($user);
        $this->em->flush();

        // user like or unlike post of userNotifier
        /** @var MicroPost $microPost */
        $microPost = $userNotifier->getPosts()->first();

        $microPost->getLikedBy()->contains($user)
            ? $microPost->getLikedBy()->removeElement($user)
            : $microPost->like($user);
        $this->em->persist($microPost);
        $this->em->flush();

        return $this->notificationRepository->findBy(['user' => $userNotifier]);
    }

    protected function getOtherUser(User $userNotifier): User
    {
        /** @var User $user who make notification for userNotifier */
        return $this->userRepository->createQueryBuilder('u')
            ->where('u != :user')
            ->setParameter(':user', $userNotifier)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
