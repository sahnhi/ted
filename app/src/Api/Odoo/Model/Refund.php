<?php

namespace App\Api\Odoo\Model;

use App\Api\Odoo\Api\OdooApiInterface;
use JMS\Serializer\Annotation as Serializer;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

class Refund implements RefundInterface
{
    /**
     * @var string
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_CONFIRM_RETURN})
     */
    public $name;

    /**
     * @var string
     *
     * @Serializer\Type("integer")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_CONFIRM_RETURN})
     *
     * @Serializer\SerializedName("amount_refunded")
     */
    public $amount;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     *
     * @Serializer\Groups({OdooApiInterface::API_CALL_CONFIRM_RETURN})
     *
     * @Serializer\SerializedName("transaction_id")
     */
    public $transactionId;

    public function __construct(PaymentInterface $payment)
    {
        Assert::notNull($payment->getOrder());

        $this->name = $payment->getOrder()->getNumber();
        $this->amount = $payment->getAmount();

        $details = $payment->getDetails();
        Assert::keyExists($details, 'REFUND');

        $this->transactionId = $details['REFUND'];
    }
}
