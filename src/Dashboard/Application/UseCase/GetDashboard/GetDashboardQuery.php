<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCase\GetDashboard;

final readonly class GetDashboardQuery
{
    public function __construct(public string $ownerId)
    {
    }
}

