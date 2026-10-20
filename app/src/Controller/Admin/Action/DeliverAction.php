<?php

namespace App\Controller\Admin\Action;

use App\Component\Core\OrderShippingStates;
use App\Component\Core\OrderShippingTransitions;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Legacy\Younited\YounitedPaymentProcessor;
use App\Model\Payum\Payment;
use App\Services\Sentry\ReportingInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Sentry;
use SM\Factory\FactoryInterface;
use Sylius\Component\Shipping\ShipmentTransitions;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DeliverAction
{
    public function __construct(
        private readonly EntityRepository $shipmentRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly FlashBagInterface $flashBag,
        private readonly FactoryInterface $smFactory,
        private readonly YounitedPaymentProcessor $younitedPaymentProcessor,
        private readonly EntityManagerInterface $shipmentManager,
        private readonly TranslatorInterface $translator,
        private readonly ReportingInterface $reporting
    ) {
    }

    public function __invoke(string $id): Response
    {
        /** @var Shipment $order */
        $shipment = $this->shipmentRepository->find($id);

        if (null === $shipment) {
            throw new NotFoundHttpException('Shipment does not exists');
        }

        try {
            $order = $shipment->getOrder();
            $shipmentStateMachine = $this->smFactory->get($shipment, ShipmentTransitions::GRAPH);
            $orderStateMachine = $this->smFactory->get($order, OrderShippingTransitions::GRAPH);

            if ($shipmentStateMachine->can(OrderShippingTransitions::TRANSITION_DELIVER)) {
                $shipmentStateMachine->apply(OrderShippingTransitions::TRANSITION_DELIVER);
            }

            $younited = false;
            /** @var Payment $payment */
            foreach ($order->getPayments() as $payment) {
                if ($payment->getMethod() && 'younited' === $payment->getMethod()->getCode()) {
                    $younited = true;
                }
            }

            if ($younited && in_array($orderStateMachine->getState(), [OrderShippingStates::STATE_DELIVERED, OrderShippingStates::STATE_CANCELLED])) {
                if (!$order->isYounitedActivated()) {
                    $this->younitedPaymentProcessor->activateAllByOrder($order);
                }
            }

            $this->shipmentManager->flush();
        } catch (\Exception $exception) {
            $this->reporting->reportIssue(
                $exception,
                Sentry\Severity::error(),
                ['Deliver' => $shipment->getOrder()->getNumber()]
            );

            $this->flashBag->add(
                'error',
                $this->translator->trans('sylius.ui.deliver_error')
            );
        }

        return new RedirectResponse(
            $this->urlGenerator->generate('sylius_admin_order_show', ['id' => $shipment->getOrder()->getId()])
        );
    }
}
