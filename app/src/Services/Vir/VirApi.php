<?php

namespace App\Services\Vir;

use App\Services\Sentry\ReportingInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\TransferStats;
use Sentry;

class VirApi implements VirApiInterface
{
    public function __construct(
        private readonly ClientInterface $erpClient,
        private readonly ReportingInterface $reporting
    ) {
    }

    public function getToken(): string
    {
        $urlToken = getenv('VIR_API_URL_TOKEN');
        try {
            $request = $this->erpClient->request('GET', $urlToken, [
                'query' => [
                    'accessId' => 'VIRTRP',
                    'userId' => getenv('VIR_API_USER_ID'),
                    'password' => getenv('VIR_API_PASSWORD'),
                ],
            ]);
        } catch (\Exception $e) {
            $this->reporting->reportIssue(
                $e,
                Sentry\Severity::error(),
                ['service' => 'vir']
            );

            return false;
        }

        return $request->getBody()->getContents();
    }

    public function getUrlTracking($number, $token): string
    {
        $reference = ltrim((string) $number, '0');
        $urlGetTracking = getenv('VIR_API_URL_GET_TRACKING');
        $redirect = '';
        try {
            $request = $this->erpClient->request('GET', $urlGetTracking, [
                'allow_redirects' => true,
                'on_stats' => function (TransferStats $stats) use (&$redirect): void {
                    $redirect = $stats->getEffectiveUri();
                },
                'query' => [
                    'token' => $token,
                    'reference' => $reference,
                ],
            ]);
        } catch (\Exception $e) {
            $this->reporting->reportIssue(
                $e,
                Sentry\Severity::error(),
                ['service' => 'vir'],
                ['order_number' => $number]
            );
        }

        return (string) $redirect;
    }

    public function releaseToken($token)
    {
        $urlReleaseToken = getenv('VIR_API_URL_TOKEN_RELEASE');
        $request = $this->erpClient->request('GET', $urlReleaseToken, [
            'query' => [
                'token' => $token,
            ],
        ]);
    }
}
