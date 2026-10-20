<?php

namespace App\Services\Transmission\Entrepot\Spidy;

use App\Api\Shipup\ShipupApiInterface;
use App\Component\Core\OrderShippingTransitions;
use App\Context\CliBasedChannelContext;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Enum\FtpConfigurationName;
use App\Exception\FtpConfigurationNotFoundException;
use App\Registry\FtpConfigurationRegistryInterface;
use App\Services\Admin\Export\ExportCsv;
use App\Services\Filesystem\GoogleStorageTransmissionFilesystem;
use App\Services\Sentry\ReportingInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use SM\Factory\Factory;
use SM\SMException;
use Sylius\Component\Shipping\ShipmentTransitions;

final class Deliver extends AbstractSpidy
{
    public function __construct(
        string $projectDir,
        FtpConfigurationRegistryInterface $ftpConfigurationRegistry,
        ExportCsv $exportCsv,
        private readonly EntityManagerInterface $orderManager,
        private readonly OrderRepository $orderRepository,
        private readonly Factory $smFactory,
        GoogleStorageTransmissionFilesystem $googleStorageTransmissionFilesystem,
        private readonly ShipupApiInterface $shipupApi,
        ReportingInterface $reporting,
        private readonly CliBasedChannelContext $cliBasedChannelContext
    ) {
        parent::__construct(
            $projectDir,
            $ftpConfigurationRegistry,
            $exportCsv,
            $googleStorageTransmissionFilesystem,
            $reporting
        );
    }

    /**
     * @throws ORMException
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws SMException
     * @throws FtpConfigurationNotFoundException
     */
    public function updateDelivered(): void
    {
        $this->getEdis(FtpConfigurationName::SPIDY, self::SPIDY_PREFIX, self::SPIDY_ORDER_NUMBER);
        $this->getEdis(FtpConfigurationName::VIR, self::VIR_PREFIX, self::VIR_ORDER_NUMBER);

        $this->orderManager->flush();
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws SMException
     * @throws FtpConfigurationNotFoundException
     */
    public function getEdis(FtpConfigurationName $ftpName, string $prefix, int $numberCol, bool $sftp = true): void
    {
        [$resource, $ftpConfiguration] = $this->connect($ftpName, $sftp);
        $folder = AbstractSpidy::DELIVERED_FOLDER;

        $monthFolderPath = sprintf('/%s%s', \date('Y/'), \date('F'));
        $dirPath = sprintf('%s%s/', $ftpConfiguration->ftpConfigurationName->value, $monthFolderPath);
        $localPath = $this->privateFolder . $folder . $dirPath;

        $this->mkdir($localPath);

        $ftpDir = $ftpConfiguration->deliveredPath;
        $files = $sftp ? $resource->nlist($ftpDir) : ftp_nlist($resource, $ftpDir);

        foreach ($files as $file) {
            if (!$sftp) {
                $split = explode('/', (string) $file);
                $file = end($split);
            }

            if (str_starts_with((string) $file, $prefix)) {
                $localFile = $localPath . $file;
                $ftpFile = $ftpDir . $file;
                $file = $sftp ?
                    $resource->get($ftpFile, $localFile) :
                    ftp_get($resource, $localFile, $ftpFile);

                if ($file) {
                    if (file_exists($localFile)) {
                        $this->readCsv($localFile, $ftpName, $numberCol);
                    }

                    $this->removeFiles($resource, $ftpFile, $localFile, $folder, $sftp);
                }
            }
        }
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws SMException
     */
    public function readCsv($file, FtpConfigurationName $ftpName, $numberCol)
    {
        $datas = $this->exportCsv->readCsv($file, ';');

        foreach ($datas as $data) {
            $data = $this->trimData($data);
            $delivered = $frExpressTracking = false;
            $pod = null;

            /** @var Order $order */
            $order = $this->orderRepository->findOneLikeNumber($data[$numberCol]);

            if (null === $order) {
                continue;
            }

            $isFrExpress = $order->getShipments()->first()
                && $order->getShipments()->first()->getMethod()
                && str_contains((string) $order->getShipments()->first()->getMethod()->getCode(), ShippingMethodInterface::METHOD_FRANCE_EXPRESS);

            switch ($ftpName) {
                case FtpConfigurationName::SPIDY:
                    $delivered = self::SPIDY_VIR_POD === $data[self::SPIDY_POD_COL];

                    // Verify if tracking is available for FR-EXPRESS
                    $frExpressTracking = self::FR_EXPRESS_EXP === $data[self::FR_EXPRESS_SHORT_EXP_COL]
                        && self::FR_EXPRESS_EXPEDIE === $data[self::FR_EXPRESS_EXP_COL];

                    // Getting FR-EXPRESS Particular DELIVERED value
                    $delivered = $isFrExpress ? self::SPIDY_VIR_LIV === $data[self::SPIDY_POD_COL] : $delivered;

                    $pod = $delivered ? $data[self::SPIDY_POD_PROOF_COL] : null;
                    break;
                case FtpConfigurationName::VIR:
                    $delivered = (in_array($data[self::VIR_POD_COL], self::VIR_LIV_ECH) && self::VIR_CFM === $data[self::VIR_CFM_COL]);
                    $pod = $data[self::VIR_POD_PROOF_COL];
                    break;
            }

            if ($delivered || $frExpressTracking) {
                if ($isFrExpress) {
                    if ($frExpressTracking
                        && ($shipment = $order->getShipments()->first())
                        && (null === $shipment->getTracking() || '' === trim($shipment->getTracking()))
                    ) {
                        /* @var Shipment $shipment */
                        $shipment->setTracking($data[self::SPIDY_TRACKING_COL]);
                        $shipment->setTrackingNumber($this->parseTrackingNumber($shipment->getTracking()));
                        $this->shipupApi->tracker($shipment);
                    }
                }

                if ($delivered) {
                    $this->setDelivered($order, $data);
                    if (null !== $pod
                        && ($shipment = $order->getShipments()->first())
                    ) {
                        /* @var Shipment $shipment */
                        $shipment->setProofOfDelivery($pod);
                    }
                }
            }
        }
    }

    /**
     * @throws SMException
     */
    public function setDelivered(Order $order, array $data = [])
    {
        $finalStateMachine = null;

        $this->cliBasedChannelContext->setChannel($order->getChannel());
        if ($order->getShipments()->count() > 1) {
            foreach ($order->getShipments() as $shipment) {
                $stateMachine = $this->smFactory->get($shipment, ShipmentTransitions::GRAPH);

                if ($stateMachine->can(OrderShippingTransitions::TRANSITION_DELIVER)) {
                    if (null === $finalStateMachine) {
                        $finalStateMachine = $stateMachine;
                    }

                    if (isset($data[self::SPIDY_TRACKING_COL]) && $data[self::SPIDY_TRACKING_COL] === trim((string) $shipment->getTracking())) {
                        $finalStateMachine = $stateMachine;
                        break;
                    }
                }
            }
        } else {
            $shipment = $order->getShipments()->first();

            if ($shipment) {
                $stateMachine = $this->smFactory->get($shipment, ShipmentTransitions::GRAPH);

                if ($stateMachine->can(OrderShippingTransitions::TRANSITION_DELIVER)) {
                    $finalStateMachine = $stateMachine;
                }
            }
        }

        if (null !== $finalStateMachine) {
            $finalStateMachine->apply(OrderShippingTransitions::TRANSITION_DELIVER);
        }
    }

    private function trimData(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = trim((string) $value);
        }

        return $data;
    }

    private function parseTrackingNumber(string $link): string
    {
        $exploded = explode('/', $link);

        return end($exploded);
    }
}
