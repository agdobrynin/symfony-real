<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\MicroPost;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MicroPostVoter extends Voter
{
    public const MICRO_POST_EDIT_DEL_OWNER_OR_ADMIN = 'MICRO_POST_EDIT_DEL_OWNER_OR_ADMIN';

    private $accessDecisionManager;

    public function __construct(AccessDecisionManagerInterface $accessDecisionManager)
    {
        $this->accessDecisionManager = $accessDecisionManager;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // https://symfony.com/doc/current/security/voters.html
        return self::MICRO_POST_EDIT_DEL_OWNER_OR_ADMIN === $attribute
            && $subject instanceof MicroPost;
    }

    /**
     * @param string $attribute
     * @param MicroPost $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User|UserInterface $user */
        $user = $token->getUser();

        // if the user is anonymous or not App\Entity\User , do not grant access
        if (!$user instanceof User) {
            return false;
        }

        if ($this->accessDecisionManager->decide($token, [User::ROLE_ADMIN])) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        return $subject->getUser() && $subject->getUser()->getUuid() === $user->getUuid();
    }
}
