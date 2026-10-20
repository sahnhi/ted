<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\AbstractTranslation;

#[ORM\Entity]
#[ORM\Table(name: 'app_shipping_category_translation')]
class ShippingCategoryTranslation extends AbstractTranslation implements ShippingCategoryTranslationInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int|null $id = null;

    #[ORM\Column(name: 'claims', type: Types::TEXT)]
    private string|null $claims = null;

    public function getId(): int|null
    {
        return $this->id;
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
