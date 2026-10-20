<?php

declare(strict_types=1);

namespace App\Processor\ProductFeedFile;

interface ProductFeedFileValidatorInterface
{
    public function validate(string $file): void;
}
