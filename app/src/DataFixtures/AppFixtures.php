<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Entity\User;
use App\Entity\UserPreferences;
use App\Service\MicroPost\LocalesInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const MICRO_POST_MAX = 1000;
    private const PASSWORD = 'qwerty';
    private $userPasswordHasher;
    /** @var UserFixtureDto[] */
    private $fixtureUsers;
    private $locales;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, LocalesInterface $locales)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->fixtureUsers = self::getUserFixtures();
        $this->locales = $locales;
    }

    /**
     * @return UserFixtureDto[]
     */
    public static function getUserFixtures(): array
    {
        return [
            new UserFixtureDto('admin', 'Admin of App', '🛡', true),
            new UserFixtureDto('blogger', 'The best blogger', '🎭'),
            new UserFixtureDto('superman', 'Ironman', '🚀'),
            new UserFixtureDto('santa', 'Santa Claus', '🎅'),
        ];
    }

    public static function searchUserFixtureByProperty(string $property, $searchValue): ?UserFixtureDto
    {
        $data = AppFixtures::getUserFixtures();
        $index = array_search($searchValue, array_column($data, $property), true);

        return $index === false ? null : $data[$index];
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadMicroPost($manager);
        $this->loadComments($manager);
    }

    private function loadMicroPost(ObjectManager $manager): void
    {
        $faker = Factory::create('ru_RU');

        for ($i = 0; $i < self::MICRO_POST_MAX; $i++) {
            $microPost = new MicroPost();
            $microPost->setDate($faker->dateTimeBetween('-60 day', 'now'))
                ->setContent($faker->realTextBetween(120, 250));
            /** @var User $referenceUser */
            $referenceUser = $this->getReference($this->randUserLogin());
            $microPost->setUser($referenceUser);

            $manager->persist($microPost);
            $this->setReference('microPost_' . $i, $microPost);
        }

        $manager->flush();
    }

    private function loadComments(ObjectManager $manager): void
    {
        $faker = Factory::create('ru_RU');

        for ($i = 0; $i < self::MICRO_POST_MAX; $i++) {
            $maxComments = rand(0, 15);

            for ($j = 0; $j < $maxComments; $j++) {
                $comment = (new Comment())
                    ->setContent($faker->text(200))
                    ->setPost($this->getReference('microPost_' . $i))
                    ->setUser($this->getReference($this->randUserLogin()));

                $manager->persist($comment);
            }
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
                ->setNick($fixtureUser->nick)
                ->setEmoji($fixtureUser->emoji)
                ->setIsActive(true);
            $role = $fixtureUser->isAdmin ? User::ROLE_ADMIN : User::ROLE_USER;
            $user->setRoles([$role]);
            $user->setPassword($this->getPasswordHash($user));

            $preferences = (new UserPreferences())->setLocale($this->getRandomUserLocale());
            $user->setPreferences($preferences);
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

    private function getRandomUserLocale(): string
    {
        $locales = $this->locales->getLocales();
        return $locales[rand(0, count($locales) - 1)];
    }
}
