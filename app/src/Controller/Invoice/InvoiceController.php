<?php

declare(strict_types=1);

namespace App\Controller\Invoice;

use App\Identifier\OrderNumber;
use App\Services\Admin\InvoiceService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class InvoiceController
{
    public function __construct(
        private readonly string $apiKey,
        private readonly InvoiceService $invoiceService
    ) {
    }

    public function download(Request $request, string $orderNumber): JsonResponse
    {
        if ($this->apiKey !== $request->headers->get('api-key')) {
            throw new AccessDeniedHttpException();
        }

        $downloadInvoiceViewModel = $this->invoiceService->getFileFromOrderNumber(OrderNumber::fromSpidy($orderNumber));

        return new JsonResponse(
            $downloadInvoiceViewModel,
            Response::HTTP_OK
        );
    }
}
