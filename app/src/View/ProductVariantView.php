<?php

namespace App\View;

class ProductVariantView
{
    public ?string $code = null;
    public ?string $name = null;
    public ?int $price = null;
    public ?int $originalPrice = null;
    public ?int $ecotax = null;
    public ?float $width = null;
    public ?float $height = null;
    public ?float $depth = null;
    /** @var ImageView[] */
    public array $images = [];
    public array $optionValues = [];
    public array $optionCodes = [];
    public array $optionTranslated = [];
    public array $packs = [];
    public ?string $product = null;
    public ?string $slug = null;
    public ?bool $enabled = null;
    public array $bundles = [];
    public array $gifted = [];
    public ?string $joinStoryLink = null;
}
