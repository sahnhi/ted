<?php

namespace App\MessageHandler;

use App\Message\DalenysNotify;
use App\Payum\Dalenys\Api;
use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DalenysNotifyHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly RepositoryInterface $paymentRepository,
        private readonly ObjectManager $paymentManager
    ) {
    }

    public function __invoke(DalenysNotify $message)
    {
        $details = $message->getDetails();
        $payload = $message->getPayload();

        /** @var PaymentInterface $payment */
        $payment = $this->paymentRepository->find($message->getPaymentId());

        $payload['notify_by_messenger'] = true;

        $details->replace($payload);

        if (!empty($payload['SCHEDULE'])) {
            $schedule = (int) current(explode('-', (string) $payload['SCHEDULE'])) - 1;

            $scheduleData = $details->getArrayCopy();
            if (!empty($scheduleData['SCHEDULEDATA'])) {
                $scheduleData['SCHEDULEDATA'][$schedule]['TRANSACTIONID'] = $payload['TRANSACTIONID'];
                $scheduleData['SCHEDULEDATA'][$schedule]['MESSAGE'] = $payload['MESSAGE'];
                $scheduleData['SCHEDULEDATA'][$schedule]['EXECCODE'] = $payload['EXECCODE'];
            }

            $details->replace($scheduleData);
        }

        if (!empty($payload['OPERATIONTYPE']) && Api::OPERATION_REFUND === $payload['OPERATIONTYPE']) {
            /** @var PaymentInterface $refundNull */
            $refundNull = $this->paymentRepository->findOneByRefundReferenceIdIsNull($payload['ORDERID'], $message->getPaymentId(), $payload['AMOUNT']);

            if (null !== $refundNull) {
                $refundNullDetails = $refundNull->getDetails();

                if (Api::EXECCODE_SUCCESSFUL === $payload['EXECCODE']) {
                    $refundNullDetails['REFUND'] = $payload['TRANSACTIONID'];
                } else {
                    $refundNullDetails['EXECCODE'] = $payload['EXECCODE'];
                }

                $refundNull->setDetails($refundNullDetails);

                $this->paymentManager->persist($refundNull);
            }
        }

        $payment->setDetails($details->toUnsafeArray());

        $this->paymentManager->persist($payment);
        $this->paymentManager->flush();
        $this->paymentManager->clear();
    }
}
