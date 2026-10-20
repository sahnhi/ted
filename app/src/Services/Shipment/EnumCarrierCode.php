<?php

namespace App\Services\Shipment;

use App\Doctrine\Entity\Sylius\ShippingMethodInterface;

class EnumCarrierCode
{
    public const CARRIER_CODE_DPD = 'dpd_fr';
    public const CARRIER_CODE_VIR = 'vir';
    public const CARRIER_CODE_GEO = 'geodis_agence';
    public const CARRIER_CODE_POSTE = 'colissimo';
    public const CARRIER_CODE_TRUSK = 'trusk';
    public const CARRIER_CODE_DHL = 'dhl';
    public const CARRIER_CODE_FREXP = 'france_express';
    public const CARRIER_CODE_CHRONOPOST = 'chronopost';

    public const CARRIER_CODES = [
        ShippingMethodInterface::METHOD_DPD => self::CARRIER_CODE_DPD,
        ShippingMethodInterface::METHOD_VIR => self::CARRIER_CODE_VIR,
        ShippingMethodInterface::METHOD_GEODIS => self::CARRIER_CODE_GEO,
        ShippingMethodInterface::METHOD_POSTE => self::CARRIER_CODE_POSTE,
        ShippingMethodInterface::METHOD_TRUSK => self::CARRIER_CODE_TRUSK,
        ShippingMethodInterface::METHOD_TRUSK_WEB => self::CARRIER_CODE_TRUSK,
        ShippingMethodInterface::METHOD_DHL => self::CARRIER_CODE_DHL,
        ShippingMethodInterface::METHOD_FRANCE_EXPRESS => self::CARRIER_CODE_FREXP,
        ShippingMethodInterface::METHOD_CHRONOPOST => self::CARRIER_CODE_CHRONOPOST,
    ];
}
