<?php

declare(strict_types=1);

namespace App\Utils;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

final class PhoneNumber
{
    public function __construct(
        private readonly PhoneNumberUtil $phoneNumberUtil
    ) {
    }

    /**
     * @throws NumberParseException
     */
    public function formatInE164(string $phoneNumber): ?string
    {
        $phone = $this->phoneNumberUtil->parse($phoneNumber, 'FR');

        return $phone ? $this->phoneNumberUtil->format($phone, PhoneNumberFormat::E164) : null;
    }
}
