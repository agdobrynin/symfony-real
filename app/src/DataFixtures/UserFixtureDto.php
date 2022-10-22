<?php

namespace App\DataFixtures;

class UserFixtureDto
{
    public $login;
    public $emoji;
    public $isAdmin;

    public function __construct(string $login, string $emoji = '🔔', bool $isAdmin = false)
    {
        $this->login = $login;
        $this->emoji = $emoji;
        $this->isAdmin = $isAdmin;
    }
}
