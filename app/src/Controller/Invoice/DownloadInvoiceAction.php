<?php

namespace App\Controller\Invoice;

use App\Doctrine\Entity\Sylius\InvoiceInterface;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Services\Admin\InvoiceService;
use App\Services\Channel\ChannelCode;
use App\Services\Sentry\ReportingInterface;
use Sentry;
use Sylius\InvoicingPlugin\Doctrine\ORM\InvoiceRepositoryInterface;
use Sylius\InvoicingPlugin\Security\Voter\InvoiceVoter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Webmozart\Assert\Assert;

final class DownloadInvoiceAction
{
    public const ACCESS = [
        ChannelCode::CHANNEL_CARREFOUR,
    ];

    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly InvoiceService $invoiceService,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly FlashBagInterface $flashBag,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ReportingInterface $reporting
    ) {
    }

    public function __invoke(string $id): Response
    {
        /** @var InvoiceInterface|null $invoice */
        $invoice = $this->invoiceRepository->find($id);
        Assert::notNull($invoice);

        $access = $invoice->channel()->getCode();

        if (!in_array($access, self::ACCESS, true)
            && !$this->authorizationChecker->isGranted(InvoiceVoter::ACCESS, $invoice)) {
            throw new AccessDeniedHttpException();
        }

        try {
            $file = $this->invoiceService->getFileFromInvoicePlugin($invoice);
        } catch (\Exception $exception) {
            $order = $this->orderRepository->findOneByNumber($invoice->orderNumber());
            Assert::notNull($order);

            $this->flashBag->add(
                'error',
                $exception->getMessage()
            );

            $this->reporting->reportIssue(
                $exception,
                Sentry\Severity::error(),
                [
                    'order' => $order->getNumber(),
                    'invoice' => $invoice->number(),
                ]
            );

            return new RedirectResponse(
                $this->urlGenerator->generate('sylius_admin_order_show', ['id' => $order->getId()])
            );
        }

        $response = new Response($file->getContent(), Response::HTTP_OK, ['Content-Type' => 'application/pdf']);
        $response->headers->add([
            'Content-Disposition' => $response->headers->makeDisposition('attachment', basename((string) $file->getName())),
        ]);

        return $response;
    }
}
