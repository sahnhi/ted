<?php

declare(strict_types=1);

namespace App\Api\ChronoPost\OrderWeightCalculator;

use App\Api\ChronoPost\Exception\OrderCannotWeightZeroGram;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;

final class OrderWeightCalculator
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ProductVariantRepositoryInterface $productVariantRepository
    ) {
    }

    public function weightInGramsCalculateFromTokenOrder(string $tokenOrder): ?int
    {
        $weight = $this->orderRepository->findTotalWeightInKilograms($tokenOrder);

        if ('0' === $weight) {
            throw new OrderCannotWeightZeroGram($tokenOrder);
        }

        if (!$weight) {
            throw new \InvalidArgumentException('command not found');
        }

        return (int) floor((float) $weight * 1000);
    }

    public function weightInGramsCalculateFromProducts(array $products): int
    {
        $weight = 0;

        foreach ($products as $product) {
            /** @var ProductVariant $productVariant */
            $productVariant = $this->productVariantRepository->findOneByCode($product['code']);
            if (null === $productVariant) {
                throw new \InvalidArgumentException('variant not found');
            }
            $weight += $productVariant->getWeight() * $product['quantity'];
        }

        if (0 === $weight) {
            throw new OrderCannotWeightZeroGram();
        }

        return (int) floor($weight * 1000);
    }
}
