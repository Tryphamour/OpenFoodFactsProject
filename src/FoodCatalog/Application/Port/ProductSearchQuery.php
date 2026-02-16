<?php

declare(strict_types=1);

namespace App\FoodCatalog\Application\Port;

final readonly class ProductSearchQuery
{
    /**
     * @param array<string, scalar> $filters
     */
    public function __construct(
        public string $term,
        public int $page = 1,
        public int $limit = 20,
        public array $filters = [],
        public ?string $sortBy = null,
    ) {
    }
}

