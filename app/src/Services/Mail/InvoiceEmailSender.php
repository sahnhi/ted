<?php

namespace App\Services\Mail;

use App\Services\Admin\InvoiceService;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;

final class InvoiceEmailSender implements InvoiceEmailSenderInterface
{
    public function __construct(
        private readonly MandrillEmail $mandrillEmail,
        private readonly InvoiceService $invoiceService
    ) {
    }

    public function sendInvoiceEmail(OrderInterface $order, InvoiceInterface $invoice): void
    {
        $customer = $order->getCustomer();

        $templateVars = [
            [
                'name' => 'EMAIL',
                'content' => $customer->getEmail(),
            ],
            [
                'name' => 'PRENOM',
                'content' => $customer->getFirstName(),
            ],
            [
                'name' => 'COMMANDE',
                'content' => $order->getNumber(),
            ],
            [
                'name' => 'USER_ID',
                'content' => $customer->getId(),
            ],
        ];

        $templateName = 'dartagnan-facture';

        $file = $this->invoiceService->getFileFromInvoicePlugin($invoice);

        $files[] = [
            'type' => 'application/pdf',
            'name' => basename((string) $file->getName()),
            'content' => base64_encode((string) $file->getContent()),
        ];

        $this->mandrillEmail->generateEmail(
            $order,
            $templateName,
            $templateVars,
            MandrillEmail::MERGE_LANGUAGE_HANDLEBARS,
            null,
            $files
        );
    }
}
