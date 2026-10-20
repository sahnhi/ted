<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\ShippingMethodCategory;
use App\Model\Order\OrderMessageInterface;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\OrderInterface as SyliusOrderInterface;

interface OrderInterface extends SyliusOrderInterface
{
    public function setOdooState(?string $odooState): self;

    public function getReturnType($human = false): string|int|null;

    public function getOdooState(): string|null;

    public function isLocked(): bool;

    /**
     * @return array<array-key, ShippingMethodCategory>
     */
    public function getShippingMethodCategories(): array;

    public function getCrossSellProducts(): array;

    /**
     * @return Collection<OrderItem>
     */
    public function getPromotionableItems(): Collection;

    public function getCoupon(): array|null;

    public function getCustomerEmail(): string|null;

    public function getPromotionCouponCode(): string|null;

    /**
     * @return Collection<OrderItem>
     */
    public function getServiceItems(): Collection;

    public function addMessage(OrderMessageInterface $message): self;
}
