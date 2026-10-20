<?php

namespace App\Services\Admin;

use App\Doctrine\Entity\Invoice;
use App\Doctrine\Entity\Sylius\Invoice as InvoicePlugin;
use App\Doctrine\Entity\Sylius\InvoiceInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\InvoicePluginRepository;
use App\Doctrine\Repository\InvoiceRepositoryInterface;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Exception\InvoiceNotFound;
use App\Identifier\OrderNumber;
use App\Message\DTO\OrderDTO;
use App\Services\Channel\ChannelCode;
use App\Services\Filesystem\GoogleStorageInvoiceFilesystem;
use App\Services\InvoiceGenerator\InvoiceGenerator;
use App\View\DownloadInvoiceViewModel;
use Gaufrette\Exception\FileNotFound;

class InvoiceService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly InvoicePluginRepository $invoicePluginRepository,
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly GoogleStorageInvoiceFilesystem $fileSystem,
        private readonly InvoiceGenerator $invoiceGenerator,
    ) {
    }

    public function getFileFromOrderDTO(OrderDTO $order): mixed
    {
        if ($invoice = $this->getInvoiceFromOrderDTO($order)) {
            return $this->getFile($invoice);
        }

        throw new FileNotFound('Invoice not found for order #' . $order->getNumber());
    }

    public function getFileFromInvoicePlugin(InvoicePlugin $invoice): mixed
    {
        return $this->getFile($this->getInvoiceFromInvoicePlugin($invoice));
    }

    public function getFileFromOrderNumber(OrderNumber $orderNumber): DownloadInvoiceViewModel
    {
        $orderIdAndCheckoutDate = $this->orderRepository->findInvoiceIdForPaidOrderByOrderNumber($orderNumber);

        $invoice = $this->invoiceRepository->findOneBy(['order' => $orderIdAndCheckoutDate['id']]);

        if (null === $invoice) {
            $invoice = $this->invoicePluginRepository->findCarrierInvoiceByOrderNumber($orderNumber);
        }

        if (null === $invoice) {
            $invoice = $this->invoicePluginRepository->findInvoiceByOrderNumber($orderNumber);
        }

        if (null === $invoice) {
            throw new InvoiceNotFound();
        }

        $invoiceFileName = $invoice->number() . '_' . $invoice->orderNumber();
        $order = $this->orderRepository->findOneBy(['number' => $invoice->orderNumber()]);
        $orderTaxTotal = $order instanceof Order ? $order->getTaxTotal() : null;

        return new DownloadInvoiceViewModel(
            $this->getFile($invoice),
            \DateTimeImmutable::createFromMutable($orderIdAndCheckoutDate['checkoutCompletedAt']),
            $invoiceFileName,
            $orderIdAndCheckoutDate['total'],
            $orderTaxTotal
        );
    }

    private function getInvoiceFromOrderDTO(OrderDTO $orderDTO): InvoicePlugin|Invoice|null
    {
        /** @var Order $order */
        $order = $this->orderRepository->find($orderDTO->getId());

        $number = ChannelCode::CHANNEL_BIS === $order->getChannel()->getCode() ?
            $order->getParentNumber() :
            $order->getNumber();

        /** @var Invoice|null $invoice */
        $invoice = $this->invoiceRepository->findOneBy(['order' => $order]);

        return is_null($invoice) ?
            $this->invoicePluginRepository->findOneByOrderNumber($number) :
            $invoice;
    }

    private function getInvoiceFromInvoicePlugin(InvoiceInterface $invoicePlugin): InvoicePlugin|InvoiceInterface|Invoice
    {
        $order = $this->orderRepository->findOneByNumber($invoicePlugin->orderNumber());
        $invoice = $this->invoiceRepository->findOneBy(['order' => $order]);

        return is_null($invoice) ?
            $invoicePlugin :
            $invoice;
    }

    private function getFile($invoice): mixed
    {
        $invoiceFilename = $this->getInvoiceFileName($invoice);

        if (!$this->fileSystem->exists($invoiceFilename)) {
            $this->invoiceGenerator->regenerate($invoice);
            throw new InvoiceNotFound(sprintf('invoice %s is being regenerated, try again in a few minutes', $invoiceFilename));
        }

        return $this->fileSystem->retrieve($invoiceFilename);
    }

    private function getInvoiceFileName(InvoicePlugin|Invoice $invoice): string
    {
        $usePlugin = $invoice instanceof InvoicePlugin;

        $year = substr($usePlugin ? $invoice->number() : $invoice->getRef(), 1, 4);
        $month = substr($usePlugin ? $invoice->number() : $invoice->getRef(), 5, 2);

        $invoiceFilename = $year . '/' . $month . '/';

        return $invoiceFilename . ($usePlugin ?
            ($invoice->number() . '_' . $invoice->orderNumber() . ($invoice->getType() ? '_' . $invoice->getType() : '') . '.pdf') :
            $invoice->getFileName());
    }
}
