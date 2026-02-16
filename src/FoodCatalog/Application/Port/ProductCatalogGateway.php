<?php

declare(strict_types=1);

namespace App\FoodCatalog\Application\Port;

interface ProductCatalogGateway
{
    /**
     * @return list<array<string, mixed>>
     */
    public function search(ProductSearchQuery $query): array;
}

