<?php

namespace App\Services\Mail;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\PaymentMethodInterface;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Message\DTO\OrderDTO;
use App\Message\DTO\PaymentDTO;
use App\Services\Channel\ChannelCode;
use App\Services\Mail\TemplateVar\OrderConfirmationEmailTemplateVars;
use Sylius\Bundle\MoneyBundle\Templating\Helper\FormatMoneyHelperInterface;
use Sylius\Component\Payment\Model\PaymentInterface as PaymentInterfaceAlias;
use Symfony\Component\Intl\Currencies;

class OrderConfirmationEmail
{
    public const TEMPLATE_NAME_B2C = 'confirmation-de-commande-dartagnan';
    public const TEMPLATE_NAME_B2B = 'b2b-confirmation-commande-';

    public function __construct(
        private readonly FormatMoneyHelperInterface $formatMoneyHelper,
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @throws \Exception
     */
    public function generateEmail(OrderDTO $order): OrderConfirmationEmailTemplateVars
    {
        // get data for mail
        $customer = $order->getCustomer();

        if (!$customer) {
            throw new \LogicException('No customer found for this payment');
        }

        $firstShipment = $order->getShipments()->first();
        $shippingMethod = $firstShipment->getMethod()->getCode();

        $paymentMethodName = 'NA';
        $paymentMethodCode = 'NA';

        $paymentNtimes = $company = false;
        $monthAmount = [];

        if (!$order->getPayments()->isEmpty()) {
            $lastPayment = null;
            /** @var PaymentDTO $payment */
            foreach ($order->getPayments() as $payment) {
                if (PaymentInterfaceAlias::STATE_COMPLETED === $payment->getState()) {
                    $paymentMethodCode = $payment->getMethod()->getCode();
                    $paymentMethodName = $payment->getMethod()->getName();
                }
                $lastPayment = $payment;
            }

            if (in_array($paymentMethodCode, PaymentMethodInterface::PAYMENT_MULTIPLE_TIMES)) {
                $paymentNtimes = true;

                if ($lastPayment instanceof PaymentDTO && isset($lastPayment->getDetails()['AMOUNTS'])) {
                    foreach ($lastPayment->getDetails()['AMOUNTS'] as $date => $amount) {
                        $date = new \DateTime($date);

                        $monthAmount[] = [
                            'payment_date' => $date->format('d-m-Y'),
                            'amount' => $this->formatMoneyHelper->formatAmount($amount, $order->getCurrencyCode(), $order->getLocaleCode()),
                        ];
                    }
                }
            }
        }
        $templateName = self::TEMPLATE_NAME_B2C;
        if (null !== $order->getChannel() && ChannelCode::CHANNEL_B2B === $order->getChannel()->getCode()) {
            $templateName = self::TEMPLATE_NAME_B2B . str_replace('_', '-', strtolower((string) $order->getLocaleCode()));
            $company = $order->getShippingAddress()->getCompany();
        }

        $orders = $customer->getOrders();
        $build = false;
        /** @var OrderItem $item */
        foreach ($order->getItems() as $item) {
            if (str_contains((string) $item->getVariantCode(), 'MONT')) {
                $build = true;
            }
        }

        $giftCard = false;
        if (count($order->getGiftCards()) > 0) {
            $giftCard = '-' . sprintf('%01.2f', ($order->getItemsTotal() - $order->getTotal()) / 100);
        }

        $shippingMethodCategoryName = null;
        $claims = [];
        /** @var Order $originalOrder */
        $originalOrder = $this->orderRepository->find($order->getId());

        /** @var Shipment $shipment */
        foreach ($originalOrder->getShipments() as $shipment) {
            /** @var ?ShippingMethod $shippingMethod */
            $shippingMethod = $shipment->getMethod();
            if (!$shippingMethod) {
                continue;
            }

            $shippingMethodCategory = $shippingMethod->getShippingMethodCategory();
            $shippingMethodCategoryName = $shippingMethodCategory->getName();
            $claims = $shippingMethodCategory->getPushedClaims();
        }

        return new OrderConfirmationEmailTemplateVars(
            $customer->getPhoneNumber(),
            $customer->getEmail(),
            $firstShipment->getStartDeliverySlot(),
            $firstShipment->getEndDeliverySlot(),
            $customer->getFirstName(),
            $customer->getLastName(),
            $order->getShippingAddress(),
            $order->getBillingAddress(),
            $orders->count() <= 1,
            $order->getNumber(),
            $order->getItems()->getValues(),
            $shippingMethod,
            Currencies::getSymbol($order->getCurrencyCode()),
            sprintf('%01.2f', ($order->getTotal() - $order->getTaxTotal()) / 100),
            sprintf('%01.2f', $order->getTotal() / 100),
            sprintf('%01.2f', $order->getTaxTotal() / 100),
            $paymentMethodName,
            $paymentNtimes,
            $monthAmount,
            $order->getTokenValue(),
            $firstShipment->getMethod()->hasTracking(),
            $firstShipment->getMethod()->isFloor(),
            $firstShipment->getMethod()->isTuned(),
            $build,
            $giftCard,
            $company,
            $customer->getId(),
            in_array($shippingMethod, ShippingMethodInterface::METHOD_RELAY),
            $shippingMethodCategoryName,
            $claims,
            sprintf('%01.2f', $originalOrder->getShippingTotal() / 100),
            $templateName
        );
    }
}
