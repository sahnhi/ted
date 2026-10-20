<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Order;
use Sylius\Component\Resource\Model\ResourceInterface;

interface InvoiceInterface extends ResourceInterface
{
    public function getId();

    public function getRef(): string;

    public function getOrder(): Order;

    public function getFileName(): string;
}
