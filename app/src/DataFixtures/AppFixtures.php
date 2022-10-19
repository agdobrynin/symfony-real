<?php

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
        $this->loadMicroPost($manager);
        $this->loadUsers($manager);
    }

    private function loadMicroPost(ObjectManager $manager): void
    {
        $faker = Factory::create('ru_RU');

        for ($i = 0; $i < 10; $i++) {
            $microPost = new MicroPost();
            $microPost->setDate($faker->dateTimeBetween('-60 day', 'now'))
                ->setContent($faker->realTextBetween(120, 250));
            $manager->persist($microPost);
        }

        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('dev@kaspi.ru')
            ->setLogin('dev.php')
            ->setNick('🐘 Php developer')
            ->setPassword($this->userPasswordHasher->hashPassword($user, 'secret123'))
            ->setRoles(['ROLE_USER']);

        $manager->persist($user);
        $manager->flush();
    }
}
