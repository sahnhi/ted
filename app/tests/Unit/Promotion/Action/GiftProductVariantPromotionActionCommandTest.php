<?php

declare(strict_types=1);

namespace App\Tests\Unit\Promotion\Action;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Promotion;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Promotion\Action\GiftProductVariantPromotionActionCommand;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;
use Sylius\Component\Resource\Factory\FactoryInterface;

class GiftProductVariantPromotionActionCommandTest extends TestCase
{
    private readonly GiftProductVariantPromotionActionCommand $command;

    protected function setUp(): void
    {
        $this->command = new GiftProductVariantPromotionActionCommand(
            $this->createMock(ProductVariantRepositoryInterface::class),
            $this->createMock(FactoryInterface::class),
            $this->createMock(OrderItemQuantityModifierInterface::class),
            $this->createMock(AdjustmentFactoryInterface::class),
        );
    }

    public function testItThrowsAnUnexpectedTypeExceptionIfTheSubjectIsNotAnOrderDuringExecute(): void
    {
        $subject = $this->createMock(PromotionSubjectInterface::class);

        $this->expectException(UnexpectedTypeException::class);

        $this->command->execute($subject, [], new Promotion());
    }

    public function testItThrowsAnInvalidArgumentExceptionIfTheConfigurationIsMissingTheVariantsDuringExecute(): void
    {
        $subject = new Order();

        $configuration = [];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Configuration must contain "variants" key.');

        $this->command->execute($subject, $configuration, new Promotion());
    }

    public function testItThrowsAnUnexpectedTypeExceptionIfTheSubjectIsNotAnOrderDuringRevert(): void
    {
        $subject = $this->createMock(PromotionSubjectInterface::class);

        $this->expectException(UnexpectedTypeException::class);

        $this->command->revert($subject, [], new Promotion());
    }

    public function testItThrowsAnInvalidArgumentExceptionIfTheConfigurationIsMissingTheVariantsDuringRevert(): void
    {
        $subject = new Order();

        $configuration = [];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Configuration must contain "variants" key.');

        $this->command->revert($subject, $configuration, new Promotion());
    }
}
