<?php

namespace App\Api\Odoo\Service;

use App\Api\Odoo\DTO\Invoice as InvoiceDTO;
use App\Doctrine\Entity\Invoice as InvoiceAlias;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\InvoiceRepository;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Generator\InvoicePdfFileGenerator;
use App\Services\Filesystem\GoogleStorageInvoiceFilesystem;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Sylius\InvoicingPlugin\Filesystem\TemporaryFilesystem;
use Webmozart\Assert\Assert;

class Invoice
{
    private readonly TemporaryFilesystem $temporaryFilesystem;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly InvoiceRepository $invoiceRepository,
        private readonly GoogleStorageInvoiceFilesystem $invoiceUploader
    ) {
        $this->temporaryFilesystem = new TemporaryFilesystem();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create(InvoiceDTO $invoiceDTO)
    {
        /** @var Order $order */
        $order = $this->orderRepository->findOneByNumber($invoiceDTO->getOrderRef());
        Assert::notNull($order, 'Order with number ' . $invoiceDTO->getOrderRef() . ' not found');

        $invoice = $this->generateInvoice($order, $invoiceDTO);

        $this->temporaryFilesystem->executeWithFile(
            $invoice->getFileName(),
            base64_decode($invoiceDTO->getInvoice()),
            function (string $filepath) use ($invoice): void {
                $this->invoiceUploader->upload($filepath, $invoice->getFileName());
            }
        );
    }

    private function generateInvoice(Order $order, InvoiceDTO $invoiceDTO)
    {
        $invoice = $this->invoiceRepository->findOneBy(['ref' => $invoiceDTO->getInvoiceRef()]);

        $filename = str_replace('/', '_', $invoiceDTO->getInvoiceRef())
            . '_'
            . $order->getNumber()
            . InvoicePdfFileGenerator::FILE_EXTENSION;

        if (is_null($invoice)) {
            $invoice = new InvoiceAlias($invoiceDTO->getInvoiceRef(), $order, $filename);
            $this->entityManager->persist($invoice);
            $this->entityManager->flush();
        }

        return $invoice;
    }
}
