<?php

namespace App\Payum\Dalenys\Action;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\PaymentMethodInterface;
use App\Services\Channel\ChannelCode;
use Payum\Be2Bill\Action\ConvertPaymentAction as Be2billConvertPaymentAction;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Component\Payment\Model\PaymentInterface as PaymentInterfaceAlias;

class ConvertPaymentAction extends Be2billConvertPaymentAction implements GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;

    /**
     * @param Convert $request
     */
    public function execute($request)
    {
        parent::execute($request);

        $payment = $request->getSource();
        $model = $request->getResult();
        $amount = $model['AMOUNT'] / 100;
        /** @var Order $order */
        $order = $payment->getOrder();

        if ($this->isPaymentDelayed($order)) {
            $model = array_merge($model, $this->calculateAmountsDelayed($amount));
        } else {
            $model = array_merge($model, $this->calculateAmounts($amount));
        }

        $model['ORDERID'] = $order->getId();
        $model['SCHEDULEDATA'] = [];
        foreach ($model['AMOUNTS'] as $modelAmount) {
            $model['SCHEDULEDATA'] = array_merge($model['SCHEDULEDATA'], [
                [
                    'AMOUNT' => $modelAmount,
                    'REFUNDED_AMOUNT' => 0,
                    'TRANSACTIONID' => '',
                    'STATUS' => '',
                    'MESSAGE' => '',
                    'EXECCODE' => '',
                ],
            ]);
        }
        $model['3DSECURE'] = true;
        $model['3DSECUREDISPLAYMODE'] = 'main';
        $model['CLIENTIP'] = '127.0.0.1'; // get clientIP instead?
        $model['CLIENTREFERRER'] = 'www.tediber.com';
        $model['OPERATIONTYPE'] = 'payment';
        $model['BILLINGCITY'] = mb_substr((string) $order->getBillingAddress()->getCity(), 0, 255, 'UTF-8');
        $model['BILLINGCOUNTRY'] = $order->getBillingAddress()->getCountryCode();
        $model['BILLINGADDRESS'] = mb_substr(str_replace("\n", ' ', (string) $order->getBillingAddress()->getStreet()), 0, 50, 'UTF-8');
        $model['BILLINGPOSTALCODE'] = mb_substr((string) $order->getBillingAddress()->getPostcode(), 0, 9, 'UTF-8');
        $model['SHIPTOCITY'] = mb_substr((string) $order->getShippingAddress()->getCity(), 0, 255);
        $model['SHIPTOCOUNTRY'] = $order->getShippingAddress()->getCountryCode();
        $model['SHIPTOADDRESS'] = mb_substr(str_replace("\n", ' ', (string) $order->getBillingAddress()->getStreet()), 0, 50, 'UTF-8');
        $model['SHIPTOPOSTALCODE'] = mb_substr((string) $order->getShippingAddress()->getPostcode(), 0, 9, 'UTF-8');
        $model['SHIPTOADDRESSTYPE'] = 'new';

        unset($model['AMOUNT']);

        // set extra tokens
        $extraData = isset($model['EXTRADATA']) && is_array($model['EXTRADATA'])
            ? json_decode($model['EXTRADATA'], true)
            : []
        ;

        if (!isset($extraData['capture_token']) && $request->getToken()->getHash()) {
            $extraData['capture_token'] = $request->getToken()->getHash();
        }

        if (!isset($extraData['notify_token']) && $request->getToken() && $this->tokenFactory) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $request->getToken()->getGatewayName(),
                $request->getToken()->getDetails()
            );

            $extraData['notify_token'] = $notifyToken->getHash();
        }

        if (false === isset($extraData['refund_token']) && $this->tokenFactory) {
            $extraData['refund_token'] = $this->tokenFactory->createRefundToken(
                $request->getToken()->getGatewayName(),
                $request->getToken()->getDetails() ?? $model
            )->getHash();
        }

        $model['EXTRADATA'] = json_encode($extraData);

        $request->setResult((array) $model);
    }

    /**
     * Calculate the amount of each mensuality.
     */
    public function calculateAmounts(float $amount): array
    {
        $amounts = [];
        $floatBaseMonthAmount = $amount / 3;
        $intBaseAmountMonthly = intval($floatBaseMonthAmount);
        $date1 = new \DateTime();
        $date2 = new \DateTime('+1 month');
        $date3 = new \DateTime('+2 months');

        $amounts['AMOUNTS'][$date1->format('Y-m-d')] = (int) ($intBaseAmountMonthly + ($amount - (3 * $intBaseAmountMonthly))) * 100;
        $amounts['AMOUNTS'][$date2->format('Y-m-d')] = $intBaseAmountMonthly * 100;
        $amounts['AMOUNTS'][$date3->format('Y-m-d')] = $intBaseAmountMonthly * 100;

        return $amounts;
    }

    public function calculateAmountsDelayed(float $amount): array
    {
        $amounts = [];
        $floatBaseMonthAmount = $amount / 3;
        $intBaseAmountMonthly = intval($floatBaseMonthAmount);
        $date1 = new \DateTime();
        $date2 = new \DateTime('+60 days');
        $date3 = new \DateTime('+90 days');

        $amounts['AMOUNTS'][$date1->format('Y-m-d')] = (int) ($intBaseAmountMonthly + ($amount - (3 * $intBaseAmountMonthly))) * 100;
        $amounts['AMOUNTS'][$date2->format('Y-m-d')] = $intBaseAmountMonthly * 100;
        $amounts['AMOUNTS'][$date3->format('Y-m-d')] = $intBaseAmountMonthly * 100;

        return $amounts;
    }

    private function isPaymentDelayed(Order $order): bool
    {
        $channel = $order->getChannel();

        if (in_array($channel->getCode(), ChannelCode::SYLIUS_FRONT_END)) {
            return PaymentMethodInterface::BE2BILL_NTIMES_DELAYED === $order->getLastPayment(PaymentInterfaceAlias::STATE_NEW)->getMethod()->getCode();
        }

        return PaymentMethodInterface::BE2BILL_NTIMES_DELAYED === $order->getLastPayment(PaymentInterfaceAlias::STATE_CART)->getMethod()->getCode();
    }

    public function supports($request)
    {
        return
            $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && 'array' === $request->getTo()
        ;
    }
}
