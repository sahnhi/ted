<?php

namespace App\Services\Transmission\Pickup;

use App\Services\Transmission\AbstractTransmissionService;

class PickupService extends AbstractTransmissionService
{
    protected array $normalizeArray = [];
    protected array $headerLine;

    public function __construct()
    {
        $this->headerLine = array_fill_keys(['Code pays', 'Raison sociale', 'taskId', 'dateShipment', 'products', 'reasons', 'Pre tournée / TRANSPORTEUR', 'paymentMethod'], '');
    }

    public function normalizeArray(array $order, int $numberLine): array
    {
        $values = parent::normalizeArray($order, $numberLine);

        $value['values'] = array_values($values);

        return $value;
    }
}
