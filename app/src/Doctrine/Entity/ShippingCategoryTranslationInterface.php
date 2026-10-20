<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslationInterface;

interface ShippingCategoryTranslationInterface extends ResourceInterface, TranslationInterface
{
    public function getClaims(): string|null;

    public function setClaims(string|null $claims): self;
}
