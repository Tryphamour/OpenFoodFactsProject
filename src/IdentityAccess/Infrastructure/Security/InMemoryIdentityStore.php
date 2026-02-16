<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Domain\Model\TwoFactor\SecondFactorChallenge;
use App\IdentityAccess\Domain\Model\User\SecurityUser;

final class InMemoryIdentityStore
{
    /**
     * @var array<string, array{id: string, email: string, password_hash: string, roles: list<string>, security: SecurityUser}>
     */
    private array $usersById = [];

    /**
     * @var array<string, string>
     */
    private array $userIdByEmail = [];

    /**
     * @var array<string, SecondFactorChallenge>
     */
    private array $challengesById = [];

    public function __construct()
    {
        $this->seedDefaultAdmin();
    }

    public function getAppUserByEmail(string $email): ?AppUser
    {
        $id = $this->userIdByEmail[strtolower($email)] ?? null;
        if ($id === null) {
            return null;
        }

        return $this->toAppUser($this->usersById[$id]);
    }

    public function getSecurityUserById(string $userId): ?SecurityUser
    {
        if (!isset($this->usersById[$userId])) {
            return null;
        }

        return $this->usersById[$userId]['security'];
    }

    public function saveSecurityUser(SecurityUser $user): void
    {
        if (!isset($this->usersById[$user->id()])) {
            throw new \RuntimeException(sprintf('Unknown user id "%s".', $user->id()));
        }

        $this->usersById[$user->id()]['security'] = $user;
    }

    public function saveChallenge(SecondFactorChallenge $challenge): void
    {
        $this->challengesById[$challenge->id()] = $challenge;
    }

    public function getChallengeById(string $challengeId): ?SecondFactorChallenge
    {
        return $this->challengesById[$challengeId] ?? null;
    }

    private function seedDefaultAdmin(): void
    {
        $id = 'user_admin_1';
        $email = 'admin@example.com';
        $passwordHash = '$2y$12$1DhR3C6trehv1/qyG9DNPO.ffD8I/YMalDxrQZDVaJjFoIG3NT0Z6';

        $this->usersById[$id] = [
            'id' => $id,
            'email' => $email,
            'password_hash' => $passwordHash,
            'roles' => ['ROLE_ADMIN'],
            'security' => new SecurityUser($id),
        ];

        $this->userIdByEmail[strtolower($email)] = $id;
    }

    /**
     * @param array{id: string, email: string, password_hash: string, roles: list<string>, security: SecurityUser} $row
     */
    private function toAppUser(array $row): AppUser
    {
        return new AppUser($row['id'], $row['email'], $row['password_hash'], $row['roles']);
    }
}

