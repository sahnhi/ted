<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\AbstractTranslation;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Entity]
#[ORM\Table(name: 'app_shipping_method_category_translation')]
class ShippingMethodCategoryTranslation extends AbstractTranslation implements ResourceInterface
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int|null $id = null;

    #[ORM\Column(type: Types::STRING, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    private string|null $description = null;

    #[ORM\Column(name: 'claims', type: Types::TEXT, nullable: true)]
    private string|null $claims = null;

    public function getId(): int|null
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    public function setDescription(string|null $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getClaims(): string|null
    {
        return $this->claims;
    }

    public function setClaims(string|null $claims): self
    {
        $this->claims = $claims;

        return $this;
    }
}
