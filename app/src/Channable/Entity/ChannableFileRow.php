<?php

declare(strict_types=1);

namespace App\Channable\Entity;

use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="app_file_channable_row")
 */
class ChannableFileRow implements ResourceInterface
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(name="google_product_id", type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("ID")
     */
    private string $googleProductId;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("SKU")
     */
    private string $sku;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("GTIN")
     */
    private string $gtin;

    /**
     * @ORM\Column(name="parent_sku", type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Parent SKU")
     */
    private string $parentSku;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Titre")
     */
    private string $title;

    /**
     * @ORM\Column(name="url_product", type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("URL Product")
     */
    private string $urlProduct;

    /**
     * @ORM\Column(type="text")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Description")
     */
    private string $description;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Etat")
     */
    private string $state;

    /**
     * @ORM\Column(name="product_highlight", type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Product Highlight")
     */
    private ?string $productHighlight = null;

    /**
     * @ORM\Column(name="google_product_category", type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Google_product_category")
     */
    private string $googleProductCategory;

    /**
     * @ORM\Column(name="product_type", type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Product_type")
     */
    private string $productType;

    /**
     * @ORM\Column(name="is_bundle", type="boolean")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("est un lot")
     */
    private bool $isBundle;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Tranche d'âge")
     */
    private string $public;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Marque")
     */
    private string $brand;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Genre")
     */
    private string $gender;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Disponibilité")
     */
    private string $availability;

    /**
     * @ORM\Column(name="price_with_vat", type="integer")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Prix (TTC)")
     */
    private int $priceWithVat;

    /**
     * @ORM\Column(name="promotion_price_with_vat", type="integer", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Prix Soldé(TTC)")
     */
    private ?int $promotionPriceWithVat = null;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Devise")
     */
    private string $currency;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Shipping")
     */
    private string $shipping;

    /**
     * @ORM\Column(name="maximum_handling_time", type="integer", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Maximum handling time")
     */
    private ?int $maximumHandlingTime = 7;

    /**
     * @ORM\Column(name="minimum_handling_time", type="integer", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Minimum handling time")
     */
    private ?int $minimumHandlingTime = 1;

    /**
     * @ORM\Column(name="maximum_delivery_time", type="integer", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Maximum transit time")
     */
    private ?int $maximumDeliveryTime = 15;

    /**
     * @ORM\Column(name="minimum_delivery_time", type="integer", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Minimum transit time")
     */
    private ?int $minimumDeliveryTime = 3;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Couleur")
     */
    private ?string $color = null;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Taille")
     */
    private string $size;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Matière")
     */
    private string $material;

    /**
     * @ORM\Column(type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Epaisseur")
     */
    private string $thickness;

    /**
     * @ORM\Column(name="made_in", type="string")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Fabrication")
     */
    private string $madeIn;

    /**
     * @ORM\Column(name="expedition_country", type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Pays d'expédition")
     */
    private ?string $expeditionCountry = null;

    /**
     * @ORM\Column(name="product_details", type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Product detail")
     */
    private ?string $productDetails = null;

    /**
     * @ORM\Column(name="product_length", type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Product length")
     */
    private ?string $productLength = null;

    /**
     * @ORM\Column(name="product_width", type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Product width")
     */
    private ?string $productWidth = null;

    /**
     * @ORM\Column(name="product_height", type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Product height")
     */
    private ?string $productHeight = null;

    /**
     * @ORM\Column(name="product_weight", type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Product weight")
     */
    private ?string $productWeight = null;

    /**
     * @ORM\Column(name="packging_length", type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Longueur du colis")
     */
    private ?string $packgingLength = null;

    /**
     * @ORM\Column(name="packging_width", type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Largeur du colis")
     */
    private ?string $packgingWidth = null;

    /**
     * @ORM\Column(name="packging_height", type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Hauteur du colis")
     */
    private ?string $packgingHeight = null;

    /**
     * @ORM\Column(name="packging_weight", type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Poids du colis")
     */
    private ?string $packgingWeight = null;

    /**
     * @ORM\Column(name="product_characteristics", type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Caractéristiques du produit")
     */
    private ?string $productCharacteristics = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Certifications")
     */
    private ?string $certifications = null;

    /**
     * @ORM\Column(type="array")
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Url image")
     */
    private iterable $images;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("promotion_id")
     */
    private ?string $promotion = null;

    /**
     * @ORM\Column(name="mobile_url", type="string", nullable=true)
     *
     * @Groups({"channable"})
     *
     * @SerializedName("Lien Mobile")
     */
    private ?string $mobileUrl = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Channable\Entity\ChannableFileLog")
     *
     * @ORM\JoinColumn(name="channable_file_log_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ChannableFileLog $channableFileLog;

    /**
     * @ORM\ManyToOne(targetEntity=ProductVariantInterface::class)
     *
     * @ORM\JoinColumn(name="variant_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ProductVariantInterface $variant;

    public function getId(): int
    {
        return $this->id;
    }

    public function getGoogleProductId(): string
    {
        return $this->googleProductId;
    }

    public function setGoogleProductId(string $googleProductId): self
    {
        $this->googleProductId = $googleProductId;

        return $this;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;

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

    public function getParentSku(): string
    {
        return $this->parentSku;
    }

    public function setParentSku(string $parentSku): self
    {
        $this->parentSku = $parentSku;

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

    public function getUrlProduct(): string
    {
        return $this->urlProduct;
    }

    public function setUrlProduct(string $urlProduct): self
    {
        $this->urlProduct = $urlProduct;

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

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getProductHighlight(): ?string
    {
        return $this->productHighlight;
    }

    public function setProductHighlight(?string $productHighlight): self
    {
        $this->productHighlight = $productHighlight;

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

    public function isBundle(): bool
    {
        return $this->isBundle;
    }

    public function setBundle(bool $isBundle): self
    {
        $this->isBundle = $isBundle;

        return $this;
    }

    public function getPublic(): string
    {
        return $this->public;
    }

    public function setPublic(string $public): self
    {
        $this->public = $public;

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

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

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

    public function getPriceWithVat(): int
    {
        return $this->priceWithVat;
    }

    public function setPriceWithVat(int $priceWithVat): self
    {
        $this->priceWithVat = $priceWithVat;

        return $this;
    }

    public function getPromotionPriceWithVat(): ?int
    {
        return $this->promotionPriceWithVat;
    }

    public function setPromotionPriceWithVat(?int $promotionPriceWithVat): self
    {
        $this->promotionPriceWithVat = $promotionPriceWithVat;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

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

    public function getMaximumHandlingTime(): ?int
    {
        return $this->maximumHandlingTime;
    }

    public function setMaximumHandlingTime(?int $maximumHandlingTime): self
    {
        $this->maximumHandlingTime = $maximumHandlingTime;

        return $this;
    }

    public function getMinimumHandlingTime(): ?int
    {
        return $this->minimumHandlingTime;
    }

    public function setMinimumHandlingTime(?int $minimumHandlingTime): self
    {
        $this->minimumHandlingTime = $minimumHandlingTime;

        return $this;
    }

    public function getMaximumDeliveryTime(): ?int
    {
        return $this->maximumDeliveryTime;
    }

    public function setMaximumDeliveryTime(?int $maximumDeliveryTime): self
    {
        $this->maximumDeliveryTime = $maximumDeliveryTime;

        return $this;
    }

    public function getMinimumDeliveryTime(): ?int
    {
        return $this->minimumDeliveryTime;
    }

    public function setMinimumDeliveryTime(?int $minimumDeliveryTime): self
    {
        $this->minimumDeliveryTime = $minimumDeliveryTime;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

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

    public function getMaterial(): string
    {
        return $this->material;
    }

    public function setMaterial(string $material): self
    {
        $this->material = $material;

        return $this;
    }

    public function getThickness(): string
    {
        return $this->thickness;
    }

    public function setThickness(string $thickness): self
    {
        $this->thickness = $thickness;

        return $this;
    }

    public function getMadeIn(): string
    {
        return $this->madeIn;
    }

    public function setMadeIn(string $madeIn): self
    {
        $this->madeIn = $madeIn;

        return $this;
    }

    public function getExpeditionCountry(): ?string
    {
        return $this->expeditionCountry;
    }

    public function setExpeditionCountry(?string $expeditionCountry): self
    {
        $this->expeditionCountry = $expeditionCountry;

        return $this;
    }

    public function getProductDetails(): ?string
    {
        return $this->productDetails;
    }

    public function setProductDetails(?string $productDetails): self
    {
        $this->productDetails = $productDetails;

        return $this;
    }

    public function getProductLength(): ?string
    {
        return $this->productLength;
    }

    public function setProductLength(?string $productLength): self
    {
        $this->productLength = $productLength;

        return $this;
    }

    public function getProductWidth(): ?string
    {
        return $this->productWidth;
    }

    public function setProductWidth(?string $productWidth): self
    {
        $this->productWidth = $productWidth;

        return $this;
    }

    public function getProductHeight(): ?string
    {
        return $this->productHeight;
    }

    public function setProductHeight(?string $productHeight): self
    {
        $this->productHeight = $productHeight;

        return $this;
    }

    public function getProductWeight(): ?string
    {
        return $this->productWeight;
    }

    public function setProductWeight(?string $productWeight): self
    {
        $this->productWeight = $productWeight;

        return $this;
    }

    public function getPackgingLength(): ?string
    {
        return $this->packgingLength;
    }

    public function setPackgingLength(?string $packgingLength): self
    {
        $this->packgingLength = $packgingLength;

        return $this;
    }

    public function getPackgingWidth(): ?string
    {
        return $this->packgingWidth;
    }

    public function setPackgingWidth(?string $packgingWidth): self
    {
        $this->packgingWidth = $packgingWidth;

        return $this;
    }

    public function getPackgingHeight(): ?string
    {
        return $this->packgingHeight;
    }

    public function setPackgingHeight(?string $packgingHeight): self
    {
        $this->packgingHeight = $packgingHeight;

        return $this;
    }

    public function getPackgingWeight(): ?string
    {
        return $this->packgingWeight;
    }

    public function setPackgingWeight(?string $packgingWeight): self
    {
        $this->packgingWeight = $packgingWeight;

        return $this;
    }

    public function getProductCharacteristics(): ?string
    {
        return $this->productCharacteristics;
    }

    public function setProductCharacteristics(?string $productCharacteristics): self
    {
        $this->productCharacteristics = $productCharacteristics;

        return $this;
    }

    public function getCertifications(): ?string
    {
        return $this->certifications;
    }

    public function setCertifications(?string $certifications): self
    {
        $this->certifications = $certifications;

        return $this;
    }

    public function getImages(): iterable
    {
        return $this->images;
    }

    public function addImage(string $imageUrl): self
    {
        $this->images[] = $imageUrl;

        return $this;
    }

    public function getPromotion(): ?string
    {
        return $this->promotion;
    }

    public function setPromotion(?string $promotion): self
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function getMobileUrl(): ?string
    {
        return $this->mobileUrl;
    }

    public function setMobileUrl(?string $mobileUrl): self
    {
        $this->mobileUrl = $mobileUrl;

        return $this;
    }

    public function getChannableFileLog(): ChannableFileLog
    {
        return $this->channableFileLog;
    }

    public function setChannableFileLog(ChannableFileLog $channableFileLog): self
    {
        $this->channableFileLog = $channableFileLog;

        return $this;
    }

    public function getVariant(): ProductVariantInterface
    {
        return $this->variant;
    }

    public function setVariant(ProductVariantInterface $variant): self
    {
        $this->variant = $variant;

        return $this;
    }
}
