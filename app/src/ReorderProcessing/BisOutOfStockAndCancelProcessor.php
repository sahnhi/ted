<?php

declare(strict_types=1);

namespace App\ReorderProcessing;

use App\Doctrine\Entity\OrderOutOfStock;
use App\Doctrine\Entity\OrderOutOfStockInterface;
use App\Doctrine\Entity\OrderVip;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\OrderOutOfStockRepository;
use App\Services\OutOfStock\WeightingCalculationInterface;
use SM\Factory\FactoryInterface;
use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderShippingStates;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class BisOutOfStockAndCancelProcessor implements ReorderProcessor
{
    private readonly ?Request $request;

    public function __construct(
        RequestStack $request,
        private readonly FactoryInterface $smFactory,
        private readonly FlashBagInterface $flashBag,
        private readonly OrderOutOfStockRepository $orderOutOfStockRepository,
        private readonly RepositoryInterface $paymentMethodRepository,
        private readonly WeightingCalculationInterface $weightingCalculation,
        private readonly TranslatorInterface $translator
    ) {
        $this->request = $request->getCurrentRequest();
    }

    public function process(OrderInterface $order, OrderInterface $reorder): void
    {
        $this->request->request->add(['bisComplete' => true]);

        $newOrderFormParams = $this->request->get('sylius_admin_order_creation_new_order');
        Assert::isArray($newOrderFormParams);

        $parentState = $newOrderFormParams['parentState'] ?? null;
        $outOfStock = $newOrderFormParams['outOfStock'] ?? null;

        if (null !== $parentState) {
            switch ($parentState) {
                case OrderInterface::STATE_CANCELLED:
                    $this->cancelParent($order, $reorder);
                    break;

                case OrderOutOfStockInterface::STATE_SOLVED:
                    $this->createOutOfStockStatus($order, $reorder, $outOfStock);
                    break;
            }
        }
    }

    private function cancelParent(Order $order, Order $reorder)
    {
        if (in_array($order->getState(), [OrderInterface::STATE_CANCELLED, OrderInterface::STATE_FULFILLED])) {
            $this->flashBag->add('error', $this->translator->trans('sylius.ui.order.cancelled_failed'));
        } else {
            $stateMachineBis = $this->smFactory->get($order, 'sylius_order');
            $stateMachineBis->apply('cancel');
        }

        $reasons = $reorder->getOrderReason();

        foreach ($reasons as $reason) {
            $newReason = clone $reason;
            $newReason->setOrder($order);
            $order->addOrderReason($newReason);
        }
    }

    private function createOutOfStockStatus(Order $order, Order $reorder, string $outOfStock)
    {
        $orderOutOfStock = $this->orderOutOfStockRepository->findOneBy(['order' => $order]) ?? new OrderOutOfStock();
        $orderOutOfStock->setOrder($order);
        $orderOutOfStock->setStatus(OrderOutOfStockInterface::STATE_SOLVED);
        $order->setOrderOutOfStock($orderOutOfStock);

        $reorderOutOfStock = new OrderOutOfStock();
        $reorderOutOfStock->setOrder($reorder);
        $reorderOutOfStock->setStatus($outOfStock);
        $reorder->setOrderOutOfStock($reorderOutOfStock);

        if (OrderOutOfStockInterface::STATE_PARTIALLY_PLANNED === $outOfStock) {
            $vip = new OrderVip();
            $vip->setOrder($reorder);
            $reorder->setOrderVip($vip);

            $reorder->setState(OrderInterface::STATE_NEW);
            $reorder->setPaymentState(OrderPaymentStates::STATE_AWAITING_PAYMENT);
            $reorder->setShippingState(OrderShippingStates::STATE_READY);
            $reorder->setCheckoutState(OrderCheckoutStates::STATE_COMPLETED);
            $reorder->setCheckoutCompletedAt(new \DateTime());

            $method = $this->paymentMethodRepository->findOneBy(['code' => $reorder->getChannel()->getCode()]);
            Assert::notNull($method);

            $payment = new Payment();
            $payment->setOrder($reorder);
            $payment->setCurrencyCode($reorder->getCurrencyCode());
            $payment->setState(PaymentInterface::STATE_NEW);
            $payment->setMethod($method);
            $reorder->addPayment($payment);

            $this->request->request->add(['toAddress' => false]);
            $this->request->request->add(['toComplete' => false]);
        }

        $reorder->setStockPriority($this->weightingCalculation->recalculate($reorder, $order));
    }
}
