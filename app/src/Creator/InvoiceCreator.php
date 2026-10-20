<?php

declare(strict_types=1);

namespace App\Creator;

use App\Component\Core\OrderShippingStates;
use App\Doctrine\Entity\Sylius\InvoiceInterface;
use App\Doctrine\Repository\InvoicePluginRepositoryInterface;
use App\Generator\InvoiceGeneratorInterface;
use App\Generator\Renderer\SwissInvoiceRenderer;
use App\Identifier\OrderNumber;
use App\Services\Channel\ChannelCode;
use App\Services\Sentry\ReportingInterface;
use Sentry;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\InvoicingPlugin\Creator\InvoiceCreatorInterface;
use Sylius\InvoicingPlugin\Doctrine\ORM\InvoiceRepositoryInterface;
use Sylius\InvoicingPlugin\Exception\InvoiceAlreadyGenerated;

final class InvoiceCreator implements InvoiceCreatorInterface
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly InvoiceGeneratorInterface $invoiceGenerator,
        private readonly InvoiceCreatorInterface $decoratedInvoiceCreator,
        private readonly InvoicePluginRepositoryInterface $invoicePluginRepository,
        private readonly ReportingInterface $reporting
    ) {
    }

    public function __invoke(string $orderNumber, \DateTimeInterface $dateTime): void
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneByNumber($orderNumber);
        $channel = $order->getChannel();

        if (in_array($order->getShippingState(), [OrderShippingStates::STATE_SHIPPED, OrderShippingStates::STATE_DELIVERED])
            && !in_array($channel->getCode(), [ChannelCode::CHANNEL_BIS, ChannelCode::CHANNEL_B2B, ChannelCode::CHANNEL_RETURN, ChannelCode::CHANNEL_OPERATION], true)) {
            try {
                $this->decoratedInvoiceCreator->__invoke($orderNumber, $dateTime);
            } catch (\Exception $exception) {
                if (!$exception instanceof InvoiceAlreadyGenerated) {
                    $this->reporting->reportIssue(
                        $exception,
                        Sentry\Severity::error(),
                        ['invoice_generation_failed' => $orderNumber]
                    );
                }
            }
        }
    }

    public function handleCarrierInvoice(OrderInterface $order, \DateTimeInterface $dateTime): void
    {
        if (SwissInvoiceRenderer::SWISS_TAX_CODE === $order->getShippingAddress()->getCountryCode() && 1 !== $this->invoicePluginRepository->countCarrierInvoiceByOrderNumber(OrderNumber::create($order->getNumber()))) {
            $invoice = $this->invoiceGenerator->generateForOrderWithType($order, $dateTime, InvoiceInterface::INVOICE_TYPE_CARRIER);

            $this->invoiceRepository->add($invoice);
        }
    }
}
