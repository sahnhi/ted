<?php

declare(strict_types=1);

namespace App\Event\ProductFeedFile;

use Symfony\Contracts\EventDispatcher\Event;

final class PreValidatorEvent extends Event
{
    public const NAME = 'product_feed_file.pre_validator';

    public function __construct(
        protected string $file
    ) {
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function setFile(string $file): void
    {
        $this->file = $file;
    }
}
