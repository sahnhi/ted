<?php

namespace App\Controller\Admin\Action;

use App\Services\Mail\InvoiceEmailSenderInterface;
use App\Services\Sentry\ReportingInterface;
use Sentry;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\InvoicingPlugin\Doctrine\ORM\InvoiceRepositoryInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;

final class ResendInvoiceAction
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly InvoiceEmailSenderInterface $invoiceEmailSender,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly FlashBagInterface $flashBag,
        private readonly ReportingInterface $reporting
    ) {
    }

    public function __invoke(string $id): Response
    {
        /** @var InvoiceInterface|null $invoice */
        $invoice = $this->invoiceRepository->find($id);
        Assert::notNull($invoice);

        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneBy(['number' => $invoice->orderNumber()]);

        try {
            $this->invoiceEmailSender->sendInvoiceEmail($order, $invoice);
        } catch (\Exception $exception) {
            $this->flashBag->add(
                'error',
                $exception->getMessage()
            );

            $this->reporting->reportIssue(
                $exception,
                Sentry\Severity::error(),
                ['order' => $order]
            );

            return new RedirectResponse(
                $this->urlGenerator->generate('sylius_admin_order_show', ['id' => $order->getId()])
            );
        }

        $this->flashBag->add(
            'success',
            'sylius_invoicing_plugin.invoice_resent_successfully'
        );

        return new RedirectResponse(
            $this->urlGenerator->generate('sylius_admin_order_show', ['id' => $order->getId()])
        );
    }
}
