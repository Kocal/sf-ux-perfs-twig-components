<?php

namespace App\Security;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Editors edit every post, authors only their own.
 *
 * @extends Voter<string, Post>
 */
final class PostVoter extends Voter
{
    public const EDIT = 'POST_EDIT';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::EDIT === $attribute && $subject instanceof Post;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_EDITOR'])) {
            return true;
        }

        $user = $token->getUser();

        return $user instanceof User && null !== $user->getId() && $subject->getAuthor()?->getUser()?->getId() === $user->getId();
    }
}
