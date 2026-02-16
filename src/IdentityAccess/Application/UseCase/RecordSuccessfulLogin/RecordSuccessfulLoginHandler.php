<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\UseCase\RecordSuccessfulLogin;

use App\IdentityAccess\Application\Port\SecurityUserRepository;

final readonly class RecordSuccessfulLoginHandler
{
    public function __construct(private SecurityUserRepository $users)
    {
    }

    public function handle(RecordSuccessfulLoginCommand $command): void
    {
        $user = $this->users->getById($command->userId);
        $user->recordSuccessfulLogin();
        $this->users->save($user);
    }
}

