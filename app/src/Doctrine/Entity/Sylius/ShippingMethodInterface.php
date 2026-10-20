<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\FtpConfigurationAwareInterface;
use App\Doctrine\Entity\Warehouse;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\ShippingMethodInterface as BaseShippingMethodInterface;

interface ShippingMethodInterface extends BaseShippingMethodInterface, FtpConfigurationAwareInterface
{
    public const METHOD_POSTE = 'POSTE';
    public const METHOD_EDELIV = 'E-DELIV';
    public const METHOD_VIR = 'VIR';
    public const METHOD_OLVO = 'OLVO';
    public const METHOD_UBM = 'UBM MILANO';
    public const METHOD_MENSOS = 'MENSOS MADRID';
    public const METHOD_RETOURINTER = 'RETOURINTER';
    public const METHOD_FRANCE_EXPRESS = 'FRANCE-EXPRESS';
    public const METHOD_STORE = 'MAGASIN';
    public const METHOD_COLISWEB = 'COLISWEB';
    public const METHOD_DHL = 'DHL';
    public const METHOD_DPD = 'DPD';
    public const METHOD_GEODIS = 'GEO';
    public const METHOD_TRUSK_WEB = 'TRUSK_WEB';
    public const METHOD_TRUSK = 'TRUSK';
    public const METHOD_SPRINGARE = 'SPRINGARE';
    public const METHOD_CHRONOPOST = 'CHRONOPOST';
    public const METHOD_DPD_RELAY = 'DPWR';
    public const SPIDY_CODE_CHRONOPOST = 'CR13';
    public const SPIDY_AA_CHRONOPOST = 'TEDIBER';

    public const BIKE_DELIVERY = [
        self::METHOD_OLVO,
        self::METHOD_UBM,
        self::METHOD_MENSOS,
    ];

    public const META_DATA_METHOD = [
        self::METHOD_EDELIV => self::METHOD_EDELIV,
    ];

    public const METHOD_RELAY = [
        self::METHOD_CHRONOPOST,
        self::METHOD_DPD_RELAY,
    ];

    public const SMALL_PACKAGE_DELIVERIES = [
        self::METHOD_DPD,
    ];

    public const SPIDY_DELIVERIES = [
        self::METHOD_POSTE,
        self::METHOD_OLVO,
        self::METHOD_STORE,
        self::METHOD_COLISWEB,
        self::METHOD_SPRINGARE,
        self::METHOD_TRUSK_WEB,
    ];

    public const CATEGORY_EXPRESS_PETIT_COLIS = 'EXPRESS_PETIT_COLIS';
    public const CATEGORY_EXPRESS_ENCOMBRANT = 'EXPRESS_ENCOMBRANT';
    public const CATEGORY_CONFORT = 'CONFORT';
    public const CATEGORY_PREMIUM = 'PREMIUM';
    public const CATEGORY_GRAND_OURS = 'GRAND_OURS';

    public function getTransportTime(): ?float;

    public function getShippingTimeDate(): ?float;

    public function getShipupCarrierCode(): ?string;

    public function setShipupCarrierCode(?string $shipupCarrierCode): void;

    public function getWarehouse(): ?Warehouse;

    public function getShippingCategory(): Collection;
}
