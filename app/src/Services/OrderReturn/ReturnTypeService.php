<?php

namespace App\Services\OrderReturn;

use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;
use Webmozart\Assert\Assert;

class ReturnTypeService implements ReturnTypeServiceInterface
{
    public function getReturnOrderPrefix(Order $order): ?string
    {
        Assert::notEmpty($order->getOrderReason()->toArray());

        $orderReason = $order->getOrderReason()->first();
        Assert::notNull($orderReason->getReason(), 'Reason cannot be null.');

        $returnType = $orderReason->getReason()->getReturnType();
        Assert::notNull($returnType, 'Return Type cannot be null');

        return self::RETURN_PREFIXES[$returnType] ?? self::RETURN_PREFIXES[Reason::RETURN_TYPE_NORMAL];
    }

    public function getTemplateOptions(Order $order): array
    {
        $templateOptions = [];

        foreach ($order->getOrderReason() as $orderReason) {
            Assert::notNull($orderReason->getReason(), 'Reason cannot be null.');
            $templateOptions = $orderReason->getReason()->getTemplateOptions() ?? [];
        }

        return $templateOptions;
    }

    public function getReturnType(Order $order): ?int
    {
        return $order->getReturnType();
    }
}
