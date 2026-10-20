<?php

namespace App\Services\Colissimo;

interface ColissimoApiInterface
{
    public const URL_TRACKING = 'https://www.laposte.fr/outils/suivre-vos-envois?code=';

    public function getLabel($order, $item);

    public function setArrayDatas($order, $item): array;
}
