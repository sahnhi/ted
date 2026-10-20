<?php

namespace App\Doctrine\Entity\Sylius;

use Sylius\Component\Product\Model\ProductOptionValueTranslationInterface as BaseProductOptionValueTranslationInterface;

interface ProductOptionValueTranslationInterface extends BaseProductOptionValueTranslationInterface
{
    public function getHelp(): ?string;

    public function setHelp(string $help): void;

    public function getDescription(): ?string;

    public function setDescription(string $description): void;

    public function getOptgroup(): ?int;

    public function setOptgroup(int $optgroup): void;
}
