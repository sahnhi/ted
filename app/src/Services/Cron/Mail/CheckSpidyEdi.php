<?php

namespace App\Services\Cron\Mail;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Services\Filesystem\GoogleStorageEdiFilesystem;
use App\Services\Sentry\ReportingInterface;
use Sentry;
use Sylius\Component\Payment\Model\PaymentInterface as PaymentInterfaceAlias;

class CheckSpidyEdi
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly GoogleStorageEdiFilesystem $googleStorageFilesystem,
        private readonly ReportingInterface $reporting
    ) {
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(): void
    {
        $orders = $this->orderRepository->getOrdersToCheckEdi();

        $date = new \DateTime();
        $interval = new \DateInterval('P1D');
        $date->sub($interval);
        $dirPath = 'CSV/Livraisons/Spidy/' . $date->format('Y') . '/' . $date->format('F') . '/';

        foreach ($orders as $order) {
            /* @var Order $order */
            try {
                $number = ltrim((string) $order->getNumber(), '0');
                $paymentDate = '';
                foreach ($order->getPayments() as $payment) {
                    if (PaymentInterfaceAlias::STATE_COMPLETED === $payment->getState()) {
                        $paymentDate = $payment->getCreatedAt()->format('ymd');
                    }
                }
                $fileName = $dirPath . 'SHIPUTF_' . $paymentDate . $number . 'ted.csv';
                $this->googleStorageFilesystem->setOrder($order)
                    ->readTemporaryFile($fileName);
            } catch (\Exception $e) {
                $this->reporting->reportIssue(
                    $e,
                    Sentry\Severity::error(),
                    ['Order' => $order->getNumber()]
                );
            }
        }
    }
}
