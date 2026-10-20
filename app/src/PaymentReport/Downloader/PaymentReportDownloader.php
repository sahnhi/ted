<?php

declare(strict_types=1);

namespace App\PaymentReport\Downloader;

use App\HttpClient\HttpClientInterface;
use App\PaymentReport\Exception\PaymentReportClientDownloadFailed;
use App\PaymentReport\Model\Payment;
use App\PaymentReport\Model\PaymentFactory;
use App\PaymentReport\Model\PaymentList;
use App\Utils\CsvUtils;
use App\Utils\Url;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClient;

final class PaymentReportDownloader implements PaymentReportDownloaderInterface
{
    public function __construct(
        private readonly string $basicAuth,
        private readonly SymfonyHttpClient $httpClient
    ) {
    }

    public function download(Url $url): PaymentList
    {
        $headers = ['Authorization' => 'Basic ' . base64_encode($this->basicAuth)];
        $response = $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            (string) $url,
            [
                'headers' => $headers,
            ]
        );

        if (400 <= $response->getStatusCode()) {
            throw new PaymentReportClientDownloadFailed();
        }

        return new PaymentList(array_map(
            static fn ($payment): Payment => PaymentFactory::create($payment),
            CsvUtils::csvWithHeaderToArray($response->getContent())
        ));
    }
}
