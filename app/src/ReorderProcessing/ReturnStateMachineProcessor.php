<?php

declare(strict_types=1);

namespace App\ReorderProcessing;

use App\Api\Odoo\Model\OrderInterface as OdooOrderInterface;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;
use App\Services\Channel\ChannelCode;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderShippingStates;
use Sylius\Component\Order\Model\OrderInterface as OrderInterfaceAlias;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ReturnStateMachineProcessor implements ReorderProcessor
{
    private readonly ?Request $request;

    public function __construct(
        private readonly ReturnTypeServiceInterface $returnTypeService,
        RequestStack $requestStack,
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param Order $reorder
     */
    public function process(OrderInterface $order, OrderInterface $reorder): void
    {
        $channel = $reorder->getChannel();

        if (ChannelCode::CHANNEL_RETURN !== $channel->getCode()) {
            return;
        }

        $returnType = $this->returnTypeService->getReturnType($reorder);

        if (in_array($returnType, [
            Reason::RETURN_TYPE_SENDER,
            Reason::RETURN_TYPE_WAREHOUSE,
            Reason::RETURN_TYPE_REFUND,
        ], true)) {
            $this->request->request->add(['toAddress' => false]);
            $this->request->request->add(['toSelectShipping' => false]);
            $this->request->request->add(['toComplete' => false]);

            $reorder->setState(OrderInterfaceAlias::STATE_FULFILLED);
            $reorder->setPaymentState(OrderPaymentStates::STATE_PAID);
            $reorder->setShippingState(OrderShippingStates::STATE_SHIPPED);
            $reorder->setCheckoutState(OrderCheckoutStates::STATE_COMPLETED);
            $reorder->setCheckoutCompletedAt(new \DateTime());
        } elseif (Reason::RETURN_TYPE_AUTONOMOUS === $returnType) {
            $this->request->request->add(['toSelectShipping' => false]);
            $this->request->request->add(['toComplete' => false]);

            $reorder->setState(OrderInterfaceAlias::STATE_CART);
            $reorder->setPaymentState(OrderPaymentStates::STATE_PAID);
            $reorder->setShippingState(OrderShippingStates::STATE_CART);
            $reorder->setCheckoutState(OrderCheckoutStates::STATE_ADDRESSED);
            $reorder->setCheckoutCompletedAt(new \DateTime());
            $reorder->setOdooState(OdooOrderInterface::ODOO_STATE_NOT_READY);
        }
    }
}
