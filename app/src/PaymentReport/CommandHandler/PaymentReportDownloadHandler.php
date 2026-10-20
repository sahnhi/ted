<?php

declare(strict_types=1);

namespace App\PaymentReport\CommandHandler;

use App\PaymentReport\Command\DownloadReportCommand;
use App\PaymentReport\Downloader\PaymentReportDownloaderInterface;
use App\PaymentReport\Event\PaymentReportDownloaded;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class PaymentReportDownloadHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly PaymentReportDownloaderInterface $paymentReportDownloader,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function __invoke(DownloadReportCommand $command): void
    {
        $paymentReportDownloaded = $this->paymentReportDownloader->download($command->getReportToDownload());

        $this->eventDispatcher->dispatch(new PaymentReportDownloaded($paymentReportDownloaded->sanitize()));
    }
}
