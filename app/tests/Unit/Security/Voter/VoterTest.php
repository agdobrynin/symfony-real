<?php
declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Entity\User;
use App\Security\Voter\CommentVoter;
use App\Security\Voter\MicroPostVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class VoterTest extends TestCase
{
    public function dataProvider(): \Generator
    {
        $equalUser = (new User())->setRoles([User::ROLE_USER]);
        // MicroPost
        yield [
            (new User())->setRoles([User::ROLE_ADMIN]), // checked user
            (new MicroPost())->setUser((new User())->setRoles([User::ROLE_USER])),
            MicroPostVoter::MICRO_POST_EDIT_DEL_OWNER_OR_ADMIN,
            VoterInterface::ACCESS_GRANTED,
            MicroPostVoter::class,
        ];

        yield [
            $equalUser, // checked user
            (new MicroPost())->setUser($equalUser),
            MicroPostVoter::MICRO_POST_EDIT_DEL_OWNER_OR_ADMIN,
            VoterInterface::ACCESS_GRANTED,
            MicroPostVoter::class,
        ];

        yield [
            (new User())->setRoles([User::ROLE_USER]), // checked user
            (new MicroPost())->setUser((new User())->setRoles([User::ROLE_USER])),
            MicroPostVoter::MICRO_POST_EDIT_DEL_OWNER_OR_ADMIN,
            VoterInterface::ACCESS_DENIED,
            MicroPostVoter::class,
        ];

        yield [
            $this->getCustomUser(), // check user
            (new MicroPost())->setUser((new User())->setRoles([User::ROLE_USER])),
            MicroPostVoter::MICRO_POST_EDIT_DEL_OWNER_OR_ADMIN,
            VoterInterface::ACCESS_DENIED,
            MicroPostVoter::class,
        ];
        // Comment
        yield [
            $this->getCustomUser(), // check user
            (new Comment())->setUser((new User())->setRoles([User::ROLE_USER])),
            CommentVoter::COMMENT_DEL_OWNER_OR_ADMIN,
            VoterInterface::ACCESS_DENIED,
            CommentVoter::class,
        ];

        yield [
            (new User())->setRoles([User::ROLE_USER]), // check user
            (new Comment())->setUser((new User())->setRoles([User::ROLE_USER])),
            CommentVoter::COMMENT_DEL_OWNER_OR_ADMIN,
            VoterInterface::ACCESS_DENIED,
            CommentVoter::class,
        ];

        yield [
            (new User())->setRoles([User::ROLE_ADMIN]), // check user
            (new Comment())->setUser((new User())->setRoles([User::ROLE_USER])),
            CommentVoter::COMMENT_DEL_OWNER_OR_ADMIN,
            VoterInterface::ACCESS_GRANTED,
            CommentVoter::class,
        ];

        yield [
            $equalUser, // check user
            (new Comment())->setUser($equalUser),
            CommentVoter::COMMENT_DEL_OWNER_OR_ADMIN,
            VoterInterface::ACCESS_GRANTED,
            CommentVoter::class,
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testVoter($user, $subject, string $attribute, int $access, string $voterClass): void
    {
        $token = self::createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn($user);

        $accessDecisionManager = self::createMock(AccessDecisionManagerInterface::class);

        $expectCount = $user instanceof User ? self::once() : self::never();

        $isAdmin = \in_array(User::ROLE_ADMIN, $user->getRoles());

        $accessDecisionManager->expects($expectCount)
            ->method('decide')->with($token)
            ->willReturn($isAdmin);

        $vote = (new $voterClass($accessDecisionManager))
            ->vote($token, $subject, [$attribute]);

        self::assertEquals($access, $vote);
    }

    protected function getCustomUser(): UserInterface
    {
        return new class implements UserInterface {
            public function getUserIdentifier()
            {
                return 'super-admin';
            }

            public function getRoles()
            {
                return [User::ROLE_ADMIN];
            }

            public function getPassword()
            {
                return null;
            }

            public function getSalt()
            {
                return null;
            }

            public function eraseCredentials()
            {
            }

            public function getUsername()
            {
                return 'super-admin';
            }

            public function __call($name, $arguments)
            {
            }
        };
    }
}
