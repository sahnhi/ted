<?php

namespace App\Generator;

use App\Model\GiftCard\GiftCardPdf;

interface GiftCardPdfFileGeneratorInterface
{
    public function generate(string $giftCardId): GiftCardPdf;
}
