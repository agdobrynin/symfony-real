<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\MicroPost;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const PASSWORD = 'qwerty';
    private $userPasswordHasher;
    /** @var UserFixtureDto[] */
    private $fixtureUsers;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->fixtureUsers[] = new UserFixtureDto('admin', '🛡', true);
        $this->fixtureUsers[] = new UserFixtureDto('blogger', '🎭');
        $this->fixtureUsers[] = new UserFixtureDto('super-man', '🚀');
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadMicroPost($manager);
    }

    private function loadMicroPost(ObjectManager $manager): void
    {
        $faker = Factory::create('ru_RU');

        for ($i = 0; $i < 20; $i++) {
            $microPost = new MicroPost();
            $microPost->setDate($faker->dateTimeBetween('-60 day', 'now'))
                ->setContent($faker->realTextBetween(120, 250));
            /** @var User $referenceUser */
            $referenceUser = $this->getReference($this->randUserLogin());
            $microPost->setUser($referenceUser);

            $manager->persist($microPost);
        }

        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager): void
    {
        $faker = Factory::create('ru_RU');

        foreach ($this->fixtureUsers as $fixtureUser) {
            $user = (new User())
                ->setEmail($faker->email)
                ->setLogin($fixtureUser->login)
                ->setNick($fixtureUser->icon . $faker->userName);
            $role = $fixtureUser->isAdmin ? User::ROLE_ADMIN : User::ROLE_USER;
            $user->setRoles([$role]);
            $user->setPassword($this->getPasswordHash($user));
            $this->addReference($fixtureUser->login, $user);
            $manager->persist($user);
        }

        // Followers
        foreach ($this->fixtureUsers as $fixtureUserDto) {
            /** @var User $currentUser */
            $currentUser = $this->getReference($fixtureUserDto->login);
            $followingUser = $this->getReference($this->randUserLogin($fixtureUserDto->login));
            $currentUser->getFollowing()->add($followingUser);
        }

        $manager->flush();
    }

    private function getPasswordHash(User $user): string
    {
        return $this->userPasswordHasher->hashPassword($user, self::PASSWORD);
    }

    private function randUserLogin(?string $excludeLogin = null): string
    {
        $users = $this->fixtureUsers;

        if ($excludeLogin) {
            $users = array_values(array_filter($this->fixtureUsers, static function (UserFixtureDto $item) use ($excludeLogin) {
                return $item->login !== $excludeLogin;
            }));
        }

        $maxIndex = count($users) - 1;

        return $users[rand(0, $maxIndex)]->login;
    }

}
