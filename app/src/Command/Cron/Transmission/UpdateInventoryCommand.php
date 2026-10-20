<?php

namespace App\Command\Cron\Transmission;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepository;
use App\Enum\FtpConfigurationName;
use App\Exception\FtpConfigurationNotFoundException;
use App\Registry\FtpConfigurationRegistryInterface;
use App\Services\Admin\Export\ExportCsv;
use App\Services\Channel\ChannelCode;
use Doctrine\ORM\EntityManagerInterface;
use phpseclib3\Net\SFTP;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdateInventoryCommand extends Command
{
    public const INVENTORY_REMOTE_CSV_DIR = '/STOCKS/';
    public const REMOTE_CSV_DIR_SPIDY = ['/Attend/', '/Detail/', '/Mouvem/', '/Portef/'];

    protected static $defaultName = 'app:cron:transmission:inventory-update';

    private readonly string $privateFolder;

    public function __construct(
        private readonly ExportCsv $exportCsv,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductVariantRepository $productVariantRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly FtpConfigurationRegistryInterface $ftpConfigurationRegistry,
        string $projectDir
    ) {
        parent::__construct();
        $this->privateFolder = $projectDir . '/src/Resources/private/';
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Transmission Inventory Update');
    }

    /**
     * @throws FtpConfigurationNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inventoryCsvLocaleFilename = '../var/inventory_csv/inventory.csv';
        $inventoryCsvRemoteName = 'STOCKS_' . date('ymd');
        $inventoryCsvRemoteFilename = '';

        $ftpConfiguration = $this->ftpConfigurationRegistry->getFtpConfigurationByName(FtpConfigurationName::SPIDY);
        $ftp_server = $ftpConfiguration->host;
        $ftp_name = $ftpConfiguration->user;
        $ftp_pass = $ftpConfiguration->password;
        $sftp = new SFTP($ftp_server, 22);
        $sftp->login($ftp_name, $ftp_pass);

        $this->sendFilesToLokad($sftp);
        $files = $sftp->nlist(self::INVENTORY_REMOTE_CSV_DIR);

        foreach ($files as $file) {
            if (preg_match("#$inventoryCsvRemoteName(.*?)\.csv#is", (string) $file)) {
                $inventoryCsvRemoteFilename = self::INVENTORY_REMOTE_CSV_DIR . $file;
            }
        }

        if (!empty($inventoryCsvRemoteFilename)) {
            $dirname = dirname($inventoryCsvLocaleFilename);
            if (!is_dir($dirname)) {
                mkdir($dirname, 0755, true);
            }

            $sftp->get($inventoryCsvRemoteFilename, $inventoryCsvLocaleFilename);
        }

        if (file_exists($inventoryCsvLocaleFilename)) {
            $inventoryCsvDatas = $this->exportCsv->readCsv($inventoryCsvLocaleFilename, ';');

            $row = 0;
            $parseRows = [];
            foreach ($inventoryCsvDatas as $data) {
                ++$row;

                $codename = $data[3];
                $stock = $data[8];

                if (!str_starts_with((string) $codename, 'TED')) {
                    continue;
                }

                if (str_starts_with((string) $codename, 'TEDM')) {
                    $codename = rtrim(substr((string) $codename, 0, 4), 'M') . substr((string) $codename, 4);
                }

                if (str_ends_with((string) $codename, 'INT')) {
                    $codename = substr((string) $codename, 0, -3);
                }

                if (str_ends_with((string) $codename, 'FR')) {
                    $codename = substr((string) $codename, 0, -2);
                }

                $codename = preg_replace('/\s*/m', '', (string) $codename);

                if (isset($parseRows[$codename])) {
                    $parseRows[$codename]['stock'] += (int) round($stock, 0);
                } else {
                    $parseRows[$codename] = ['codename' => $codename, 'stock' => (int) round($stock, 0)];
                }
            }

            $productCodes = [];
            foreach ($parseRows as $row) {
                $productCodes[] = $row['codename'];
            }

            $notInProductVariants = $this->productVariantRepository->getProductVariantNotIn($productCodes);

            $notInArray = [];
            foreach ($notInProductVariants as $productVariant) {
                $notInArray[$productVariant->getCode()] = $productVariant;
            }

            $productVariants = $this->productVariantRepository->findAll();

            $inventoryProductVariants = [];
            foreach ($productVariants as $productVariant) {
                foreach ($parseRows as $row) {
                    $bedframeFootCode = [];

                    if (str_contains((string) $productVariant->getCode(), '_')) {
                        $bedframeFootCode = ['bedframeCode' => explode('_', (string) $productVariant->getCode())[0], 'footCode' => explode('_', (string) $productVariant->getCode())[1]];

                        if (empty($inventoryProductVariants[$bedframeFootCode['bedframeCode'] . '_' . $bedframeFootCode['footCode']])) {
                            if (!empty($parseRows[$bedframeFootCode['bedframeCode']]) and !empty($parseRows[$bedframeFootCode['footCode']])) {
                                $row['codename'] = $bedframeFootCode['bedframeCode'] . '_' . $bedframeFootCode['footCode'];
                                $bedframeStock = $parseRows[$bedframeFootCode['bedframeCode']]['stock'];
                                $row['stock'] = $bedframeStock;
                            }
                        }
                    }

                    if ($productVariant->getCode() === $row['codename']) {
                        $productVariant->setOnHand($row['stock']);
                        $productVariant->setTracked(true);

                        $parseProductVariants = [['code' => $productVariant->getCode(), 'onHand' => $productVariant->getOnHand(), 'onHold' => $productVariant->getOnHold()]];
                        foreach ($bedframeFootCode as $value) {
                            if (!empty($parseRows[$value])) {
                                $parseProductVariants[] = ['code' => $parseRows[$value]['codename'], 'onHand' => $parseRows[$value]['stock'], 'onHold' => $productVariant->getOnHold()];
                            }
                        }

                        unset($notInArray[$productVariant->getCode()]);

                        foreach ($parseProductVariants as $parseProductVariant) {
                            if (!empty($inventoryProductVariants[$parseProductVariant['code']])) {
                                $inventoryProductVariants[$parseProductVariant['code']]['onHand'] = $inventoryProductVariants[$parseProductVariant['code']]['onHand'] + $parseProductVariant['onHand'];
                                $inventoryProductVariants[$parseProductVariant['code']]['onHold'] = $inventoryProductVariants[$parseProductVariant['code']]['onHold'] + $parseProductVariant['onHold'];
                            } else {
                                $inventoryProductVariants[$parseProductVariant['code']] = ['code' => $parseProductVariant['code'], 'onHand' => $parseProductVariant['onHand'], 'onHold' => $parseProductVariant['onHold']];
                            }
                        }
                    } else {
                        if (isset($notInArray[$productVariant->getCode()])) {
                            $productVariant->setOnHand(0);

                            $inventoryProductVariants[$productVariant->getCode()] = ['code' => $productVariant->getCode(), 'onHand' => $productVariant->getOnHand(), 'onHold' => $productVariant->getOnHold()];
                        }
                    }
                }
            }

            $this->entityManager->flush();
        }

        return 0;
    }

    private function sendFilesToLokad(SFTP $sftpSpidy): void
    {
        $sftpServer = getenv('LOKAD_SERVER');
        $sftpServerUsername = getenv('LOKAD_USERNAME');
        $sftpServerPassword = getenv('LOKAD_PASSWORD');

        $sftp = new SFTP($sftpServer, 22, 30);
        $sftp->login($sftpServerUsername, $sftpServerPassword);

        $lokadLocaleFolder = '../var/inventory_csv/lokad/';

        $env = getenv('APP_ENV');
        $folder = 'prod' === $env ? '/InputDaily/' : '/Test-Tediber/';

        $this->sendOutOfStockFile($sftp, $folder);

        $date = date('Y-m-d');
        foreach (self::REMOTE_CSV_DIR_SPIDY as $dir) {
            $files = $sftpSpidy->nlist($dir);
            $matches = preg_grep("#(.*?)_$date(.*?)\.csv#is", $files, 0);

            foreach ($matches as $file) {
                $dirname = dirname($lokadLocaleFolder . $file);
                if (!is_dir($dirname)) {
                    mkdir($dirname, 0755, true);
                }

                $sftpSpidy->get($dir . $file, $lokadLocaleFolder . $file);
                if (in_array($dir, [self::REMOTE_CSV_DIR_SPIDY[2], self::REMOTE_CSV_DIR_SPIDY[3]])) {
                    $openFile = fopen($lokadLocaleFolder . $file, 'r');
                    $tmpFile = fopen($lokadLocaleFolder . 'tmp_' . $file, 'w');
                    while (false !== ($data = fgetcsv($openFile, 0, ';'))) {
                        if ($dir === self::REMOTE_CSV_DIR_SPIDY[2]) {
                            unset($data[18]);
                        } elseif ($dir === self::REMOTE_CSV_DIR_SPIDY[3]) {
                            unset($data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[12], $data[13]);
                        }
                        fputcsv($tmpFile, $data, ';');
                    }
                    rename($lokadLocaleFolder . 'tmp_' . $file, $lokadLocaleFolder . $file);
                }
            }
        }
        $yesterday = new \DateTime();

        $interval = new \DateInterval('PT24H');
        $yesterday->sub($interval);

        $updatedOrders = $this->orderRepository->getOrdersForLokad($yesterday);

        $ordersUpdated = $this->formatOrdersCSV($updatedOrders);
        $ordersItemUpdated = $this->formatOrdersItemCSV($updatedOrders);

        if (!empty($updatedOrders)) {
            $this->exportCsv->createCsv($lokadLocaleFolder . '/updated_orders' . date('ymd') . '.csv', $ordersUpdated);
            $this->exportCsv->createCsv($lokadLocaleFolder . '/updated_orders_item' . date('ymd') . '.csv', $ordersItemUpdated);
        }

        $files = array_slice(scandir($lokadLocaleFolder), 2);
        foreach ($files as $file) {
            $sftp->put($folder . $file, $lokadLocaleFolder . $file, SFTP::SOURCE_LOCAL_FILE);
            unlink($lokadLocaleFolder . $file);
        }
    }

    /**
     * @param Order[] $result
     */
    private function formatOrdersCSV(array $result): array
    {
        $orders[] = [
            'id',
            'customer_id',
            'name',
            'number',
            'checkout_completed_at',
            'state',
            'payment_state',
            'shipping_state',
            'total',
            'postcode',
            'country_code',
            'shipping_code',
            'store_name',
            'start_delivery_slot',
            'end_delivery_slot',
        ];
        foreach ($result as $row) {
            /** @var Shipment $firstShipment */
            $firstShipment = !empty($row->getShipments()[0]) ? $row->getShipments()[0] : null;

            if (ChannelCode::CHANNEL_RETURN === $row->getChannel()->getCode()
                || (null !== $firstShipment
                    && (null === $firstShipment->getStartDeliverySlot()
                        || null === $firstShipment->getEndDeliverySlot()
                    ))
            ) {
                continue;
            }
            $order = [];
            $order[] = $row->getId();
            $order[] = $row->getCustomer()->getId();
            $order[] = $row->getChannel()->getName();
            $order[] = $row->getNumber();
            $order[] = $row->getCheckoutCompletedAt()->format('Y-m-d H:i:s');
            $order[] = $row->getState();
            $order[] = $row->getPaymentState();
            $order[] = $row->getShippingState();
            $order[] = $row->getTotal();
            $order[] = $row->getShippingAddress()->getPostcode();
            $order[] = $row->getShippingAddress()->getCountryCode();
            $order[] = !empty($row->getShipments()[0]) ? $row->getShipments()[0]->getMethod()->getCode() : '';
            if (!empty($row->getOrderStore()) && !empty($row->getOrderStore()->getStore()) && !empty($row->getOrderStore()->getStore()->getName())) {
                $order[] = $row->getOrderStore()->getStore()->getName();
            } else {
                $order[] = '';
            }
            $order[] = null !== $firstShipment ? $firstShipment->getStartDeliverySlot()->format('Y-m-d H:i:s') : '';
            $order[] = null !== $firstShipment ? $firstShipment->getEndDeliverySlot()->format('Y-m-d H:i:s') : '';
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * @param Order[] $result
     */
    private function formatOrdersItemCSV(array $result): array
    {
        $ordersItem[] = [
            'order_id',
            'quantity',
            'total',
            'product_variant_code',
        ];
        foreach ($result as $row) {
            foreach ($row->getItems() as $item) {
                /** @var OrderItem $item */
                $orderItem = [];
                $orderItem[] = $row->getId();
                $orderItem[] = $item->getQuantity();
                $orderItem[] = $item->getTotal();
                $orderItem[] = $item->getVariant()->getCode();
                $ordersItem[] = $orderItem;
            }
        }

        return $ordersItem;
    }

    private function sendOutOfStockFile(SFTP $sftp, string $folder): void
    {
        $variants = $this->productVariantRepository->findOutOfStockVariants();
        $formattedVariants = $this->formatVariants($variants);

        $path = $this->privateFolder . 'lokad/out-of-stock/';
        $filename = 'shipping_date_' . date('ymd') . '.csv';
        $this->exportCsv->createCsv($path . $filename, $formattedVariants);

        $sftp->put($folder . $filename, $path . $filename, SFTP::SOURCE_LOCAL_FILE);
        unlink($path . $filename);
    }

    /**
     * @param ProductVariant[] $variants
     *
     * @return string[][]
     */
    private function formatVariants(array $variants): array
    {
        $formatted = [['code', 'enabled', 'shipping_date']];

        foreach ($variants as $variant) {
            /** @var ProductVariant $variant */
            $shippingDate = 'NULL';

            if (!$variant->getShippingStock()->isEmpty() && null !== ($date = $variant->getShippingStock()->first()->getShippingDate())) {
                if ($date < new \DateTime()) {
                    if ($variant->isEnabled()) {
                        continue;
                    }
                } else {
                    $shippingDate = $date->format('Y-m-d H:i:s');
                }
            }

            $formatted[] = [
                $variant->getCode(),
                (int) $variant->isEnabled(),
                $shippingDate,
            ];
        }

        return $formatted;
    }
}
