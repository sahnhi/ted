<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\AbstractTranslation;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'app_product_cms_tags_translation')]
#[ORM\Entity]
class ProductCMSTagsTranslation extends AbstractTranslation implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(name: 'primary_tag', type: 'string', nullable: true)]
    private ?string $primaryTag = null;

    #[ORM\Column(name: 'secondary_tag', type: 'string', nullable: true)]
    private ?string $secondaryTag = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPrimaryTag(): ?string
    {
        return $this->primaryTag;
    }

    public function setPrimaryTag(?string $primaryTag): self
    {
        $this->primaryTag = $primaryTag;

        return $this;
    }

    public function getSecondaryTag(): ?string
    {
        return $this->secondaryTag;
    }

    public function setSecondaryTag(?string $secondaryTag): self
    {
        $this->secondaryTag = $secondaryTag;

        return $this;
    }
}
