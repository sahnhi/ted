<?php

namespace App\MessageHandler;

use App\Message\Invoice;
use App\Services\Filesystem\GoogleStorageInvoiceFilesystem;
use Sylius\InvoicingPlugin\Filesystem\TemporaryFilesystem;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class InvoiceHandler implements MessageHandlerInterface
{
    protected TemporaryFilesystem $temporaryFilesystem;

    public function __construct(
        protected GoogleStorageInvoiceFilesystem $invoiceUploader
    ) {
        $this->temporaryFilesystem = new TemporaryFilesystem();
    }

    public function __invoke(Invoice $message)
    {
        $this->temporaryFilesystem->executeWithFile(
            $message->getFilename(),
            $message->getContent(),
            function (string $filepath) use ($message): void {
                $this->invoiceUploader->upload($filepath, $message->getNumber());
            }
        );
    }
}
