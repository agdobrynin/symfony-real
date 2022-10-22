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
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadMicroPost($manager);
    }

    private function loadMicroPost(ObjectManager $manager): void
    {
        $faker = Factory::create('ru_RU');

        for ($i = 0; $i < 5; $i++) {
            $microPost = new MicroPost();
            $microPost->setDate($faker->dateTimeBetween('-60 day', 'now'))
                ->setContent($faker->realTextBetween(120, 250));

            $referenceUser = $this->getReference($i === 0 ? 'admin' : 'dev.php');
            $microPost->setUser($referenceUser);

            $manager->persist($microPost);
        }

        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager): void
    {
        $user = (new User())
            ->setEmail('dev@kaspi.ru')
            ->setLogin('dev.php')
            ->setNick('🐘 Php developer')
            ->setRoles(User::ROLE_DEFAULT);
        $user->setPassword($this->getPasswordHash($user, 'secret123'));
        $this->addReference('dev.php', $user);
        $manager->persist($user);

        $userAdmin = (new User())
            ->setEmail('admin@kaspi.ru')
            ->setLogin('admin')
            ->setNick('🔮 Administrator')
            ->setRoles([User::ROLE_ADMIN]);
        $userAdmin->setPassword($this->getPasswordHash($userAdmin, 'secret321'));
        $this->addReference('admin', $userAdmin);
        $manager->persist($userAdmin);

        $manager->flush();
    }

    private function getPasswordHash(User $user, string $password): string
    {
        return $this->userPasswordHasher->hashPassword($user, $password);
    }
}
