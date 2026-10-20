<?php

namespace App\Services\Admin;

use App\Context\AdminUserContext;
use App\Doctrine\Entity\BundleAssociationInterface;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\PaymentMethodInterface;
use App\Form\Type\Payment\RefundType;
use App\Form\Type\Payment\TransactionIdType;
use App\Message\Mail;
use App\Payum\Adyen\PaymentProcessing\RefundPaymentProcessor as AdyenRefundPaymentProcessor;
use App\Payum\Dalenys\PaymentProcessing\RefundPaymentProcessor as DalenysRefundPaymentProcessor;
use App\Services\CaptainMetrics\CaptainMetricsApi;
use App\Services\Mail\MailerEmailInterface;
use App\Services\Payment\PaymentCalculation;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderService
{
    public const PAYMENT_METHODS_NEED_TRANSACTIONID = ['stripe', 'cheque', 'virement'];
    private Request $request;
    private mixed $resource;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FormFactoryInterface $formFactory,
        private readonly MessageBusInterface $bus,
        private readonly PaymentCalculation $paymentCalculation,
        private readonly RepositoryInterface $orderRepository,
        private readonly AdyenRefundPaymentProcessor $adyenRefundPaymentProcessor,
        private readonly DalenysRefundPaymentProcessor $dalenysRefundPaymentProcessor,
        private readonly CaptainMetricsApi $captainMetricsApi,
        private readonly AdminUserContext $adminUserContext
    ) {
    }

    public function init(Request $request, Order $resource): OrderService
    {
        $this->request = $request;
        $this->resource = $resource;

        return $this;
    }

    public function createForm(array $form, string $formName = ''): FormInterface
    {
        if (!empty($formName)) {
            $formCreated = $this->formFactory->createNamed($formName, $form['formType'], $form['resource'] ?? $this->resource, $form['data']);
        } else {
            if (RefundType::class === $form['formType']) {
                $initialOrder = $this->findInitialOrder($form['order']);

                if ($initialOrder) {
                    $lastPayment = $initialOrder->getLastPayment(PaymentInterface::STATE_COMPLETED);
                    $form['data']['payment'] = $lastPayment;
                    $form['data']['order'] = $form['order'];
                }

                $form['data']['refund_amount'] = $this->calculateRefundAmount($form['order']);
                $formCreated = $this->formFactory->create($form['formType'], new Payment(), $form['data']);
            } else {
                $formCreated = $this->formFactory->create($form['formType'], $form['resource'] ?? $this->resource, $form['data']);
            }
        }

        if ($this->request->request->has($formCreated->getName())) {
            $captainMetrics = false;
            $formCreated->handleRequest($this->request);
            if ($formCreated->isSubmitted() && ((!$formName && $formCreated->isValid()) || $formName)) {
                $this->resource = $formCreated->getData();

                if ('refund' === $formCreated->getName()) {
                    $refundCode = $formCreated->get('refundCode')->getData();

                    $isExchange = str_contains((string) $form['order']->getNumber(), 'RE');

                    $lastPayment = null;
                    $amount = $this->resource->getAmount();
                    if (0 < $amount && !$isExchange) {
                        /** @var OrderInterface $orderParent */
                        $orderParent = $this->orderRepository->findInitialOrder($form['order']->getParentNumber());
                        $lastPayment = $orderParent->getLastPayment(BasePaymentInterface::STATE_COMPLETED);
                        $lastPayment = !is_null($lastPayment) ? $lastPayment : $orderParent->getLastPayment(BasePaymentInterface::STATE_REFUNDED);

                        $this->adyenRefundPaymentProcessor->process($lastPayment, $formCreated->get('amount')->getData());
                        $this->dalenysRefundPaymentProcessor->process($lastPayment, $formCreated->get('amount')->getData());

                        /* Adyen refund code */
                        if (!empty($lastPayment->getDetails()['refundedAmount']['latestPspReference'])) {
                            $refundCode = $lastPayment->getDetails()['refundedAmount']['latestPspReference'];
                        }

                        /* Dalenys refund code */
                        if (!empty($lastPayment->getDetails()['REFUNDED_LAST_TRANSACTIONS_ID'])) {
                            $refundCode = $lastPayment->getDetails()['REFUNDED_LAST_TRANSACTIONS_ID'];
                        }

                        $mailer = new Mail(MailerEmailInterface::TYPE_REFUND_CONFIRMATION_EMAIL, $form['order'], [
                            'recipients' => [
                                [
                                    'email' => $form['order']->getCustomer()->getEmail(),
                                    'firstname' => $form['order']->getCustomer()->getFirstName(),
                                    'lastname' => $form['order']->getCustomer()->getLastName(),
                                ],
                            ],
                        ]);
                        $mailer->setData(['payment' => $formCreated->getData()]);
                        $this->bus->dispatch($mailer);
                    } elseif (null === $refundCode && 0 === $amount) {
                        $refundCode = 'retour00';
                    }

                    $form['order']->setPaymentState(OrderPaymentStates::STATE_REFUNDED);
                    $this->saveRefund($refundCode, $form['order'], $lastPayment);
                    $captainMetrics = true;
                }

                if (str_contains($formCreated->getName(), TransactionIdType::NAME)) {
                    $payment = $form['resource'];
                    $details = array_merge($payment->getDetails(), ['TRANSACTIONID' => $formCreated->get('transactionId')->getData()]);
                    $payment->setDetails($details);
                }

                $this->em->persist($this->resource);
                $this->em->flush();

                if ($captainMetrics) {
                    $this->captainMetricsApi->sendOrder($form['order']);
                }
            }
        }

        return $formCreated;
    }

    private function saveRefund(string $refundCode, OrderInterface $order, ?PaymentInterface $lastPayment = null)
    {
        $this->resource->setOrder($order);
        $this->resource->setState(BasePaymentInterface::STATE_REFUNDED);
        $this->resource->setCurrencyCode($order->getCurrencyCode());

        $adminUser = [
            'id' => $this->adminUserContext->getAdmin()->getId(),
            'email' => $this->adminUserContext->getAdmin()->getEmail(),
            'firstName' => $this->adminUserContext->getAdmin()->getFirstName(),
            'lastName' => $this->adminUserContext->getAdmin()->getLastName(),
        ];

        $details = [
            'REFUND' => $refundCode,
            'DATE' => date('Y-m-d H:i:s'),
            'ADMIN_USER' => $adminUser,
        ];

        /** @var PaymentInterface $lastPayment */
        if (null !== $lastPayment) {
            $details['ORDER_NUMBER'] = (string) $lastPayment->getOrder()->getNumber();
            $details['PAYMENT_ID'] = (string) $lastPayment->getId();
        }

        $this->resource->setDetails($details);
    }

    private function findInitialOrder(Order $order)
    {
        /** @var Order $initialOrder */
        $initialOrder = null !== $order->getParentNumber() ? $this->orderRepository->findInitialOrder($order->getParentNumber()) : null;

        return $initialOrder;
    }

    private function calculateRefundAmount(Order $order): float|int
    {
        $initial = $this->findInitialOrder($order);
        if (null === $initial
            || str_contains((string) $order->getNumber(), 'RE')) {
            return 0;
        }

        $totalReturn = 0;
        $refundedItems = [];

        /** @var OrderItem $item */
        foreach ($order->getItems() as $item) {
            foreach ($initial->getItems() as $initialItem) {
                if (in_array($item->getVariant()->getCode(), $refundedItems)) {
                    continue 2;
                }

                if ($initialItem->getVariant()->isBundle()) {
                    /** @var BundleAssociationInterface $association */
                    foreach ($initialItem->getVariant()->getBundleAssociations() as $association) {
                        $associationVariant = $association->getVariant();

                        if (ltrim((string) $item->getVariant()->getCode(), 'R') === $associationVariant->getCode()
                            || $item->getVariant()->getCode() === $associationVariant->getCode()) {
                            $truePrice = $association->getPriceAtDate($initial->getCheckoutCompletedAt());
                            $totalReturn += $truePrice * $item->getQuantity();
                            $refundedItems[] = $item->getVariant()->getCode();
                        }
                    }
                } else {
                    if (ltrim((string) $item->getVariant()->getCode(), 'R') === $initialItem->getVariant()->getCode()
                        || $item->getVariant()->getCode() === $initialItem->getVariant()->getCode()) {
                        // Get Price with promotions
                        $truePrice = $initialItem->getTotal() / $initialItem->getQuantity();
                        $totalReturn += $truePrice * $item->getQuantity();
                        $refundedItems[] = $item->getVariant()->getCode();
                    }
                }
            }
        }

        // GiftCards calculation
        foreach ($initial->getGiftCards() as $giftCard) {
            // Get the good adjustment to get the used sum
            foreach ($initial->getAdjustments() as $adjustment) {
                if ('order_gift_card' === $adjustment->getType() && $adjustment->getOriginCode() === $giftCard->getCode()) {
                    $giftCard->setRefundAmount($totalReturn * abs($adjustment->getAmount()) / $initial->getItemsTotal());
                }
            }
        }

        if ($initial->getPayments()->count() > 0 && PaymentMethodInterface::BE2BILL_NTIMES === $initial->getPayments()->first()->getMethod()->getCode()) {
            $amounts = $this->paymentCalculation->calculateAmounts($totalReturn / 100, $initial->getCheckoutCompletedAt());
            $now = new \DateTime();
            if ($totalReturn === $initial->getTotal()) {
                $totalReturn = 0;

                foreach ($amounts['AMOUNTS'] as $formatedDate => $amount) {
                    $date = date_create_from_format('Y-m-d', $formatedDate);
                    if ($date < $now) {
                        $totalReturn += $amount;
                    }
                }
            }
        }

        return $initial->getItemsTotal() > 0 ? $totalReturn * $initial->getTotal() / $initial->getItemsTotal() : 0;
    }

    public function isRefundAlreadyDone(Order $order): bool
    {
        $initial = null === $order->getParentNumber() ? null : $this->orderRepository->findInitialOrder($order->getParentNumber());

        if (null === $initial || 0 === count($initial->getPayments())) {
            return false;
        }

        $totalRefund = 0;
        foreach ($order->getPayments() as $payment) {
            if (OrderPaymentStates::STATE_REFUNDED === $payment->getState()) {
                $totalRefund += $payment->getAmount();
            }
        }

        return $totalRefund >= $initial->getTotal();
    }

    public function checkTransactionIds(Order $order): array
    {
        $forms = [];

        /** @var Payment $payment */
        foreach ($order->getPayments() as $payment) {
            if ('new' === $payment->getState() && null !== $payment->getMethod() && in_array($payment->getMethod()->getCode(), self::PAYMENT_METHODS_NEED_TRANSACTIONID)) {
                $forms[$payment->getId()] = $this->createForm([
                    'formType' => TransactionIdType::class,
                    'resource' => $payment,
                    'data' => [],
                ], TransactionIdType::NAME . '_' . $payment->getId())->createView();
            }
        }

        return $forms;
    }
}
