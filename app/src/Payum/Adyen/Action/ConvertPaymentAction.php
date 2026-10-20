<?php

namespace App\Payum\Adyen\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Convert;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class ConvertPaymentAction implements ActionInterface
{
    public function execute($request): void
    {
        /* @var Convert $request */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        // TODO : warning it workds only with fr, es, it locales and not en, etc...
        $countryCode = substr((string) $order->getLocaleCode(), -2);
        if (!empty($order->getShippingAddress()->getCountryCode())) {
            $countryCode = $order->getShippingAddress()->getCountryCode();
        }

        $details['order'] = [
            'id' => $order->getId(),
            'channelCode' => $order->getChannel()->getCode(),
            'localeCode' => $order->getLocaleCode(),
            'countryCode' => $countryCode,
        ];

        if (!empty($order->getBillingAddress()->getCountryCode())) {
            $details['billingAddress'] = [
                'city' => ($order->getBillingAddress()->getCity() ?: 'NA'),
                'country' => ($order->getBillingAddress()->getCountryCode() ?: 'NA'),
                'houseNumberOrName' => ($order->getBillingAddress()->getStreet() ? $this->getHouseNumber($order->getBillingAddress()->getStreet()) : 'NA'),
                'postalCode' => ($order->getBillingAddress()->getPostcode() ?: 'NA'),
                'street' => ($order->getBillingAddress()->getStreet() ?: 'NA'),
            ];

            if ('NA' !== $details['billingAddress']['street']) {
                $details['billingAddress'] = array_merge($details['billingAddress'], [
                    'street' => trim(str_replace($details['billingAddress']['houseNumberOrName'], '', (string) $details['billingAddress']['street'])),
                ]);
            }
        }

        if (!empty($order->getShippingAddress()->getCountryCode())) {
            $details['deliveryAddress'] = [
                'city' => ($order->getShippingAddress()->getCity() ?: 'NA'),
                'country' => ($order->getShippingAddress()->getCountryCode() ?: 'NA'),
                'houseNumberOrName' => ($order->getShippingAddress()->getStreet() ? $this->getHouseNumber($order->getShippingAddress()->getStreet()) : 'NA'),
                'postalCode' => ($order->getShippingAddress()->getPostcode() ?: 'NA'),
                'street' => ($order->getShippingAddress()->getStreet() ?: 'NA'),
            ];

            if ('NA' !== $details['deliveryAddress']['street']) {
                $details['deliveryAddress'] = array_merge($details['deliveryAddress'], [
                    'street' => trim(str_replace($details['deliveryAddress']['houseNumberOrName'], '', (string) $details['deliveryAddress']['street'])),
                ]);
            }
        }

        $details['amount'] = [
            'value' => $payment->getAmount(),
            'currency' => $payment->getCurrencyCode(),
        ];

        $details['merchantReference'] = $order->getId() . '-' . $payment->getId();
        $details['merchantOrderReference'] = $order->getId();

        $details['customer'] = [
            'id' => $order->getCustomer()->getId(),
            'email' => $order->getCustomer()->getEmail(),
            'firstName' => $order->getBillingAddress()->getFirstName(),
            'lastName' => $order->getBillingAddress()->getLastName(),
        ];

        $request->setResult((array) $details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && 'array' === $request->getTo()
        ;
    }

    private function getHouseNumber(string $street)
    {
        preg_match('![0-9/-]+!', $street, $streetNumber);

        return current($streetNumber);
    }
}
