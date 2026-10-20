<?php

namespace App\Services\Cron\Transmission;

use App\Doctrine\Entity\Sylius\ShippingMethodInterface;

interface PackingProcessorInterface extends RecapProcessorInterface
{
    public const SHIPPING_METHOD_ELIGIBLE = ShippingMethodInterface::METHOD_VIR;
    public const PACKING_LOCAL_FOLDER = 'packing/';
    public const PACKING_GOOGLE_FOLDER = 'Packing/';
    public const CR_PRE_PACKING_CODE_COLUMN = 20;
    public const CR_PRE_BATCH_NUMBER_COLUMN = 8;
    public const ENCODING_WINDOWS = 'Windows-1252';
    public const ENCODING_UTF8 = 'UTF-8';

    public const LIMIT_ADDRESS1 = 31;
    public const LIMIT_ADDRESS2 = 63;

    public const VIR_CSV_COLUMN = [
        'packingCode' => 5,
        'variantCode' => 3,
        'quantity' => 6,
        'lineNumber' => 2,
        'productCode' => 16,
        'wrongLineNumber' => 17,
        'shippedQuantity' => 7,
        'stockType' => 8,
        'orderDate' => 22,
        'slotDate' => 25,
        'slotTime' => 26,
        'fullname' => 28,
        'street' => 29,
        'street2' => 30,
        'city' => 33,
        'phoneNumber' => 36,
        'difficulty' => 42,
        'comment1' => 43,
        'comment2' => 44,
    ];
}
