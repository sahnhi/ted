<?php

namespace App\Payum\Braintree\Action;

use App\Model\Payum\PaymentInterface;
use Payum\Braintree\Action\ConvertPaymentAction as BaseConvertPaymentAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;

class ConvertPaymentAction extends BaseConvertPaymentAction
{
    /**
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));

        $details['amount'] = $payment->getTotalAmount() / 10 ** $currency->exp;

        $details['orderId'] = $payment->getNumber();

        // BEGIN : Custom lines from the original file
        $details['customer'] = ['id' => $payment->getClientId(), 'email' => $payment->getClientEmail(), 'firstName' => $payment->getOrder()->getCustomer()->getFirstName(), 'lastName' => $payment->getOrder()->getCustomer()->getLastName(), 'phone' => preg_replace('/[\(\)\s\-\.\+]/', '', (string) $payment->getOrder()->getCustomer()->getPhoneNumber())];

        $details['shipping'] = ['firstName' => $payment->getOrder()->getShippingAddress()->getFirstName(), 'lastName' => $payment->getOrder()->getShippingAddress()->getLastName(), 'company' => $payment->getOrder()->getShippingAddress()->getCompany(), 'streetAddress' => $payment->getOrder()->getShippingAddress()->getStreet(), 'locality' => $payment->getOrder()->getShippingAddress()->getCity(), 'postalCode' => $payment->getOrder()->getShippingAddress()->getPostcode(), 'countryCodeAlpha2' => $payment->getOrder()->getShippingAddress()->getCountryCode()];

        $details['billing'] = ['firstName' => $payment->getOrder()->getBillingAddress()->getFirstName(), 'lastName' => $payment->getOrder()->getBillingAddress()->getLastName(), 'company' => $payment->getOrder()->getBillingAddress()->getCompany(), 'streetAddress' => $payment->getOrder()->getBillingAddress()->getStreet(), 'locality' => $payment->getOrder()->getBillingAddress()->getCity(), 'postalCode' => $payment->getOrder()->getBillingAddress()->getPostcode(), 'countryCodeAlpha2' => $payment->getOrder()->getBillingAddress()->getCountryCode()];
        // ENDED : Custom lines from the original file

        $request->setResult((array) $details);
    }
}
