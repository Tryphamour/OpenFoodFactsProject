<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCase\GetDashboard;

use App\Dashboard\Application\Port\DashboardRepository;
use App\Dashboard\Domain\Model\Dashboard;

final readonly class GetDashboardHandler
{
    public function __construct(private DashboardRepository $dashboards)
    {
    }

    public function handle(GetDashboardQuery $query): Dashboard
    {
        return $this->dashboards->getForOwner($query->ownerId);
    }
}
