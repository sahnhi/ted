<?php

declare(strict_types=1);

namespace App\Factory\Logistic\PickUp;

use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Model\Logistic\PickUp\SearchableAddress;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

final class SearchableAddressFactory
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly RepositoryInterface $productVariantRepository
    ) {
    }

    public function createNewFromRequest(Request $request)
    {
        $orderToken = $request->query->get('tokenOrder');
        $order = $orderToken ? $this->getOrder($orderToken) : null;

        $shippingDateObject = $this->getShippingDate($request);

        $products = $this->getProducts($request);

        return new SearchableAddress(
            $request->query->get('zipCode'),
            $request->query->get('address'),
            $request->query->get('city'),
            $request->query->get('countryCode'),
            $shippingDateObject,
            $order,
            $products
        );
    }

    public function createFromProvider(array $context, string $tokenValue): SearchableAddress
    {
        $order = $this->getOrder($tokenValue);
        $shippingDate = array_key_exists('shippingDate', $context) ? new \DateTimeImmutable($context['shippingDate']) : new \DateTimeImmutable('now');

        return new SearchableAddress(
            $context['zipCode'],
            array_key_exists('address', $context) ? $context['address'] : null,
            $context['city'],
            $context['countryCode'],
            $shippingDate,
            $order,
            []
        );
    }

    private function getOrder(string $orderToken): OrderInterface
    {
        return $this->orderRepository->findOneBy(['tokenValue' => $orderToken]);
    }

    private function getShippingDate(Request $request): \DateTimeImmutable
    {
        return new \DateTimeImmutable($request->query->get('shippingDate', 'now'));
    }

    private function getProducts(Request $request): array
    {
        /**
         * @var array $products
         */
        $products = $request->query->get('products', []);

        if (!is_iterable($products)) {
            return [];
        }

        $data = [];

        foreach ($products as $product) {
            Assert::keyExists($product, 'code');
            Assert::keyExists($product, 'quantity');

            $variant = $this->productVariantRepository->findOneBy(['code' => $product['code']]);
            if ($variant instanceof ProductVariantInterface) {
                $data[] = [
                    'variant' => $variant,
                    'quantity' => $product['quantity'],
                ];
            }
        }

        return $data;
    }
}
