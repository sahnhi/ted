<?php

namespace App\Doctrine\Entity\Sylius;

use Sylius\Component\Product\Model\ProductOptionValueInterface as BaseProductOptionValueInterface;

interface ProductOptionValueInterface extends BaseProductOptionValueInterface
{
    public function getHelp(): ?string;

    public function setHelp(string $help): void;

    public function getDescription(): ?string;

    public function setDescription(string $description): void;

    public function getEnabled(): ?bool;

    public function setEnabled(bool $enabled): void;

    public function getPosition(): ?int;

    public function setPosition(?int $position = null): void;
}
