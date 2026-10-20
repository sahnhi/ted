<?php

namespace App\Handler;

use App\Business\Command\PutVariantBasedConfigurableItemToCart;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Handler\Shared\OrderResetter;
use App\Modifier\OrderModifierInterface;
use SM\SMException;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Webmozart\Assert\Assert;

final class PutVariantBasedConfigurableItemToCartHandler
{
    public function __construct(
        private readonly OrderRepository $cartRepository,
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly OrderModifierInterface $orderModifier,
        private readonly OrderResetter $orderResetter,
    ) {
    }

    /**
     * @throws SMException
     */
    public function handle(PutVariantBasedConfigurableItemToCart $putConfigurableItemToCart): void
    {
        /** @var OrderInterface $cart */
        $cart = $this->cartRepository->getOneByTokenValueHydrated($putConfigurableItemToCart->orderToken());
        Assert::notNull($cart, 'Cart has not been found');

        if ($cart->isLocked()) {
            throw new ConflictHttpException('Cet article ne peut pas être modifié à votre panier. Merci de faire une nouvelle commande.');
        }

        $this->orderResetter->cancelPaymentAndResetOrder($cart);

        /** @var ProductVariantInterface $productVariant */
        $productVariant = $this->productVariantRepository->findOneByEnabledProductVariantCodeAndProductCode($putConfigurableItemToCart->productVariant(), $putConfigurableItemToCart->product());
        Assert::notNull($productVariant, 'Product variant has not been found');
        $this->orderModifier->modify($cart, $productVariant, $putConfigurableItemToCart->quantity());
    }
}
