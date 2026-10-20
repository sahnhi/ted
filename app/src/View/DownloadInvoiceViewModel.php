<?php

declare(strict_types=1);

namespace App\View;

use Gaufrette\File;

final class DownloadInvoiceViewModel implements \JsonSerializable
{
    private readonly string $content;

    public function __construct(
        File $fileContent,
        private readonly \DateTimeImmutable $issuedAt,
        private readonly string $number,
        private readonly int $total,
        private readonly ?int $orderTaxTotal = null
    ) {
        $this->content = base64_encode((string) $fileContent->getContent());
    }

    public function jsonSerialize(): mixed
    {
        return [
            'invoiceNumber' => $this->number,
            'invoiceIssuedAt' => $this->issuedAt->format('d-m-Y'),
            'total' => $this->total,
            'vatCharge' => $this->orderTaxTotal,
            'fileContent' => $this->content,
        ];
    }
}
