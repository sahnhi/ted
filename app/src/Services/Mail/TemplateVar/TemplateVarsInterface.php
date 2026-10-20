<?php

declare(strict_types=1);

namespace App\Services\Mail\TemplateVar;

interface TemplateVarsInterface
{
    public function getTemplateName(): string;

    public function toArray(): array;
}
