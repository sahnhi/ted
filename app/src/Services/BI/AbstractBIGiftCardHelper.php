<?php

namespace App\Services\BI;

abstract class AbstractBIGiftCardHelper
{
    public static function getGiftCardName($orderGiftCards): string
    {
        $giftCards = [];
        foreach ($orderGiftCards as $giftCard) {
            $amount = $giftCard->getInitialAmount() / 100;
            $giftCardName = 'carte-cadeau-' . $amount;
            $giftCards[] = $giftCardName;
        }

        return implode(', ', $giftCards);
    }
}
