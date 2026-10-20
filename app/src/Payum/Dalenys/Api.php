<?php

namespace App\Payum\Dalenys;

use Payum\Be2Bill\Api as BaseApi;
use Webmozart\Assert\Assert;

class Api extends BaseApi
{
    public const EXECCODE_INVALID_REFUND_AMOUNT = '2008';

    public const OPERATION_VOID = 'void';

    public const OPERATION_STOPNTIMES = 'stopntimes';

    public const SCHEDULE_STATUS_FULLY_REFUNDED = 'fully_refunded';

    /**
     * @return string
     */
    public function calculateHash(array $params)
    {
        // Alpha sort
        ksort($params);

        $clearString = $this->options['password'];
        foreach ($params as $key => $value) {
            if (!is_array($value)) {
                $clearString .= $key . '=' . $value . $this->options['password'];
            } else {
                foreach ($value as $keyArray => $valueArray) {
                    $clearString .= $key . '[' . $keyArray . ']=' . $valueArray . $this->options['password'];
                }
            }
        }

        return hash('sha256', (string) $clearString);
    }

    public function getTotalAmounts(array $amounts)
    {
        return array_sum($amounts);
    }

    public function refund($transactionId, $orderId, $description, ?int $amount = null, array $options = []): ?array
    {
        $params = $options;
        $params['OPERATIONTYPE'] = static::OPERATION_REFUND;
        $params['DESCRIPTION'] = $description;
        $params['TRANSACTIONID'] = $transactionId;
        $params['ORDERID'] = $orderId;

        if (!empty($amount)) {
            $params['AMOUNT'] = $amount;
        }

        return (array) $this->request($params);
    }

    public function stopNTimes($scheduleId, array $options = []): ?array
    {
        $params = $options;
        $params['OPERATIONTYPE'] = static::OPERATION_STOPNTIMES;
        $params['SCHEDULEID'] = $scheduleId;

        return (array) $this->request($params);
    }

    protected function request(array &$params)
    {
        Assert::notEmpty($params['OPERATIONTYPE'], 'OPERATIONTYPE field is required.');

        $this->addGlobalParams($params);

        return $this->doRequest([
            'method' => $params['OPERATIONTYPE'],
            'params' => $params,
        ]);
    }
}
