<?php

declare(strict_types=1);

namespace App\Generator\Renderer\Model;

final class InvoiceProductList
{
    public function __construct(
        /** @var InvoiceProduct[] */
        private array $invoiceProducts
    ) {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function addList(InvoiceProductList $invoiceProductList): self
    {
        $invoiceProducts = $this->invoiceProducts;
        $invoiceProducts = array_merge($invoiceProducts, $invoiceProductList->getInvoiceProducts());

        return new self($invoiceProducts);
    }

    public function addAndAggregateByCode(InvoiceProduct $invoiceProductToAdd): self
    {
        $invoiceProducts = $this->invoiceProducts;
        $quantityToAdd = $invoiceProductToAdd->getQuantity();

        foreach ($invoiceProducts as $invoiceProduct) {
            if ($invoiceProductToAdd->getCode() === $invoiceProduct->getCode()) {
                $invoiceProduct->addQuantity($quantityToAdd);

                return new self($invoiceProducts);
            }
        }

        $invoiceProducts[] = $invoiceProductToAdd;

        return new self($invoiceProducts);
    }

    /**
     * method needed for twig rendering.
     *
     * @return InvoiceProduct[]
     */
    public function getInvoiceProducts(): array
    {
        return $this->invoiceProducts;
    }

    public function getNbProducts(): int
    {
        $total = 0;

        foreach ($this->invoiceProducts as $invoiceProduct) {
            $total += $invoiceProduct->getQuantity();
        }

        return $total;
    }
}
