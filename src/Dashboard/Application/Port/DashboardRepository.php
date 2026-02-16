<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Port;

interface DashboardRepository
{
    public function existsForUser(string $userId): bool;
}
