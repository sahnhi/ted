<?php

declare(strict_types=1);

namespace App\ApiPlatform\State\Order;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\ZipCode;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Provider\Shipping\ShippingMethodCategoryProvider;
use Sylius\Bundle\ApiBundle\Serializer\ContextKeys;
use Sylius\Component\Addressing\Matcher\ZoneMatcherInterface;
use Sylius\Component\Core\Factory\AddressFactoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ProductVariantEligibleShippingMethodCategoryCollectionProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private const DEFAULT_COUNTRY_CODE = 'FR';

    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly ShippingMethodCategoryProvider $provider,
        private readonly ZoneMatcherInterface $zoneMatcher,
        private readonly AddressFactoryInterface $addressFactory,
        private readonly RepositoryInterface $zipCodeRepository,
    ) {
    }

    /**
     * @return array<ShippingMethodCategory>
     */
    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = []): array
    {
        $variants = $this->productVariantRepository->findEnabledByCode($context['filters']['productVariants']);
        if ([] === $variants) {
            throw new \InvalidArgumentException('No variants found');
        }

        /** @var ChannelInterface $channel */
        $channel = $context[ContextKeys::CHANNEL];

        /** @var Address $address */
        $address = $this->addressFactory->createNew();

        if (isset($context['filters']['postalCode'])) {
            /** @var ZipCode|null $zipCodeCheck */
            $zipCodeCheck = $this->zipCodeRepository->findOneBy(['code' => $context['filters']['postalCode']]);
            if (null === $zipCodeCheck) {
                throw new UnprocessableEntityHttpException(sprintf('Zip code "%s" does not exist', $context['filters']['postalCode']));
            }

            $address->setPostcode($context['filters']['postalCode']);
        }

        $address->setCountryCode($context['filters']['countryCode'] ?? self::DEFAULT_COUNTRY_CODE);

        return $this->provider->provideEligibleForProductVariantsAnZones(
            $this->formatVariantWithQuantity($variants),
            $channel,
            $this->zoneMatcher->matchAll($address, Scope::SHIPPING)
        );
    }

    private function formatVariantWithQuantity(array $variants): array
    {
        $variantsWithQuantity = [];
        foreach ($variants as $variant) {
            $variantsWithQuantity[] = [
                'variant' => $variant,
                'quantity' => 1,
            ];
        }

        return $variantsWithQuantity;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return 'shop_product_shipping_method_categories' === $operationName;
    }
}
