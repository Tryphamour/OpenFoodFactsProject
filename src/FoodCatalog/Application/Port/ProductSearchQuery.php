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
        if ($this->page < 1) {
            throw new \InvalidArgumentException('Search page must be >= 1.');
        }

        if ($this->limit < 1 || $this->limit > 50) {
            throw new \InvalidArgumentException('Search limit must be between 1 and 50.');
        }
    }

    /**
     * @return array<string, scalar>
     */
    public function normalizedFilters(): array
    {
        $filters = $this->filters;
        ksort($filters);

        return $filters;
    }
}
