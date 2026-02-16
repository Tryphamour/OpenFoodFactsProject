<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\Model\Widget;

final class Widget
{
    /**
     * @param array<string, scalar> $configuration
     */
    private function __construct(
        private readonly string $id,
        private readonly string $type,
        private int $position = 0,
        private array $configuration = [],
    ) {
        if ($id === '') {
            throw new \InvalidArgumentException('Widget id cannot be empty.');
        }

        if ($type === '') {
            throw new \InvalidArgumentException('Widget type cannot be empty.');
        }
    }

    /**
     * @param array<string, scalar> $configuration
     */
    public static function create(string $id, string $type, array $configuration = []): self
    {
        return new self($id, $type, 0, $configuration);
    }

    /**
     * @param array<string, scalar> $configuration
     */
    public static function reconstitute(string $id, string $type, int $position, array $configuration = []): self
    {
        return new self($id, $type, $position, $configuration);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function position(): int
    {
        return $this->position;
    }

    public function placeAt(int $position): void
    {
        if ($position < 0) {
            throw new \InvalidArgumentException('Widget position cannot be negative.');
        }

        $this->position = $position;
    }

    /**
     * @return array<string, scalar>
     */
    public function configuration(): array
    {
        return $this->configuration;
    }

    /**
     * @param array<string, scalar> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->configuration = $configuration;
    }
}
