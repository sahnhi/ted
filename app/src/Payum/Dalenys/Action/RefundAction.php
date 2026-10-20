<?php

namespace App\Payum\Dalenys\Action;

use App\Payum\Dalenys\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Payum;
use Payum\Core\Request\Refund;

final class RefundAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    public function __construct(
        private Payum $payum
    ) {
        $this->apiClass = Api::class;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = new ArrayObject($request->getModel());

        $refundAmount = $model['refund'];

        $refundCode = '';

        $totalRefundAmount = $refundAmount['value'];
        foreach ($model['SCHEDULEDATA'] as $keyScheduleData => $scheduleData) {
            if (!empty($scheduleData['TRANSACTIONID']) && Api::SCHEDULE_STATUS_FULLY_REFUNDED !== $scheduleData['STATUS']) {
                $refundAmount = $totalRefundAmount;

                if ($totalRefundAmount > $scheduleData['AMOUNT']) {
                    $refundAmount = $totalRefundAmount - ($totalRefundAmount - $scheduleData['AMOUNT']);
                    $restRefundAmount = $scheduleData['AMOUNT'] - $scheduleData['REFUNDED_AMOUNT'];

                    if ($refundAmount > $restRefundAmount) {
                        $refundAmount = $restRefundAmount;
                    }
                }

                $refund = $this->api->refund($scheduleData['TRANSACTIONID'], $model['ORDERID'], 'Remboursement ' . $scheduleData['TRANSACTIONID'], $refundAmount);

                // ajout du stopntimes après un remboursement
                $stopntimes = $this->api->stopNTimes($model['SCHEDULEDATA'][0]['TRANSACTIONID']);

                if (Api::EXECCODE_SUCCESSFUL === $refund['EXECCODE']) {
                    $refundCode .= $refund['TRANSACTIONID'] . ', ';

                    $totalRefundAmount -= $refundAmount;

                    $newScheduleData = $model['SCHEDULEDATA'];
                    $newScheduleData[$keyScheduleData]['REFUNDED_AMOUNT'] += $refundAmount;

                    if ($newScheduleData[$keyScheduleData]['REFUNDED_AMOUNT'] === $scheduleData['AMOUNT']) {
                        $newScheduleData[$keyScheduleData]['STATUS'] = Api::SCHEDULE_STATUS_FULLY_REFUNDED;
                    }

                    $model['SCHEDULEDATA'] = $newScheduleData;
                }

                if ($totalRefundAmount <= 0) {
                    break;
                }
            }
        }

        $refundCode = rtrim($refundCode, ', ');
        $model['REFUNDED_LAST_TRANSACTIONS_ID'] = $refundCode;

        $refundedAmount = 0;
        foreach ($model['SCHEDULEDATA'] as $scheduleData) {
            $refundedAmount += $scheduleData['REFUNDED_AMOUNT'];
        }

        if ($refundedAmount >= $this->api->getTotalAmounts($model['AMOUNTS'])) {
            $extraData = json_decode((string) $model['EXTRADATA'], true);
            $refundToken = $this->payum->getTokenStorage()->find($extraData['refund_token']);

            $this->payum->getHttpRequestVerifier()->invalidate($refundToken);
        }

        unset($model['refund']);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Refund
            && $request->getModel() instanceof \ArrayAccess
        ;
    }
}
