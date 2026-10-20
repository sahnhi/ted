<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Repository\InvoicePluginRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\InvoicingPlugin\Entity\BillingDataInterface;
use Sylius\InvoicingPlugin\Entity\Invoice as BaseInvoice;
use Sylius\InvoicingPlugin\Entity\InvoiceShopBillingDataInterface;

/**
 * @final
 */
#[ORM\Table(name: 'sylius_invoicing_plugin_invoice')]
#[ORM\Entity(repositoryClass: InvoicePluginRepository::class)]
class Invoice extends BaseInvoice implements InvoiceInterface
{
    public function __construct(
        string $id,
        string $number,
        string $orderNumber,
        \DateTimeInterface $issuedAt,
        BillingDataInterface $billingData,
        string $currencyCode,
        string $localeCode,
        int $total,
        Collection $lineItems,
        Collection $taxItems,
        ChannelInterface $channel,
        InvoiceShopBillingDataInterface $shopBillingData,
        #[ORM\Column(name: 'type', type: 'string', length: 255, nullable: true)]
        private ?string $type = null
    ) {
        parent::__construct(
            $id,
            $number,
            $orderNumber,
            $issuedAt,
            $billingData,
            $currencyCode,
            $localeCode,
            $total,
            $lineItems,
            $taxItems,
            $channel,
            $shopBillingData,
        );
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }
}
