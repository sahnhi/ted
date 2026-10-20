<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Shop\Cart;

use App\Tests\Functional\WebTestCase;

class SummaryActionTest extends WebTestCase
{
    public function testCartSummaryDisplaysQuotationForm(): void
    {
        $response = $this->get('/fr_FR/cart/');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('id="quotation-modal"', $response->getResponseContent());
        self::assertStringContainsString('id="quotation_email"', $response->getResponseContent());
    }
}