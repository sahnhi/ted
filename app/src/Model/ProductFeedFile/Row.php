<?php

declare(strict_types=1);

namespace App\Model\ProductFeedFile;

use Symfony\Component\Serializer\Annotation\SerializedName;

final class Row
{
    private string $id;

    /**
     * @SerializedName("titre")
     */
    private string $title;

    private string $description;

    /**
     * @SerializedName("lien")
     */
    private string $link;

    /**
     * @SerializedName("état")
     */
    private string $state;

    /**
     * @SerializedName("identifiant promotion")
     */
    private string $promotionCode;

    /**
     * @SerializedName("prix")
     */
    private string $price;

    /**
     * @SerializedName("disponibilité")
     */
    private string $availability;

    /**
     * @SerializedName("lien image")
     */
    private string $mainImageLink;

    private array $additionalImageLink = [];

    /**
     * @SerializedName("gtin")
     */
    private string $gtin;

    /**
     * @SerializedName("identifiant existe")
     */
    private string $identityExists;

    /**
     * @SerializedName("référence fabricant")
     */
    private string $factoryReference;

    /**
     * @SerializedName("identifiant groupe")
     */
    private string $groupReference;

    /**
     * @SerializedName("système de tailles")
     */
    private string $sizingPattern;

    /**
     * @SerializedName("taille")
     */
    private string $size;

    /**
     * @SerializedName("couleur")
     */
    private string $color;

    /**
     * @SerializedName("est un lot")
     */
    private string $isBundle;

    /**
     * @SerializedName("marque")
     */
    private string $brand;

    /**
     * @SerializedName("catégorie de produits google")
     */
    private string $googleProductCategory;

    /**
     * @SerializedName("product_type")
     */
    private string $productType;

    /**
     * @SerializedName("livraison")
     */
    private string $shipping;

    /**
     * @SerializedName("étiquette personnalisée 0")
     */
    private string $customShippingTag;

    /**
     * @SerializedName("product_highlight")
     */
    private string $highlight;

    /**
     * @SerializedName("matière")
     */
    private string $material;

    private array $customTag = [];

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getPromotionCode(): string
    {
        return $this->promotionCode;
    }

    public function setPromotionCode(string $promotionCode): self
    {
        $this->promotionCode = $promotionCode;

        return $this;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getAvailability(): string
    {
        return $this->availability;
    }

    public function setAvailability(string $availability): self
    {
        $this->availability = $availability;

        return $this;
    }

    public function getMainImageLink(): string
    {
        return $this->mainImageLink;
    }

    public function setMainImageLink(string $mainImageLink): self
    {
        $this->mainImageLink = $mainImageLink;

        return $this;
    }

    public function getAdditionalImageLink(): array
    {
        return $this->additionalImageLink;
    }

    public function setAdditionalimagelink(string $additionalimagelink): self
    {
        $this->additionalImageLink[] = $additionalimagelink;

        return $this;
    }

    public function getGtin(): string
    {
        return $this->gtin;
    }

    public function setGtin(string $gtin): self
    {
        $this->gtin = $gtin;

        return $this;
    }

    public function getIdentityExists(): string
    {
        return $this->identityExists;
    }

    public function setIdentityExists(string $identityExists): self
    {
        $this->identityExists = $identityExists;

        return $this;
    }

    public function getFactoryReference(): string
    {
        return $this->factoryReference;
    }

    public function setFactoryReference(string $factoryReference): self
    {
        $this->factoryReference = $factoryReference;

        return $this;
    }

    public function getGroupReference(): string
    {
        return $this->groupReference;
    }

    public function setGroupReference(string $groupReference): self
    {
        $this->groupReference = $groupReference;

        return $this;
    }

    public function getSizingPattern(): string
    {
        return $this->sizingPattern;
    }

    public function setSizingPattern(string $sizingPattern): self
    {
        $this->sizingPattern = $sizingPattern;

        return $this;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function setSize(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getIsBundle(): string
    {
        return $this->isBundle;
    }

    public function setIsBundle(string $isBundle): self
    {
        $this->isBundle = $isBundle;

        return $this;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getGoogleProductCategory(): string
    {
        return $this->googleProductCategory;
    }

    public function setGoogleProductCategory(string $googleProductCategory): self
    {
        $this->googleProductCategory = $googleProductCategory;

        return $this;
    }

    public function getProductType(): string
    {
        return $this->productType;
    }

    public function setProductType(string $productType): self
    {
        $this->productType = $productType;

        return $this;
    }

    public function getShipping(): string
    {
        return $this->shipping;
    }

    public function setShipping(string $shipping): self
    {
        $this->shipping = $shipping;

        return $this;
    }

    public function getCustomShippingTag(): string
    {
        return $this->customShippingTag;
    }

    public function setCustomShippingTag(string $customShippingTag): self
    {
        $this->customShippingTag = $customShippingTag;

        return $this;
    }

    public function getMaterial(): string
    {
        return $this->material;
    }

    public function setMaterial(string $material): self
    {
        $this->material = $material;

        return $this;
    }

    public function getCustomTag(): array
    {
        return $this->customTag;
    }

    public function setCustomTag(string $customTag): self
    {
        $this->customTag[] = $customTag;

        return $this;
    }

    public function __set(string $key, string $value): void
    {
        if (false !== mb_strpos($key, 'lien image supplémentaire ')) {
            $this->setAdditionalimagelink($value);
        }

        if (false !== mb_strpos($key, 'étiquette personnalisée ')) {
            $this->setCustomTag($value);
        }
    }

    public function setHighlight(string $highlight): self
    {
        $this->highlight = $highlight;

        return $this;
    }

    public function getHighlight(): string
    {
        return $this->highlight;
    }
}
