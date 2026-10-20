<?php

namespace App\Api\Odoo\Controller;

use App\Api\Odoo\Service\Invoice as InvoiceService;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Invoice extends AbstractOdooController
{
    private readonly SerializerInterface $serializer;

    public function __construct(
        string $apiKey,
        private readonly InvoiceService $invoiceService,
        LoggerInterface $odooLogger
    ) {
        $this->serializer = SerializerBuilder::create()->build();

        parent::__construct($odooLogger, $apiKey);
    }

    public function __invoke(Request $request): Response
    {
        $this->logRequest($request);

        try {
            $this->authenticate($request);

            /** @var \App\Api\Odoo\DTO\Invoice $invoice */
            $invoice = $this->serializer->deserialize(
                $request->getContent(),
                \App\Api\Odoo\DTO\Invoice::class,
                'json'
            );

            $this->invoiceService->create($invoice);
        } catch (\Exception $e) {
            $this->logError($request, $e->getCode(), $e->getMessage());

            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new Response('Invoice created', Response::HTTP_CREATED);
    }
}
