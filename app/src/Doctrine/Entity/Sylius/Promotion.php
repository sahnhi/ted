<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\PromotionCategoryPromotions;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PromotionRepository;
use Sylius\Component\Core\Model\Promotion as BasePromotion;
use Sylius\Component\Promotion\Model\PromotionRule;

#[ORM\Table(name: 'sylius_promotion')]
#[ORM\Entity(repositoryClass: PromotionRepository::class)]
class Promotion extends BasePromotion implements \Stringable
{
    public const REFUSE_TAXON_PROMOTION_RULE = 'refuse_taxon';

    protected $couponBased = true;
    protected $exclusive = true;

    #[ORM\Column(name: 'exceptional', type: 'boolean')]
    protected bool $exceptional = false;

    #[ORM\OneToMany(mappedBy: 'promotion', targetEntity: PromotionCategoryPromotions::class, cascade: ['all'], orphanRemoval: true)]
    private ?Collection $promotionCategoryPromotions = null;

    #[ORM\Column(name: 'automatically_applied', type: Types::BOOLEAN, options: ['default' => true])]
    protected bool $automaticallyApplied = true;

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function isExceptional(): ?bool
    {
        return $this->exceptional;
    }

    public function setExceptional(bool $exceptional): void
    {
        $this->exceptional = $exceptional;
    }

    public function hasPromotionRule(string $promotionRuleType): bool
    {
        foreach ($this->getRules() as $rule) {
            if ($rule->getType() === $promotionRuleType) {
                return true;
            }
        }

        return false;
    }

    public function addDefaultPromotionRule(): void
    {
        if ($this->hasPromotionRule(self::REFUSE_TAXON_PROMOTION_RULE)) {
            return;
        }

        $promotionRule = new PromotionRule();
        $promotionRule->setType(self::REFUSE_TAXON_PROMOTION_RULE);
        $promotionRule->setConfiguration(['taxons' => ['bundle']]);
        $promotionRule->setPromotion($this);

        $this->addRule($promotionRule);
    }

    public function getPromotionCategoryPromotions(): ?Collection
    {
        return $this->promotionCategoryPromotions;
    }

    public function isAutomaticallyApplied(): bool
    {
        return $this->automaticallyApplied;
    }

    public function setAutomaticallyApplied(bool $automaticallyApplied): self
    {
        $this->automaticallyApplied = $automaticallyApplied;

        return $this;
    }
}
