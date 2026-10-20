<?php

declare(strict_types=1);

namespace App\Services\Quality\Export;

use App\Doctrine\Entity\Quality;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Loader\LoaderResourcesByGridViewInterface;
use App\Doctrine\Repository\Sylius\ShipmentRepository;
use App\Services\Admin\Export\ExportCsv;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridView;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class QualityCsvGeneratorGenerator implements QualityCsvGeneratorInterface
{
    private const HEADERS = [
        'Numéro de commande',
        'Nom du client',
        'Date',
        'Référence du produit avec taille et couleur',
        'Numéro de lot',
        'Date de livraison',
        'Service de montage',
        'Temps écoulé (entre la date de livraison et création de la fiche qualité)',
        'Nom du problème',
        'Transporteur',
        'Titre fiche qualité',
        'Etat fiche qualité',
        'Raison du problème',
        'Date de création',
    ];

    public function __construct(
        private readonly ExportCsv $exportCsv,
        private readonly TranslatorInterface $translator,
        private readonly LoaderResourcesByGridViewInterface $resourceLoader,
        private readonly ShipmentRepository $shipmentRepository
    ) {
    }

    public function generateCsvFromResourceGridView(ResourceGridView $resources, ?SessionInterface $session = null): StreamedResponse
    {
        $data = [];
        if ($resources->getData()->getNbResults() > QualityCsvGeneratorInterface::MAX_ITEM_EXPORT) {
            $alertMessage = $this->translator->trans('app.ui.export_limit');
            $this->addFlashBag($alertMessage, $session);
            $maxPages = QualityCsvGeneratorInterface::MAX_ITEM_EXPORT / $resources->getData()->getMaxPerPage();
            $data[] = [$alertMessage];
        } else {
            $maxPages = $resources->getData()->getNbPages();
        }

        $data[] = self::HEADERS;

        for ($page = 0; $page < $maxPages; ++$page) {
            $qualities = $this->resourceLoader->load($resources, $page);
            foreach ($qualities as $quality) {
                $data[] = $this->createCsvLine($quality);
            }
        }

        return $this->exportCsv->generateCsv($data);
    }

    private function addFlashBag($alertMessage, ?SessionInterface $session = null): void
    {
        $session?->getFlashBag()->add('error', $alertMessage);
    }

    private function createCsvLine(Quality $quality): array
    {
        $orderItem = $quality->getOrderItemLogistic()->getOrderItem();
        $order = $orderItem->getOrder();
        $local = $order->getLocaleCode();
        $variant = $orderItem->getVariant();
        $productName = $variant->getProduct()->getTranslation($local)->getName();
        $shipment = $this->shipmentRepository->getByOrder($order->getId());
        $optionNames = [];

        foreach ($variant->getOptionValues() as $option) {
            $optionNames[] = $option->getTranslation($local)->getValue();
        }
        $optionsDetails = implode('-', $optionNames);

        return [
            $order->getNumber(),
            $order->getCustomer() ? $order->getCustomer()->getFullName() : null,
            $order->getCreatedAt()?->format('Y-m-d') ?? 'N/A',
            sprintf('%s (%s)', $productName, $optionsDetails),
            $quality->getOrderItemLogistic()?->getBatchNumber() ?? 'N/A',
            $shipment?->getShippingDate()?->format('Y-m-d H:i:s'),
            $this->getBuildService($order),
            $shipment && $shipment->getShippingDate() ?
                date_diff($quality->getCreatedAt(), $shipment->getShippingDate())->format('%a') :
                null,
            $quality->getReason()?->getName() ?? 'N/A',
            $shipment?->getMethod()?->getTranslation($local)->getName() ?? 'N/A',
            $quality->getTitle(),
            $quality->getStatus(),
            $quality->getSavComment(),
            $quality->getCreatedAt()->format('Y-m-d'),
        ];
    }

    private function getBuildService(OrderInterface $order): string
    {
        $buildServices = [];
        foreach ($order->getItems() as $item) {
            $variant = $item->getVariant();
            if ($variant->getCode() && Shipment::SUFFIX_BUILD_SERVICE === substr((string) $variant->getCode(), 0, 4)) {
                $buildServices[] = $variant->getCode();
            }
        }

        return implode(' - ', $buildServices);
    }
}
