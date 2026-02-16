<?php

declare(strict_types=1);

namespace App\FoodCatalog\Application\Port;

final readonly class ProductSearchResult
{
    /**
     * @param list<ProductView> $products
     * @param array<string, int> $aggregations
     */
    public function __construct(
        public array $products,
        public int $total,
        public int $page,
        public int $limit,
        public array $aggregations,
        public bool $degraded = false,
        public ?string $degradationReason = null,
    ) {
    }

    /**
     * @param list<ProductView> $products
     * @param array<string, int> $aggregations
     */
    public static function success(array $products, int $total, int $page, int $limit, array $aggregations): self
    {
        return new self(
            products: $products,
            total: $total,
            page: $page,
            limit: $limit,
            aggregations: $aggregations,
            degraded: false,
            degradationReason: null,
        );
    }

    /**
     * @param list<ProductView> $products
     * @param array<string, int> $aggregations
     */
    public static function degraded(
        array $products,
        int $total,
        int $page,
        int $limit,
        array $aggregations,
        string $reason,
    ): self {
        return new self(
            products: $products,
            total: $total,
            page: $page,
            limit: $limit,
            aggregations: $aggregations,
            degraded: true,
            degradationReason: $reason,
        );
    }

    /**
     * @return array{
     *     products: list<array{code: string, name: string, brand: ?string, nutriScore: ?string, novaGroup: ?int, imageUrl: ?string}>,
     *     total: int,
     *     page: int,
     *     limit: int,
     *     aggregations: array<string, int>,
     *     degraded: bool,
     *     degradationReason: ?string
     * }
     */
    public function toArray(): array
    {
        return [
            'products' => array_map(static fn (ProductView $product): array => $product->toArray(), $this->products),
            'total' => $this->total,
            'page' => $this->page,
            'limit' => $this->limit,
            'aggregations' => $this->aggregations,
            'degraded' => $this->degraded,
            'degradationReason' => $this->degradationReason,
        ];
    }

    /**
     * @param array{
     *     products: list<array{code: string, name: string, brand: ?string, nutriScore: ?string, novaGroup: ?int, imageUrl: ?string}>,
     *     total: int,
     *     page: int,
     *     limit: int,
     *     aggregations: array<string, int>,
     *     degraded: bool,
     *     degradationReason: ?string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            products: array_map(static fn (array $row): ProductView => ProductView::fromArray($row), $data['products']),
            total: $data['total'],
            page: $data['page'],
            limit: $data['limit'],
            aggregations: $data['aggregations'],
            degraded: $data['degraded'],
            degradationReason: $data['degradationReason'],
        );
    }
}
