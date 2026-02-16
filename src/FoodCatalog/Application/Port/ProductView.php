<?php

declare(strict_types=1);

namespace App\FoodCatalog\Application\Port;

final readonly class ProductView
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $brand,
        public ?string $nutriScore,
        public ?int $novaGroup,
        public ?string $imageUrl,
    ) {
    }

    /**
     * @return array{code: string, name: string, brand: ?string, nutriScore: ?string, novaGroup: ?int, imageUrl: ?string}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'brand' => $this->brand,
            'nutriScore' => $this->nutriScore,
            'novaGroup' => $this->novaGroup,
            'imageUrl' => $this->imageUrl,
        ];
    }

    /**
     * @param array{code: string, name: string, brand: ?string, nutriScore: ?string, novaGroup: ?int, imageUrl: ?string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
            name: $data['name'],
            brand: $data['brand'],
            nutriScore: $data['nutriScore'],
            novaGroup: $data['novaGroup'],
            imageUrl: $data['imageUrl'],
        );
    }
}
