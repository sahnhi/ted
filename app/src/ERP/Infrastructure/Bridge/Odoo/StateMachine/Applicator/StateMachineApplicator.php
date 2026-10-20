<?php

declare(strict_types=1);

namespace App\ERP\Infrastructure\Bridge\Odoo\StateMachine\Applicator;

use App\Doctrine\Entity\Sylius\Order;
use App\ERP\Domain\StateMachine\Applicator\Exception\TransitionFailException;
use App\ERP\Domain\StateMachine\Applicator\StateMachineApplicatorInterface;
use App\ERP\Infrastructure\Bridge\Winzou\StateMachine\Applicator\StateMachineApplicator as WinzouStateMachineApplicator;

final class StateMachineApplicator implements StateMachineApplicatorInterface
{
    public function __construct(
        private readonly WinzouStateMachineApplicator $applicator,
    ) {
    }

    /**
     * @throws TransitionFailException
     */
    public function cancelOrder(Order $order): void
    {
        try {
            $this->applicator->cancelOrder($order);
        } catch (\Throwable $e) {
            throw new TransitionFailException($e->getMessage(), code: $e->getCode(), previous: $e);
        }
    }

    /**
     * @throws TransitionFailException
     */
    public function confirmReturn(Order $order): void
    {
        try {
            $this->applicator->confirmReturn($order);
        } catch (\Throwable $e) {
            throw new TransitionFailException($e->getMessage(), code: $e->getCode(), previous: $e);
        }
    }

    /**
     * @throws TransitionFailException
     */
    public function createOrder(Order $order): void
    {
        try {
            $this->applicator->createOrder($order);
        } catch (\Throwable $e) {
            throw new TransitionFailException($e->getMessage(), $e->getCode(), previous: $e);
        }
    }

    /**
     * @throws TransitionFailException
     */
    public function createReturn(Order $order): void
    {
        try {
            $this->applicator->createReturn($order);
        } catch (\Throwable $e) {
            throw new TransitionFailException($e->getMessage(), code: $e->getCode(), previous: $e);
        }
    }

    /**
     * @throws TransitionFailException
     */
    public function shipOrder(Order $order): void
    {
        try {
            $this->applicator->shipOrder($order);
        } catch (\Throwable $e) {
            throw new TransitionFailException($e->getMessage(), code: $e->getCode(), previous: $e);
        }
    }

    /**
     * @throws TransitionFailException
     */
    public function destockOrder(Order $order): void
    {
        try {
            $this->applicator->destockOrder($order);
        } catch (\Throwable $e) {
            throw new TransitionFailException($e->getMessage(), code: $e->getCode(), previous: $e);
        }
    }
}
