<?php

namespace App\Api\Odoo\DTO;

use JMS\Serializer\Annotation\Type;

class Invoice
{
    /**
     * @var string
     *
     * @Type("string")
     */
    private $orderRef;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $invoiceRef;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $invoice;

    public function getOrderRef(): string
    {
        return $this->orderRef;
    }

    public function getInvoiceRef(): string
    {
        return $this->invoiceRef;
    }

    public function getInvoice(): string
    {
        return $this->invoice;
    }
}
