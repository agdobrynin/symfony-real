<?php

namespace App\DataFixtures;

class UserFixtureDto
{
    public $login;
    public $icon;
    public $isAdmin;

    public function __construct(string $login, string $icon = '🔔', bool $isAdmin = false)
    {
        $this->login = $login;
        $this->icon = $icon;
        $this->isAdmin = $isAdmin;
    }
}
