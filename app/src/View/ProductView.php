<?php

namespace App\View;

class ProductView
{
    public ?string $code = null;
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $breadcrumb = null;
    public ?string $description = null;
    public ?string $shortDescription = null;
    public ?string $metaKeywords = null;
    public ?string $metaDescription = null;
    public ?string $averageRating = null;

    // custom
    public ?string $numberOfRating = null;
    // end custom

    public ?ProductTaxonView $taxons = null;
    public array $variants = [];
    public array $attributes = [];
    public array $associations = [];
    public array $images = [];
    public array $options = [];
    public array $ids = [];
    public ?string $url = null;
    public ?int $minBound = null;
    public ?int $maxBound = null;
    public ?int $warranty = null;
    public ?int $originalLowerPrice = null;
    public ?int $lowerPrice = null;
    public ?string $gtin = null;
    public ?string $joinStoryLink = null;
    public ?string $bundleType = null;

    /**
     * @var RelatedProductView[]
     */
    public array $relatedProducts = [];

    public function __construct()
    {
        $this->taxons = new ProductTaxonView();
    }

    public function getReviewTaxon(): ?string
    {
        return $this->taxons->review;
    }
}
