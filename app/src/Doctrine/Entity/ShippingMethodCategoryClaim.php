<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'app_shipping_method_category_claim')]
class ShippingMethodCategoryClaim implements ResourceInterface, TranslatableInterface
{
    use TimestampableEntity;

    use TranslatableTrait {
        __construct as initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'enabled', type: Types::BOOLEAN, options: ['default' => false])]
    private bool $enabled = false;

    #[ORM\Column(name: 'pushed', type: Types::BOOLEAN, options: ['default' => false])]
    private bool $pushed = false;

    #[ORM\ManyToOne(targetEntity: ShippingMethodCategory::class, inversedBy: 'claims')]
    #[ORM\JoinColumn(name: 'shipping_method_category_id', referencedColumnName: 'id', nullable: false)]
    private ShippingMethodCategory|null $shippingMethodCategory = null;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isPushed(): bool
    {
        return $this->pushed;
    }

    public function setPushed(bool $pushed): self
    {
        $this->pushed = $pushed;

        return $this;
    }

    public function getShippingMethodCategory(): ?ShippingMethodCategory
    {
        return $this->shippingMethodCategory;
    }

    public function setShippingMethodCategory(?ShippingMethodCategory $shippingMethodCategory): self
    {
        $this->shippingMethodCategory = $shippingMethodCategory;

        return $this;
    }

    protected function createTranslation(): TranslationInterface
    {
        return new ShippingMethodCategoryClaimTranslation();
    }
}
