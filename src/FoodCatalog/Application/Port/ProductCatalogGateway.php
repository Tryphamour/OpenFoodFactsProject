<?php

declare(strict_types=1);

namespace App\FoodCatalog\Application\Port;

interface ProductCatalogGateway
{
    public function search(ProductSearchQuery $query): ProductSearchResult;
}
