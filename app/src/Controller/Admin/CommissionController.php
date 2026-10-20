<?php

namespace App\Controller\Admin;

use App\Services\Admin\Export\ExportCsv;
use App\Services\Admin\ReportingData;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

class CommissionController extends ResourceController
{
    public function exportAction(Request $request): Response
    {
        $channelCode = $request->get('channel');
        $type = $request->get('type');

        $channelRepository = $this->get('sylius.repository.channel');
        $reportingDataService = $this->get(ReportingData::class);

        $channel = $channelRepository->findOneByCode($channelCode);

        Assert::notNull($channel, "Channel doesn't exist");

        switch ($type) {
            case 'shop':
                $data = $reportingDataService->getShopData($channel);
                break;
            case 'seller':
                $data = $reportingDataService->getSellerData($channel);
                break;
            case 'simple':
                $data = $reportingDataService->getSimpleData($channel);
                break;
            default:
                $data = $reportingDataService->getAllData($channel);
                break;
        }

        return (new ExportCsv())->generateCsv($data, 'reporting_' . $channelCode . '_' . $type);
    }
}
