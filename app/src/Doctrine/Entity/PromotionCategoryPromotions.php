<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Promotion;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'app_promotion_category_promotions')]
#[ORM\Entity]
class PromotionCategoryPromotions
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\JoinColumn(name: 'promotion_category_id', referencedColumnName: 'id', nullable: false, onDelete: 'cascade')]
    #[ORM\ManyToOne(targetEntity: PromotionCategory::class, cascade: ['persist'])]
    private PromotionCategory $promotionCategory;

    #[ORM\JoinColumn(name: 'promotion_id', referencedColumnName: 'id', nullable: false, onDelete: 'cascade')]
    #[ORM\ManyToOne(targetEntity: Promotion::class, cascade: ['persist'])]
    private Promotion $promotion;

    #[ORM\Column(name: 'priority', type: Types::INTEGER, nullable: false)]
    private int $priority;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPromotionCategory(): ?PromotionCategory
    {
        return $this->promotionCategory;
    }

    public function setPromotionCategory(PromotionCategory $promotionCategory): void
    {
        $this->promotionCategory = $promotionCategory;
    }

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function setPromotion(Promotion $promotion): void
    {
        $this->promotion = $promotion;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): void
    {
        $this->priority = $priority;
    }
}
