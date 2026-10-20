<?php

namespace App\Doctrine\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'app_promotion_category')]
#[ORM\Entity]
class PromotionCategory implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'code', type: Types::STRING, unique: true)]
    private string|null $code = null;
    #[ORM\Column(name: 'name', type: Types::STRING)]
    private string|null $name = null;

    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    private string|null $description = null;
    #[ORM\Column(name: 'enabled', type: Types::BOOLEAN, options: ['default' => false])]
    private bool $enabled = false;

    #[ORM\OneToMany(mappedBy: 'promotionCategory', targetEntity: PromotionCategoryPromotions::class, cascade: ['all'], orphanRemoval: true)]
    private ?Collection $promotionCategoryPromotions = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
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

    public function getPromotionCategoryPromotions(): ?Collection
    {
        return $this->promotionCategoryPromotions;
    }

    public function addPromotionCategoryPromotion(PromotionCategoryPromotions $promotionCategoryPromotions): void
    {
        if (!$this->promotionCategoryPromotions->contains($promotionCategoryPromotions)) {
            $this->promotionCategoryPromotions->add($promotionCategoryPromotions);
            $promotionCategoryPromotions->setPromotionCategory($this);
        }
    }

    public function removePromotionCategoryPromotion(PromotionCategoryPromotions $promotionCategoryPromotions): void
    {
        if ($this->promotionCategoryPromotions->contains($promotionCategoryPromotions)) {
            $this->promotionCategoryPromotions->removeElement($promotionCategoryPromotions);
        }
    }
}
