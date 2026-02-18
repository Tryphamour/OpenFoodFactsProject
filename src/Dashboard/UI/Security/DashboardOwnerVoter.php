<?php

declare(strict_types=1);

namespace App\Dashboard\UI\Security;

use App\IdentityAccess\Infrastructure\Security\AppUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

final class DashboardOwnerVoter extends Voter
{
    public const string VIEW = 'DASHBOARD_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && \is_string($subject) && $subject !== '';
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof AppUser) {
            return false;
        }

        if (\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        return $user->id() === $subject;
    }
}
