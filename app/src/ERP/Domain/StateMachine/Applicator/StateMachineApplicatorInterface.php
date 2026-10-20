<?php

declare(strict_types=1);

namespace App\ERP\Domain\StateMachine\Applicator;

use App\Doctrine\Entity\Sylius\Order;
use App\ERP\Domain\StateMachine\Applicator\Exception\TransitionFailException;

interface StateMachineApplicatorInterface
{
    /**
     * @throws TransitionFailException
     */
    public function cancelOrder(Order $order): void;

    /**
     * @throws TransitionFailException
     */
    public function confirmReturn(Order $order): void;

    /**
     * @throws TransitionFailException
     */
    public function createOrder(Order $order): void;

    /**
     * @throws TransitionFailException
     */
    public function createReturn(Order $order): void;

    /**
     * @throws TransitionFailException
     */
    public function shipOrder(Order $order): void;

    /**
     * @throws TransitionFailException
     */
    public function destockOrder(Order $order): void;
}
