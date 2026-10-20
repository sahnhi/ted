<?php

declare(strict_types=1);

namespace App\PaymentReport\Model;

final class PaymentFactory
{
    private const ABBREVIATION_PATTERN = '/(^m\.|^mme\.|^mlle\.|^mle\.|^mr\.)?([a-z]*)?(\.mme$|\.mlle$|\.mle$|\.mr$|\.m$)?/i';

    public static function create(array $paymentReport): Payment
    {
        $customerName = strtolower(trim((string) $paymentReport['Shopper Name']));
        $customerNameWithoutAbbreviation = trim((string) preg_replace(self::ABBREVIATION_PATTERN, '$2', $customerName));

        $fullNameWithSlashSeparator = explode('/', $customerNameWithoutAbbreviation);

        switch (count($fullNameWithSlashSeparator)) {
            case 2:
                [$firstName, $lastName] = $fullNameWithSlashSeparator;
                break;
            case 1:
                if (1 !== substr_count($fullNameWithSlashSeparator[0], ' ')) {
                    $firstName = null;
                    $lastName = $customerNameWithoutAbbreviation;
                    break;
                }

                $fullNameWithWhiteSpaceSeparator = explode(' ', $customerNameWithoutAbbreviation);
                [$firstName, $lastName] = $fullNameWithWhiteSpaceSeparator;
                break;
            default:
                $firstName = null;
                $lastName = $customerNameWithoutAbbreviation;
        }

        return new Payment(
            $paymentReport['Merchant Reference'],
            $firstName,
            $lastName,
            (int) ($paymentReport['Main Amount'] * 100),
            $paymentReport['Booking Date'],
            $paymentReport['Psp Reference'],
            $paymentReport['Record Type'],
        );
    }
}
