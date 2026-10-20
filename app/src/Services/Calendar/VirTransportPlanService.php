<?php

namespace App\Services\Calendar;

use App\Services\Admin\Export\ExportCsv;
use App\Services\Calendar\Maker\DeliverySlotMaker;
use App\Services\Filesystem\GoogleStorageTransmissionFilesystem;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class VirTransportPlanService
{
    public const NAME_FILE = 'PlanTransportVIR.csv';

    private string $privateFolder;

    public function __construct(
        private readonly GoogleStorageTransmissionFilesystem $googleStorageTransmissionFilesystem,
        private readonly ExportCsv $exportCsv,
        private readonly LogsCalendarService $logsCalendarService,
        private readonly DeliverySlotMaker $deliverySlotMaker,
        protected string $virTransportPlanFolder,
        private readonly CacheInterface $cache,
        string $projectDir,
    ) {
        $this->privateFolder = $projectDir . '/src/Resources/private/';
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getVirTransportPlan($zipCode, string $countryCode)
    {
        $plan = [];

        $folderName = 'vir_transport_plan';
        $folderPath = $this->privateFolder;

        if (!is_dir($folderPath . $folderName)) {
            mkdir($folderPath . $folderName, 0755, true);
        }

        $virTransportPlan = $this->cache->get('vir_transport_plan_' . $countryCode, function (ItemInterface $item) use ($folderName, $countryCode, $folderPath) {
            $item->expiresAfter(600); // 10 minutes

            $cloudPath = $this->virTransportPlanFolder . $countryCode . '_' . self::NAME_FILE;
            $localPath = $folderName . '/' . $countryCode . '_' . self::NAME_FILE;
            $this->googleStorageTransmissionFilesystem->download($cloudPath, $localPath);

            return $this->exportCsv->readCsv($folderPath . $localPath, ';');
        });

        foreach ($virTransportPlan as $data) {
            if ('code_pos' !== $data[0] && $data[0] === $zipCode) {
                $plan = $data;
                break;
            }
        }

        if (empty($plan)) {
            $this->logsCalendarService->logsVirPlanTransport($zipCode);
        }

        return $plan;
    }

    public function getOpeningDays($openingDays)
    {
        foreach ($openingDays as $key => &$openingDay) {
            if (0 !== $key && '000' !== $openingDay) {
                $openingDay = $key;
            } else {
                unset($openingDays[$key]);
            }
        }

        return $openingDays;
    }

    /**
     * @throws \Exception
     */
    public function getMakerTimeInformation($shippingMethod, $day, $nb): array
    {
        $numberOfDay = (new \DateTime($day))->format('N');
        $planOfDay = str_split((string) $shippingMethod['transport_plan'][$numberOfDay]);
        $deliverySlot = $this->deliverySlotMaker->makeDeliverySlot($shippingMethod, $nb, $day);
        if (0 === $nb && count($deliverySlot) < 3) { // 1st day with cuttoff
            array_unshift($deliverySlot['times'], ''); // to compare same key as a plan, to refacto calcul nbr of slot between cuttoff
        }

        foreach ($planOfDay as $key => $dayPart) {
            if ('0' === $dayPart) {
                unset($deliverySlot['times'][$key]);
            }
        }

        return $deliverySlot;
    }
}
