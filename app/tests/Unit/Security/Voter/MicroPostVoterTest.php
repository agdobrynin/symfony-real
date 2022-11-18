<?php
declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Security\Voter\MicroPostVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class MicroPostVoterTest extends TestCase
{
    public function testMicroPostVoterForAdminSuccess(): void
    {
        $userAdmin = new User();
        $userAdmin->setRoles([User::ROLE_ADMIN]);

        $token = self::createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn($userAdmin);

        $accessDecisionManager = self::createMock(AccessDecisionManagerInterface::class);
        $accessDecisionManager->expects(self::once())
            ->method('decide')->with($token)
            ->willReturn(true);

        $vote = (new MicroPostVoter($accessDecisionManager))
            ->vote($token, $this->createMicroPost($userAdmin), [MicroPostVoter::MICRO_POST_EDIT_DEL_OWNER_OR_ADMIN]);

        self::assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
    }

    public function testMicroPostVoterForUserSuccess(): void
    {
        $user = new User();
        $user->setRoles([User::ROLE_USER]);

        $token = self::createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn($user);

        $accessDecisionManager = self::createMock(AccessDecisionManagerInterface::class);
        $accessDecisionManager->expects(self::once())
            ->method('decide')->with($token)
            ->willReturn(false);

        $vote = (new MicroPostVoter($accessDecisionManager))
            ->vote($token, $this->createMicroPost($user), [MicroPostVoter::MICRO_POST_EDIT_DEL_OWNER_OR_ADMIN]);

        self::assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
    }

    public function testMicroPostVoterForUserFail(): void
    {
        $user = new User();
        $user->setRoles([User::ROLE_USER]);
        $userPostOwner = new User();
        $userPostOwner->setRoles([User::ROLE_USER]);

        $token = self::createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn($user);

        $accessDecisionManager = self::createMock(AccessDecisionManagerInterface::class);
        $accessDecisionManager->expects(self::once())
            ->method('decide')->with($token)
            ->willReturn(false);

        $vote = (new MicroPostVoter($accessDecisionManager))
            ->vote($token, $this->createMicroPost($userPostOwner), [MicroPostVoter::MICRO_POST_EDIT_DEL_OWNER_OR_ADMIN]);

        self::assertEquals(VoterInterface::ACCESS_DENIED, $vote);
    }

    public function testMicroPostVoterForCustomUserFail(): void
    {
        $customUser = new class implements UserInterface {

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

        $token = self::createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn($customUser);

        $accessDecisionManager = self::createMock(AccessDecisionManagerInterface::class);
        $accessDecisionManager->expects(self::never())
            ->method('decide');

        $userPostOwner = new User();
        $userPostOwner->setRoles([User::ROLE_USER]);

        $vote = (new MicroPostVoter($accessDecisionManager))
            ->vote($token, $this->createMicroPost($userPostOwner), [MicroPostVoter::MICRO_POST_EDIT_DEL_OWNER_OR_ADMIN]);

        self::assertEquals(VoterInterface::ACCESS_DENIED, $vote);
    }

    protected function createMicroPost(User $ownerUser): MicroPost
    {
        $post = new MicroPost();
        $post->setUser($ownerUser);

        return $post;
    }
}
