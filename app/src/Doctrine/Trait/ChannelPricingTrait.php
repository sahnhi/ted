<?php

declare(strict_types=1);

namespace App\Doctrine\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Setono\SyliusCatalogPromotionPlugin\Model\ChannelPricingTrait as CatalogPromotionChannelPricingTrait;

/**
 * This trait is here to provide PHP Attributes support of the sylius-catalog-promotion-plugin trait which uses annotations.
 */
trait ChannelPricingTrait
{
    use CatalogPromotionChannelPricingTrait;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    protected bool $manuallyDiscounted = false;

    /**
     * @var float
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 4, options: ['default' => 1])]
    protected $multiplier = 1;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $bulkIdentifier = null;

    /**
     * @var array<array-key, string>
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    protected array $appliedPromotions = [];

    /**
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Gedmo\Timestampable(on: 'create')]
    protected $createdAt;

    /**
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    protected $updatedAt;
}
