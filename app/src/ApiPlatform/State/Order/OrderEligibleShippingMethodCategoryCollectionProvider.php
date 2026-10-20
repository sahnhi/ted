<?php

declare(strict_types=1);

namespace App\ApiPlatform\State\Order;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Entity\ZipCode;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Provider\Shipping\ShippingMethodCategoryProvider;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

class OrderEligibleShippingMethodCategoryCollectionProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ShippingMethodCategoryProvider $provider,
        private readonly RepositoryInterface $zipCodeRepository,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * @return array<ShippingMethodCategory>
     */
    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = []): array
    {
        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findCartByTokenValue($context['filters']['tokenValue']);

        if (isset($context['filters']['postalCode'])) {
            $postalCode = $context['filters']['postalCode'];

            /** @var ZipCode|null $zipCodeCheck */
            $zipCodeCheck = $this->zipCodeRepository->findOneBy(['code' => $postalCode]);
            if (null === $zipCodeCheck) {
                throw new UnprocessableEntityHttpException(sprintf('Zip code "%s" does not exist', $context['filters']['postalCode']));
            }

            $order->getShippingAddress()?->setPostcode($postalCode);
        }

        Assert::notNull($order, 'Cart not found');

        $shippingMethodCategoryEligible = $this->provider->provideEligibleForOrder($order);

        if (empty($shippingMethodCategoryEligible)) {
            throw new UnprocessableEntityHttpException($this->translator->trans('app.api.order.shipping.error.shipment_not_available'));
        }

        return $shippingMethodCategoryEligible;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return 'shop_get_eligible' === $operationName;
    }
}
