<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'second_factor_challenges')]
#[ORM\Index(name: 'idx_second_factor_user_id', columns: ['user_id'])]
class SecondFactorChallengeRecord
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string', length: 64)]
        public string $id,
        #[ORM\Column(type: 'string', length: 64, name: 'user_id')]
        public string $userId,
        #[ORM\Column(type: 'string', length: 255, name: 'code_hash')]
        public string $codeHash,
        #[ORM\Column(type: 'datetime_immutable', name: 'expires_at')]
        public \DateTimeImmutable $expiresAt,
        #[ORM\Column(type: 'integer', name: 'max_attempts')]
        public int $maxAttempts,
        #[ORM\Column(type: 'integer')]
        public int $attempts = 0,
        #[ORM\Column(type: 'datetime_immutable', nullable: true, name: 'verified_at')]
        public ?\DateTimeImmutable $verifiedAt = null,
    ) {
    }
}

