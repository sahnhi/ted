<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\AbstractTranslation;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'app_product_cms_claims_translation')]
#[ORM\Entity]
class ProductCMSClaimsTranslation extends AbstractTranslation implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'claim', type: 'string', length: 255, nullable: false)]
    private string $claim;

    public function getId(): int
    {
        return $this->id;
    }

    public function getClaim(): string
    {
        return $this->claim;
    }

    public function setClaim(string $claim): self
    {
        $this->claim = $claim;

        return $this;
    }
}
