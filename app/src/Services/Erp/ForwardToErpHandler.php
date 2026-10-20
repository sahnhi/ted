<?php

namespace App\Services\Erp;

use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use Sylius\Component\Core\OrderPaymentStates;

class ForwardToErpHandler
{
    public function __construct(
        private readonly ForwardToErp $forwardToErp,
        private readonly ReturnTypeServiceInterface $returnTypeService
    ) {
    }

    /**
     * The Order need manual forward ("pay" state_machine transition not triggered).
     */
    public function handleForward(Order $order)
    {
        $channel = $order->getChannel();

        switch ($channel->getCode()) {
            case 'operation':
            case 'b2b':
                $this->forwardToErp->postComplete($order);
                break;

            case 'bis':
                if (OrderPaymentStates::STATE_PAID === $order->getPaymentState()) {
                    $this->forwardToErp->postComplete($order);
                }
                break;

            case 'return':
                if (!in_array($this->returnTypeService->getReturnType($order), [Reason::RETURN_TYPE_REFUND, Reason::RETURN_TYPE_SENDER, Reason::RETURN_TYPE_WAREHOUSE])) {
                    $this->forwardToErp->postComplete($order);
                }
                break;
        }
    }
}
