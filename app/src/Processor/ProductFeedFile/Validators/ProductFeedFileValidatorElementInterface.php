<?php

declare(strict_types=1);

namespace App\Processor\ProductFeedFile\Validators;

interface ProductFeedFileValidatorElementInterface
{
    public function validate(string $file): bool;
}
