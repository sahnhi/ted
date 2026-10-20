<?php

namespace App\Tests\Unit\EventListener\ProductAssociationType;

use App\Doctrine\Entity\Sylius\ProductAssociationTypeInterface;
use App\EventListener\ProductAssociationType\PreDeleteListener;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Product\Model\ProductAssociationType;
use Symfony\Contracts\Translation\TranslatorInterface;

class PreDeleteListenerTest extends TestCase
{
    /**
     * @dataProvider provideProductAssociationTypeFixtures
     */
    public function testItStopsTheEventIfTheAssociationTypeIsCrossSelling(
        ResourceControllerEvent $event,
        bool $isStopped
    ): void {
        $translator = $this->createMock(TranslatorInterface::class);

        if ($isStopped) {
            $translator
                ->expects($this->once())
                ->method('trans')
                ->with('app.form.product_association_type.delete_cross_selling')
                ->willReturn('Random error test');
        }

        $listener = new PreDeleteListener($translator);
        $listener($event);

        self::assertSame($isStopped, $event->isStopped());
    }

    private function provideProductAssociationTypeFixtures(): \Generator
    {
        $fooBarProductAssociationType = new ProductAssociationType();
        $fooBarProductAssociationType->setCode('foo_bar');

        yield [new ResourceControllerEvent($fooBarProductAssociationType), false];

        $crossSellingProductAssociationType = new ProductAssociationType();
        $crossSellingProductAssociationType->setCode(ProductAssociationTypeInterface::TYPE_CART_CROSS_SELLING);

        yield [new ResourceControllerEvent($crossSellingProductAssociationType), true];

        $crossSellingProductAssociationType = new ProductAssociationType();
        $crossSellingProductAssociationType->setCode(ProductAssociationTypeInterface::TYPE_PRODUCT_CROSS_SELLING);

        yield [new ResourceControllerEvent($crossSellingProductAssociationType), true];
    }
}
