<?php

namespace App\Doctrine\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslationInterface;

interface FamiliarityTranslationInterface extends ResourceInterface, TranslationInterface
{
    public function getWording(): ?string;

    public function setWording(?string $wording): void;
}
