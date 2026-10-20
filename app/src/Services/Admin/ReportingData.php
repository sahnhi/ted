<?php

namespace App\Services\Admin;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\StoreRepository;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Model\ProductReferenceInterface;
use Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\PaymentInterface;

class ReportingData
{
    public const REPORTING_SIMPLE_HEADER = ['Numéro de commande', 'Date', 'Site (magasin)', 'Prénom', 'Nom', 'E-mail', 'Sur Devis / Sur place', 'CA TTC', 'CA HT', 'Vendeur', 'Mode de paiement', 'Livraison / Emporté (si magasin ou non)', 'Total Com Vendeur Brut'];
    public const DEFAULT_PRODUCT_COMMISSION_AMOUNT = 300;
    public const PRODUCT_WITHOUT_COMMISSION = [ProductReferenceInterface::REFERENCE_BUILD];

    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly StoreRepository $storeRepository,
        private readonly EntityRepository $commissionRepository
    ) {
    }

    public function getShopData(Channel $channel): array
    {
        $reportingDataPartners = $this->orderRepository->getReportingData($channel, 'reporting_partners_total');

        $data = [];
        $data['0_header_date'] = ['', ''];
        $data['1_header_total'] = [];
        $data['2_data_total'] = [
            'totals' => ['Total', 'CA Total HT'],
            'orders' => ['', 'Commandes #'],
            'commissions' => ['', 'Commissions'],
        ];

        /* BEGIN:HEADERS DATE */
        foreach ($reportingDataPartners as $reportingDataPartner) {
            $data['0_header_date'] = array_merge($data['0_header_date'], [$reportingDataPartner['month'] . '-' . $reportingDataPartner['year']]);
        }
        /* ENDED:HEADERS DATE */

        /* BEGIN:DATA TOTAL */
        foreach ($data['2_data_total'] as $key_row => $d) {
            if ('totals' === $key_row) {
                foreach ($reportingDataPartners as $reportingDataPartner) {
                    $data['2_data_total']['totals'] = array_merge($data['2_data_total']['totals'], [
                        $reportingDataPartner['caHT'] . '€',
                    ]);
                }
            }

            if ('orders' === $key_row) {
                foreach ($reportingDataPartners as $reportingDataPartner) {
                    $data['2_data_total']['orders'] = array_merge($data['2_data_total']['orders'], [
                        $reportingDataPartner['nbrOrder'],
                    ]);
                }
            }

            if ('commissions' === $key_row) {
                foreach ($reportingDataPartners as $reportingDataPartner) {
                    $data['2_data_total']['commissions'] = array_merge($data['2_data_total']['commissions'], [
                        $reportingDataPartner['commissionPartner'],
                    ]);
                }
            }
        }
        /* ENDED:DATA TOTAL */

        foreach ($this->storeRepository->findByChannel($channel) as $store) {
            $reportingDataPartners = $this->orderRepository->getReportingData($channel, 'reporting_partners');

            $data['3_data_store_' . $store->getId()] = [
                'totals' => [$store->getName(), 'CA Total HT'],
                'orders' => ['', 'Commandes #'],
                'commissions' => ['', 'Commissions'],
            ];

            foreach ($data['3_data_store_' . $store->getId()] as $key_row => $d) {
                if ('totals' === $key_row) {
                    foreach ($reportingDataPartners as $reportingDataPartner) {
                        if ($reportingDataPartner['storeName'] === $store->getName()) {
                            $indexDate = array_search($reportingDataPartner['month'] . '-' . $reportingDataPartner['year'], $data['0_header_date']);

                            $i = 2;
                            while ($i <= $indexDate) {
                                if (!isset($data['3_data_store_' . $store->getId()]['totals'][$i])) {
                                    $data['3_data_store_' . $store->getId()]['totals'][$i] = '';
                                }

                                ++$i;
                            }

                            $data['3_data_store_' . $store->getId()]['totals'][$indexDate] =
                                $reportingDataPartner['caHT'] . '€';
                        }
                    }
                }

                if ('orders' === $key_row) {
                    foreach ($reportingDataPartners as $reportingDataPartner) {
                        if ($reportingDataPartner['storeName'] === $store->getName()) {
                            $indexDate = array_search($reportingDataPartner['month'] . '-' . $reportingDataPartner['year'], $data['0_header_date']);

                            $i = 2;
                            while ($i <= $indexDate) {
                                if (!isset($data['3_data_store_' . $store->getId()]['orders'][$i])) {
                                    $data['3_data_store_' . $store->getId()]['orders'][$i] = '';
                                }

                                ++$i;
                            }

                            $data['3_data_store_' . $store->getId()]['orders'][$indexDate] =
                                $reportingDataPartner['nbrOrder'];
                        }
                    }
                }

                if ('commissions' === $key_row) {
                    foreach ($reportingDataPartners as $reportingDataPartner) {
                        if ($reportingDataPartner['storeName'] === $store->getName()) {
                            $indexDate = array_search($reportingDataPartner['month'] . '-' . $reportingDataPartner['year'], $data['0_header_date']);

                            $i = 2;
                            while ($i <= $indexDate) {
                                if (!isset($data['3_data_store_' . $store->getId()]['commissions'][$i])) {
                                    $data['3_data_store_' . $store->getId()]['commissions'][$i] = '';
                                }

                                ++$i;
                            }

                            $data['3_data_store_' . $store->getId()]['commissions'][$indexDate] =
                                $reportingDataPartner['commissionPartner'];
                        }
                    }
                }
            }
        }

        return $data;
    }

    public function getSellerData(Channel $channel): array
    {
        $reportingDataSellers = $this->orderRepository->getReportingData($channel, 'reporting_sellers');

        $data = [];
        $data['0_header'] = ['En € Bruts'];
        $data['1_data'] = [];

        /* BEGIN:HEADERS DATE */
        foreach ($reportingDataSellers as $reportingDataSeller) {
            if (!in_array($reportingDataSeller['month'] . '-' . $reportingDataSeller['year'], $data['0_header'])) {
                $data['0_header'] = array_merge($data['0_header'], [$reportingDataSeller['month'] . '-' . $reportingDataSeller['year']]);
            }
        }
        /* ENDED:HEADERS DATE */

        /* BEGIN:DATA */
        foreach ($reportingDataSellers as $reportingDataSeller) {
            $sellerName = $reportingDataSeller['sellerName'];

            if (!isset($data['1_data'][$sellerName])) {
                $data['1_data'][$sellerName] = [$sellerName];

                foreach ($data['0_header'] as $i => $header) {
                    if (0 === $i) { // ['En € Bruts']; not needed
                        continue;
                    }

                    $data['1_data'][$sellerName][$header] = '';
                }
            }

            $data['1_data'][$sellerName][$reportingDataSeller['month'] . '-' . $reportingDataSeller['year']] = $reportingDataSeller['commissionSeller'] . '€';
        }
        /* ENDED:DATA */

        return $data;
    }

    public function getAllData(Channel $channel): array
    {
        $reportingDatas = $this->orderRepository->getReportingData($channel);

        $data[] = [];
        $data['0_header'] = ['Numéro de commande', 'Date', 'Site (magasin)', 'Prénom', 'Nom', 'E-mail', 'Sur Devis / Sur place', 'CA TTC', 'CA HT', 'Vendeur', '# Matelas', '# Sommier', '# Ted', '# Autres', '# dont offert', '# dont lingot', 'Mode de paiement', 'Livraison / Emporté (si magasin ou non)', 'Commission Partenaire', 'Total Com Vendeur Brut'];
        $data['1_data'] = [];

        foreach ($reportingDatas as $key => $reportingData) {
            $data['1_data'][$key] = [$reportingData['number'], $reportingData['date'], $reportingData['storeName'], $reportingData['firstName'], $reportingData['lastName'], $reportingData['email'], $reportingData['quoteSpot'], $reportingData['caTTC'] . '€', $reportingData['caHT'] . '€', $reportingData['sellerName'], $reportingData['matelas'], $reportingData['sommier'], $reportingData['TED'], $reportingData['autres'], $reportingData['autresOfferts'], $reportingData['lingot'], $reportingData['paymentMethod'], $reportingData['shippingMethod'], $reportingData['commissionPartner'] . '€', $reportingData['commissionSeller'] . '€'];
        }

        return $data;
    }

    public function getSimpleData(Channel $channel): array
    {
        $orders = $this->orderRepository->getSimpleData($channel);
        $commissions = $this->commissionRepository->findAll();
        $commissionsArray = [];

        foreach ($commissions as $commission) {
            $commissionsArray[$commission->getProduct()->getCode()] = $commission->getCommission();
        }

        $data[] = [];
        $data['0_header'] = self::REPORTING_SIMPLE_HEADER;
        $data['1_data'] = [];

        /**
         * @var Order $order
         */
        foreach ($orders as $key => $order) {
            $firstPayment = null;
            foreach ($order->getPayments() as $payment) {
                if (in_array($payment->getState(), [PaymentInterface::STATE_COMPLETED, PaymentInterface::STATE_REFUNDED])) {
                    $firstPayment = $payment;
                }
            }

            $commissionAmount = 0;
            foreach ($order->getItems() as $item) {
                if (isset($commissionsArray[$item->getProduct()->getCode()])) {
                    $commissionAmount += $commissionsArray[$item->getProduct()->getCode()] * $item->getQuantity() / 100;
                } elseif (!in_array($item->getProduct()->getCode(), array_merge(
                    self::PRODUCT_WITHOUT_COMMISSION,
                    ProductReferenceInterface::REPOSSESS_REFS
                ))) {
                    $commissionAmount += self::DEFAULT_PRODUCT_COMMISSION_AMOUNT * $item->getQuantity() / 100;
                }
            }

            if ($order->getShipments()->isEmpty()) {
                $shipment = 'NA';
            } else {
                $shipment = 'MAGASIN' === $order->getShipments()->last()->getMethod()->getCode() ? 'Emporté' : 'Livraison';
            }

            if (null === $firstPayment) {
                $payment = '';
            } else {
                $payment = $firstPayment->getMethod()->getCode();
            }

            $data['1_data'][$key] = [
                $order->getNumber(),
                $order->getCheckoutCompletedAt()->add(new \DateInterval('PT2H'))->format('Y-m-d H:i:s'),
                $order->getOrderStore()->getStore()->getName(),
                $order->getCustomer()->getFirstName(),
                $order->getCustomer()->getLastName(),
                $order->getCustomer()->getEmail(),
                1 === $order->getChannel()->getId() ? 'Sur devis' : 'Sur place',
                round($order->getTotal() / 100, 2) . '€',
                round($order->getTotal() / 1.2 / 100, 2) . '€',
                $order->getOrderStore()->getSeller()->getName(),
                $payment,
                $shipment,
                round($commissionAmount, 2) . '€',
            ];
        }

        return $data;
    }
}
