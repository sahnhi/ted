<?php

namespace App\Tests\Unit\Provider\CrossSelling;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductAssociationTypeInterface;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Provider\CrossSelling\RelatedProductsProvider;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Product\Model\ProductAssociation;
use Sylius\Component\Product\Model\ProductAssociationType;

class RelatedProductsProviderTest extends TestCase
{
    private readonly RelatedProductsProvider $relatedProductsProvider;

    protected function setUp(): void
    {
        $this->relatedProductsProvider = new RelatedProductsProvider();
    }

    /**
     * @dataProvider provideOrderData
     */
    public function testProvideForCart(OrderInterface $order, ProductAssociationType $productAssociationType, string $code, array $expectedAssociatedProduct): void
    {
        $productAssociationType->setCode($code);

        self::assertEquals($expectedAssociatedProduct, $this->relatedProductsProvider->provideForCart($order));
    }

    /**
     * @dataProvider provideProductData
     */
    public function testProvideForProduct(Product $product, ProductAssociationType $productAssociationType, string $code, array $expectedAssociatedProduct): void
    {
        $productAssociationType->setCode($code);

        self::assertEquals($expectedAssociatedProduct, $this->relatedProductsProvider->provideForProduct($product));
    }

    private function provideOrderData(): iterable
    {
        $productAssociationType = new ProductAssociationType();

        // First Order Item
        $associatedProduct1 = new Product();
        $associatedProduct1->setCode('FOO');

        $association1 = new ProductAssociation();
        $association1->setType($productAssociationType);
        $association1->addAssociatedProduct($associatedProduct1);

        $product1 = new Product();
        $product1->addAssociation($association1);

        $variant1 = new ProductVariant();
        $variant1->setProduct($product1);

        $orderItem1 = new OrderItem();
        $orderItem1->setVariant($variant1);

        // Second Order Item
        $associatedProduct2 = new Product();
        $associatedProduct2->setCode('BAR');

        $association2 = new ProductAssociation();
        $association2->setType($productAssociationType);
        $association2->addAssociatedProduct($associatedProduct2);

        $product2 = new Product();
        $product2->addAssociation($association2);

        $variant2 = new ProductVariant();
        $variant2->setProduct($product2);

        $orderItem2 = new OrderItem();
        $orderItem2->setVariant($variant2);

        $cart = new Order();
        $cart->addItem($orderItem1);
        $cart->addItem($orderItem2);

        yield [$cart, $productAssociationType, ProductAssociationTypeInterface::TYPE_CART_CROSS_SELLING, [$associatedProduct1, $associatedProduct2]];

        yield [$cart, $productAssociationType, 'FOO-BAR', []];
    }

    private function provideProductData(): iterable
    {
        $productAssociationType = new ProductAssociationType();

        // First Order Item
        $associatedProduct1 = new Product();
        $associatedProduct1->setCode('FOO');

        $associatedProduct2 = new Product();
        $associatedProduct2->setCode('BAR');

        $association = new ProductAssociation();
        $association->setType($productAssociationType);
        $association->addAssociatedProduct($associatedProduct1);
        $association->addAssociatedProduct($associatedProduct2);

        $product = new Product();
        $product->addAssociation($association);

        yield [$product, $productAssociationType, ProductAssociationTypeInterface::TYPE_PRODUCT_CROSS_SELLING, [$associatedProduct1, $associatedProduct2]];
        yield [$product, $productAssociationType, 'FOO-BAR', []];
    }
}
