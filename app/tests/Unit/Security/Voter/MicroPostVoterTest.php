<?php
declare(strict_types=1);

use App\Entity\MicroPost;
use App\Entity\User;
use App\Security\Voter\MicroPostVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class MicroPostVoterTest extends TestCase
{
    public function testMicroPostVoterForAdminSuccess(): void
    {
        $userAdmin = new User();
        $userAdmin->setRoles([User::ROLE_ADMIN]);

        $token = self::createMock(TokenInterface::class);
        $token->expects(self::never())->method('getUser');

        $accessDecisionManager = self::createMock(AccessDecisionManagerInterface::class);
        $accessDecisionManager->expects(self::once())
            ->method('decide')->with($token)
            ->willReturn(true);

        $vote = (new MicroPostVoter($accessDecisionManager))
            ->vote($token, $this->createMicroPost($userAdmin), [MicroPostVoter::EDIT_DEL_OWNER_OR_ADMIN]);

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
            ->vote($token, $this->createMicroPost($user), [MicroPostVoter::EDIT_DEL_OWNER_OR_ADMIN]);

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
            ->vote($token, $this->createMicroPost($userPostOwner), [MicroPostVoter::EDIT_DEL_OWNER_OR_ADMIN]);

        self::assertEquals(VoterInterface::ACCESS_DENIED, $vote);
    }

    protected function createMicroPost(User $ownerUser): MicroPost
    {
        $post = new MicroPost();
        $post->setUser($ownerUser);

        return $post;
    }
}
