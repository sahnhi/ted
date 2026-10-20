<?php

declare(strict_types=1);

namespace App\Api\AdyenPayByLink\Query;

use App\Doctrine\Entity\Sylius\CustomerInterface;
use App\Doctrine\Entity\Sylius\OrderInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Webmozart\Assert\Assert;

final class AdyenPayByLinkQueryFactory
{
    public const COUNTRY_CODE_FR = 'FR';

    public const EXPIRACY_DELAY_IN_MINUTES = 15;

    /**
     * @throws \Exception
     */
    public function create(OrderInterface $order, string $paymentReference, string $merchantAccount): AdyenPayByLinkQuery
    {
        Assert::isInstanceOf($order->getBillingAddress(), AddressInterface::class);
        Assert::isInstanceOf($order->getCustomer(), CustomerInterface::class);

        return new AdyenPayByLinkQuery(
            amount: $order->getTotal(),
            currency: $order->getCurrencyCode(),
            merchantAccount: $merchantAccount,
            shopperLocale: $order->getLocaleCode(),
            countryCode: $order->getBillingAddress()->getCountryCode() ?? self::COUNTRY_CODE_FR,
            customerEmail: $order->getCustomer()->getEmail(),
            paymentReference: $paymentReference,
            expiracyDate: $this->getExpiracyDate(),
        );
    }

    private function getExpiracyDate(): \DateTimeInterface
    {
        $date = new \DateTimeImmutable('now');

        return $date->add(new \DateInterval('PT' . self::EXPIRACY_DELAY_IN_MINUTES . 'M'));
    }
}
