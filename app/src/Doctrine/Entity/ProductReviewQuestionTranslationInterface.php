<?php

namespace App\Doctrine\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslationInterface;

interface ProductReviewQuestionTranslationInterface extends ResourceInterface, TranslationInterface
{
    public function getId(): ?int;

    public function setName(string $name): void;

    public function getName(): ?string;
}
