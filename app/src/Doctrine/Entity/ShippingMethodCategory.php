<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\ApiPlatform\State\Order\OrderEligibleShippingMethodCategoryCollectionProvider;
use App\ApiPlatform\State\Order\ProductVariantEligibleShippingMethodCategoryCollectionProvider;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\CodeAwareInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'app_shipping_method_category')]
#[ApiResource(
    collectionOperations: [
        'shop_get_eligible' => [
            'method' => 'GET',
            'path' => '/shop/order/shipping-method-category/eligible',
            'provider' => OrderEligibleShippingMethodCategoryCollectionProvider::class,
            'openapi_context' => [
                'parameters' => [
                    [
                        'name' => 'tokenValue',
                        'in' => 'query',
                        'required' => true,
                    ],
                    [
                        'name' => 'postalCode',
                        'in' => 'query',
                    ],
                ],
            ],
        ],
        'shop_product_shipping_method_categories' => [
            'method' => 'GET',
            'path' => '/shop/products/shipping-method-category/eligible',
            'provider' => ProductVariantEligibleShippingMethodCategoryCollectionProvider::class,
            'openapi_context' => [
                'parameters' => [
                    [
                        'name' => 'productVariants[]',
                        'in' => 'query',
                        'schema' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                            ],
                        ],
                        'required' => true,
                    ],
                    [
                        'name' => 'postalCode',
                        'in' => 'query',
                    ],
                    [
                        'name' => 'countryCode',
                        'in' => 'query',
                    ],
                ],
            ],
        ],
    ],
    itemOperations: [
        'shop_get' => [
            'method' => 'GET',
            'path' => '/shop/shipping-method-categories/{code}',
        ],
    ],
    normalizationContext: [
        'groups' => [
            'shop:shipping-method-category:read',
            'shop:product_variant:read',
        ],
    ],
)]
class ShippingMethodCategory implements ResourceInterface, CodeAwareInterface, TranslatableInterface
{
    use TimestampableEntity;

    use TranslatableTrait {
        __construct as initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ApiProperty(identifier: false)]
    private int|null $id = null;

    #[ORM\Column(name: 'code', type: Types::STRING, unique: true)]
    #[ApiProperty(identifier: true)]
    private string|null $code = null;

    #[ORM\Column(name: 'enabled', type: Types::BOOLEAN, options: ['default' => false])]
    private bool $enabled = false;

    #[ORM\OneToMany(mappedBy: 'shippingMethodCategory', targetEntity: ShippingMethod::class)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $shippingMethods;

    #[ORM\OneToMany(mappedBy: 'shippingMethodCategory', targetEntity: ShippingMethodCategoryService::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $shippingMethodCategoryServices;

    /**
     * @var Collection<ShippingMethodCategoryClaim>
     */
    #[ORM\OneToMany(mappedBy: 'shippingMethodCategory', targetEntity: ShippingMethodCategoryClaim::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $claims;

    private int|null $lowestPrice = null;

    public function __construct()
    {
        $this->initializeTranslationsCollection();

        $this->shippingMethods = new ArrayCollection();
        $this->shippingMethodCategoryServices = new ArrayCollection();
        $this->claims = new ArrayCollection();
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function setCode(string|null $code): void
    {
        $this->code = $code;
    }

    public function getCode(): string|null
    {
        return $this->code;
    }

    /**
     * @return Collection<ShippingMethod>
     */
    public function getShippingMethods(): Collection
    {
        return $this->shippingMethods;
    }

    public function addShippingMethod(ShippingMethod $shippingMethod): self
    {
        if (!$this->shippingMethods->contains($shippingMethod)) {
            $shippingMethod->setShippingMethodCategory($this);
            $this->shippingMethods->add($shippingMethod);
        }

        return $this;
    }

    public function removeShippingMethod(ShippingMethod $shippingMethod): self
    {
        if ($this->shippingMethods->contains($shippingMethod)) {
            $this->shippingMethods->removeElement($shippingMethod);
            $shippingMethod->setShippingMethodCategory(null);
        }

        return $this;
    }

    public function setShippingMethods(Collection $shippingMethods): self
    {
        $this->shippingMethods = $shippingMethods;

        return $this;
    }

    public function addShippingMethodCategoryService(ShippingMethodCategoryService $shippingMethodCategoryService): self
    {
        if (!$this->shippingMethodCategoryServices->contains($shippingMethodCategoryService)) {
            $shippingMethodCategoryService->setShippingMethodCategory($this);
            $this->shippingMethodCategoryServices->add($shippingMethodCategoryService);
        }

        return $this;
    }

    public function removeShippingMethodCategoryService(ShippingMethodCategoryService $shippingMethodCategoryService): self
    {
        if ($this->shippingMethodCategoryServices->contains($shippingMethodCategoryService)) {
            $this->shippingMethodCategoryServices->removeElement($shippingMethodCategoryService);
            $shippingMethodCategoryService->setShippingMethodCategory(null);
        }

        return $this;
    }

    /**
     * @return Collection<ShippingMethodCategoryService>
     */
    public function getShippingMethodCategoryServices(): Collection
    {
        return $this->shippingMethodCategoryServices;
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

    public function getTranslation(?string $locale = null): ShippingMethodCategoryTranslation
    {
        /** @var ShippingMethodCategoryTranslation $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }

    public function getName(?string $locale = null): string
    {
        return $this->getTranslation($locale)->getName();
    }

    public function setShippingMethodCategoryServices(Collection $shippingMethodCategoryServices): self
    {
        $this->shippingMethodCategoryServices = $shippingMethodCategoryServices;

        return $this;
    }

    /**
     * @return Collection<ShippingMethodCategoryService>
     */
    public function getEnabledServices(): Collection
    {
        return $this->getShippingMethodCategoryServices()->filter(
            static fn (ShippingMethodCategoryService $service): bool => $service->isEnabled()
        );
    }

    /**
     * Returns services that should be automatically included freely.
     *
     * @return Collection<ShippingMethodCategoryService>
     */
    public function getIncludedServices(): Collection
    {
        return $this->getEnabledServices()->filter(
            static fn (ShippingMethodCategoryService $service): bool => $service->isIncluded()
        );
    }

    /**
     * @return array<ShippingMethodCategoryService>
     */
    public function getIncludedServicesValues(): array
    {
        return $this->getIncludedServices()->getValues();
    }

    /**
     * Returns paid services that can be selected by the customer.
     *
     * @return Collection<ShippingMethodCategoryService>
     */
    public function getPaidServices(): Collection
    {
        return $this->getEnabledServices()->filter(
            static fn (ShippingMethodCategoryService $service): bool => !$service->isIncluded()
        );
    }

    /**
     * @return array<ShippingMethodCategoryService>
     */
    public function getPaidServicesValues(): array
    {
        return $this->getPaidServices()->getValues();
    }

    public function getLowestPrice(): int|null
    {
        return $this->lowestPrice;
    }

    public function setLowestPrice(int|null $lowestPrice): self
    {
        $this->lowestPrice = $lowestPrice;

        return $this;
    }

    public function getClaims(): Collection
    {
        return $this->claims;
    }

    public function getEnabledClaims(): Collection
    {
        return $this->claims
            ->filter(static fn (ShippingMethodCategoryClaim $claim): bool => $claim->isEnabled());
    }

    /** @return array<ShippingMethodCategoryClaim> */
    public function getPushedClaims(): array
    {
        return $this->getEnabledClaims()
            ->filter(static fn (ShippingMethodCategoryClaim $claim): bool => $claim->isPushed())
            ->getValues();
    }

    public function addClaim(ShippingMethodCategoryClaim $claim): self
    {
        if (!$this->claims->contains($claim)) {
            $claim->setShippingMethodCategory($this);
            $this->claims->add($claim);
        }

        return $this;
    }

    public function removeClaim(ShippingMethodCategoryClaim $claim): self
    {
        if ($this->claims->contains($claim)) {
            $this->claims->removeElement($claim);
            $claim->setShippingMethodCategory(null);
        }

        return $this;
    }

    protected function createTranslation(): TranslationInterface
    {
        return new ShippingMethodCategoryTranslation();
    }
}
