<?php

namespace App\Services\Colissimo;

use App\Services\Sentry\ReportingInterface;
use GuzzleHttp\ClientInterface;
use Sentry;
use Symfony\Component\HttpFoundation\Response;

class ColissimoApi implements ColissimoApiInterface
{
    private ClientInterface $erpClient;

    public const SERVER_NAME = 'https://ws.colissimo.fr/sls-ws/SlsServiceWSRest/2.0';
    public const FRANCE_CODE = 'FR';

    public const ADDRESSEE_ADDRESS = [
        'companyName' => 'Quai 77',
        'line0' => 'Segro Park',
        'line1' => 'Batiment D',
        'line2' => '2 rue Eugène Pottier',
        'countryCode' => self::FRANCE_CODE,
        'city' => 'MARLY LA VILLE',
        'zipCode' => '95670',
    ];

    public const ADDRESSEE_PARCEL_REF = 'Benoit Moll';

    public function __construct(
        private readonly ReportingInterface $reporting
    ) {
    }

    public function getLabel($order, $item): array
    {
        $filename = 'etiquette_retour.pdf';
        $response = $packageNumber = null;

        try {
            $request = $this->erpClient->request('POST', self::SERVER_NAME . '/generateLabel', [
                'json' => $this->setArrayDatas($order, $item),
            ]);

            $parseResponse = new MTOMResponseReader($request->getBody()->getContents());
            if (isset($parseResponse->attachments[0]) && isset($parseResponse->attachments[1])) {
                $header = json_decode((string) $parseResponse->attachments[0]['data'], true);
                $packageNumber = $header['labelV2Response']['parcelNumber'];
                $response = new Response($parseResponse->attachments[1]['data'], Response::HTTP_OK, ['Content-Type' => 'application/pdf']);

                $response->headers->add([
                    'Content-Disposition' => $response->headers->makeDisposition('attachment', $filename),
                ]);
            }
        } catch (\Exception $e) {
            $this->reporting->reportIssue(
                $e,
                Sentry\Severity::error(),
                ['service' => 'colissimo'],
                [
                    'order_number' => $order->getNumber(),
                    'product' => $item->getVariant()->getProduct()->getCode(),
                ]
            );
        }

        return [$response, $packageNumber];
    }

    public function setArrayDatas($order, $item): array
    {
        $countryCode = $order->getShippingAddress()->getCountryCode();

        return [
            'contractNumber' => getenv('COLISSIMO_CONTRACT_NUMBER'),
            'password' => getenv('COLISSIMO_PASSWORD'),
            'outputFormat' => [
                'x' => 0,
                'y' => 0,
                'outputPrintingType' => 'PDF_A4_300dpi',
            ],
            'letter' => [
                'service' => [
                    'productCode' => self::FRANCE_CODE === $countryCode ? 'CORE' : 'CORI',
                    'depositDate' => date('Y-m-d'),
                    'orderNumber' => $order->getId(),
                ],
                'parcel' => [
                    'weight' => $item->getVariant()->getWeight(),
                ],
                'sender' => [
                    'address' => [
                        'lastName' => $order->getShippingAddress()->getLastName(),
                        'companyName' => $order->getShippingAddress()->getFirstName() . ' ' . $order->getShippingAddress()->getLastName(),
                        'line2' => $order->getShippingAddress()->getStreet(),
                        'countryCode' => $countryCode,
                        'city' => $order->getShippingAddress()->getCity(),
                        'zipCode' => $order->getShippingAddress()->getPostcode(),
                    ],
                ],
                'addressee' => [
                    'addresseeParcelRef' => self::ADDRESSEE_PARCEL_REF,
                    'address' => self::ADDRESSEE_ADDRESS,
                ],
            ],
        ];
    }

    public function setErpClient(ClientInterface $erpClient)
    {
        $this->erpClient = $erpClient;
    }
}
