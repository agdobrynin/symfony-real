<?php
declare(strict_types=1);

namespace App\Tests\Unit\Event;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Event\LikeNotifyByEmailEvent;
use PHPUnit\Framework\TestCase;

class LikeNotifyByEmailEventTest extends TestCase
{
    public function testEventConstructorAndMethods(): void
    {
        $post = (new MicroPost())->setContent('QWERTY');
        $likeByUser = (new User())->setNick('IVAN');
        $event = new LikeNotifyByEmailEvent($post, $likeByUser);

        self::assertInstanceOf(MicroPost::class, $event->getMicroPost());
        self::assertEquals('QWERTY', $event->getMicroPost()->getContent());
        self::assertInstanceOf(User::class, $event->getLikedByUser());
        self::assertEquals('IVAN', $event->getLikedByUser()->getNick());
    }
}
