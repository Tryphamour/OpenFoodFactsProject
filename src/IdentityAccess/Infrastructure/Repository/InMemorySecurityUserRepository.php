<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Repository;

use App\IdentityAccess\Application\Port\SecurityUserRepository;
use App\IdentityAccess\Domain\Model\User\SecurityUser;
use App\IdentityAccess\Infrastructure\Security\InMemoryIdentityStore;

final readonly class InMemorySecurityUserRepository implements SecurityUserRepository
{
    public function __construct(private InMemoryIdentityStore $store)
    {
    }

    public function getById(string $userId): SecurityUser
    {
        $user = $this->store->getSecurityUserById($userId);
        if ($user === null) {
            throw new \RuntimeException(sprintf('Security user "%s" not found.', $userId));
        }

        return $user;
    }

    public function save(SecurityUser $user): void
    {
        $this->store->saveSecurityUser($user);
    }
}

