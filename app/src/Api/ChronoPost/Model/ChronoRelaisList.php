<?php

declare(strict_types=1);

namespace App\Api\ChronoPost\Model;

final class ChronoRelaisList implements \JsonSerializable
{
    public function __construct(
        /** @var ChronoRelais[] */
        private readonly array $chronoRelais
    ) {
    }

    public function toArray(): array
    {
        return array_map(static fn (ChronoRelais $chronoRelais): array => $chronoRelais->toArray(), $this->chronoRelais);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
