<?php

namespace App\DataFixtures;

class UserFixtureDto
{
    public $login;
    public $nick;
    public $emoji;
    public $isAdmin;

    public function __construct(string $login, string $nick, string $emoji = '🔔', bool $isAdmin = false)
    {
        $this->login = $login;
        $this->nick = $nick;
        $this->emoji = $emoji;
        $this->isAdmin = $isAdmin;
    }
}
