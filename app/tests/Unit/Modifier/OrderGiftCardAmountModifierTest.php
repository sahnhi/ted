<?php

namespace App\Tests\Unit\Modifier;

use App\Doctrine\Entity\Sylius\GiftCard;
use App\Doctrine\Entity\Sylius\Order;
use App\Modifier\OrderGiftCardAmountModifier;
use App\Tests\TestCase;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use Safe\Exceptions\StringsException;
use Setono\SyliusGiftCardPlugin\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Adjustment;

class OrderGiftCardAmountModifierTest extends TestCase
{
    /** @var Order */
    private $order;
    /** @var GiftCard */
    private $giftCard;

    private $adjustment;
    /** @var string */
    private $code = 'code';

    /** @var ObjectManager|MockObject */
    private $giftCardManager;

    /** @var OrderGiftCardAmountModifier */
    private $modifier;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->giftCard = new GiftCard();
        $this->adjustment = new Adjustment();

        $this->giftCard->setCode($this->code);
        $this->giftCard->setAmount(100);
        $this->order->addGiftCard($this->giftCard);
        $this->adjustment->setType(AdjustmentInterface::ORDER_GIFT_CARD_ADJUSTMENT);
        $this->adjustment->setOriginCode($this->code);
        $this->order->addAdjustment($this->adjustment);

        $this->giftCardManager = $this->createMock(ObjectManager::class);
        $this->modifier = new OrderGiftCardAmountModifier($this->giftCardManager);
    }

    /**
     * @throws StringsException
     */
    public function testDecrement(): void
    {
        $this->order->removeAdjustments();

        $this->modifier->decrement(clone $this->order);

        $this->assertEquals($this->order, $this->modifier->getOrder());
    }

    /**
     * @throws StringsException
     */
    public function testDecrementAmountSuperior(): void
    {
        $this->adjustment->setAmount(150);
        $this->modifier->decrement(clone $this->order);

        foreach ($this->modifier->getOrder()->getGiftCards() as $giftCard) {
            $this->assertFalse($giftCard->isEnabled());
            $this->assertEquals(0, $giftCard->getAmount());
        }
    }

    /**
     * @throws StringsException
     */
    public function testDecrementAmountInferior(): void
    {
        $this->adjustment->setAmount(50);
        $this->modifier->decrement(clone $this->order);

        foreach ($this->modifier->getOrder()->getGiftCards() as $giftCard) {
            $this->assertTrue($giftCard->isEnabled());
            $this->assertEquals(50, $giftCard->getAmount());
        }
    }

    /**
     * @throws StringsException
     */
    public function testIncrement(): void
    {
        $this->order->removeAdjustments();

        $this->modifier->increment(clone $this->order);

        $this->assertEquals($this->order, $this->modifier->getOrder());
    }

    /**
     * @throws StringsException
     */
    public function testIncrementHasAdjustment(): void
    {
        $this->giftCard->disable();
        $this->giftCard->setAmount(-50);
        $this->adjustment->setAmount(10);

        $this->modifier->increment(clone $this->order);

        foreach ($this->modifier->getOrder()->getGiftCards() as $giftCard) {
            $this->assertEquals(-40, $giftCard->getAmount());
            $this->assertFalse($giftCard->isEnabled());
        }
    }

    /**
     * @throws StringsException
     */
    public function testIncrementAmountSuperior(): void
    {
        $this->modifier->increment(clone $this->order);

        foreach ($this->modifier->getOrder()->getGiftCards() as $giftCard) {
            $this->assertEquals(100, $giftCard->getAmount());
            $this->assertTrue($giftCard->isEnabled());
        }
    }

    /**
     * @throws StringsException
     */
    public function testResetIfDisable(): void
    {
        $this->order->removeAdjustments();

        $this->modifier->resetIfDisable(clone $this->order);

        $this->assertEquals($this->order, $this->modifier->getOrder());
    }

    /**
     * @throws StringsException
     */
    public function testResetIfDisableGiftCardEnabled(): void
    {
        $this->modifier->resetIfDisable(clone $this->order);

        $this->assertEquals($this->order->getGiftCards(), $this->modifier->getOrder()->getGiftCards());
    }

    /**
     * @throws StringsException
     */
    public function testResetIfDisableGiftCardDisabled(): void
    {
        $this->order->removeGiftCard($this->giftCard);
        $this->giftCard = new GiftCard();
        $this->giftCard->setCode($this->code);
        $this->giftCard->setAmount(-10);
        $this->giftCard->disable();
        $this->order->addGiftCard($this->giftCard);

        $this->modifier->resetIfDisable(clone $this->order);

        foreach ($this->modifier->getOrder()->getGiftCards() as $giftCard) {
            $this->assertEquals(-10, $giftCard->getAmount());
            $this->assertFalse($giftCard->isEnabled());
        }
    }

    /**
     * @throws StringsException
     */
    public function testResetIfDisableGiftCardDisabledAmountSuperior(): void
    {
        $this->giftCard->disable();

        $this->modifier->resetIfDisable(clone $this->order);

        foreach ($this->modifier->getOrder()->getGiftCards() as $giftCard) {
            $this->assertEquals(100, $giftCard->getAmount());
            $this->assertTrue($giftCard->isEnabled());
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetGiftCard(): void
    {
        $giftCard = $this->invokeMethod(
            $this->modifier,
            'getGiftCard',
            [$this->order, $this->code]
        );

        $this->assertEquals($this->giftCard, $giftCard);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGiftCardNotFound(): void
    {
        $this->expectException(\RuntimeException::class);

        $giftCard = $this->invokeMethod(
            $this->modifier,
            'getGiftCard',
            [$this->order, '']
        );
    }
}
