<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Port;

use App\Dashboard\Domain\Model\Dashboard;

interface DashboardRepository
{
    public function getForOwner(string $ownerId): Dashboard;

    public function save(Dashboard $dashboard): void;
}
