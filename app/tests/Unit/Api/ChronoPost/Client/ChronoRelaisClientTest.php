<?php

declare(strict_types=1);

namespace App\Tests\Unit\Api\ChronoPost\Client;

use App\Api\ChronoPost\Client\ChronoRelaisClient;
use App\Api\ChronoPost\Exception\ChronoRelaisHttpClientFailed;
use App\Api\ChronoPost\Query\ChronoRelaisQuery;
use App\Tests\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

final class ChronoRelaisClientTest extends TestCase
{
    public function testRequestFailed(): void
    {
        $response = new MockResponse('', ['http_code' => 200]);
        $httpClient = new MockHttpClient($response);

        $decoder = $this->createMock(DecoderInterface::class);

        $chronoRelaisClient = new ChronoRelaisClient(
            $httpClient,
            'baseUri',
            'accountNumber',
            'password',
            $decoder
        );

        $query = new ChronoRelaisQuery(
            null,
            '75005',
            null,
            null,
            new \DateTime('2030-01-01 00:00:00'),
            1
        );

        $this->expectException(ChronoRelaisHttpClientFailed::class);

        $chronoRelaisClient->findNearBy($query);
    }

    public function testOneRelaisIsFound(): void
    {
        $body = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><ns1:recherchePointChronopostInterParServiceResponse xmlns:ns1="http://cxf.rechercheBt.soap.chronopost.fr/"><return><errorCode>0</errorCode><errorMessage>Code retour OK</errorMessage><listePointRelais><accesPersonneMobiliteReduite>true</accesPersonneMobiliteReduite><actif>true</actif><adresse1>11 PLACE DU MONUMENT</adresse1><adresse2></adresse2><adresse3></adresse3><codePays>FR</codePays><codePostal>15400</codePostal><coordGeolocalisationLatitude>45.2827460</coordGeolocalisationLatitude><coordGeolocalisationLongitude>2.65948700</coordGeolocalisationLongitude><distanceEnMetre>0</distanceEnMetre><identifiant>7573O</identifiant><indiceDeLocalisation></indiceDeLocalisation><listeHoraireOuverture><jour>7</jour></listeHoraireOuverture><listeHoraireOuverture><horairesAsString>09:00-12:00</horairesAsString><jour>6</jour><listeHoraireOuverture><debut>09:00</debut><fin>12:00</fin></listeHoraireOuverture></listeHoraireOuverture><listeHoraireOuverture><horairesAsString>08:30-12:00 14:00-17:30</horairesAsString><jour>5</jour><listeHoraireOuverture><debut>08:30</debut><fin>12:00</fin></listeHoraireOuverture><listeHoraireOuverture><debut>14:00</debut><fin>17:30</fin></listeHoraireOuverture></listeHoraireOuverture><listeHoraireOuverture><horairesAsString>08:30-12:00 14:00-17:30</horairesAsString><jour>4</jour><listeHoraireOuverture><debut>08:30</debut><fin>12:00</fin></listeHoraireOuverture><listeHoraireOuverture><debut>14:00</debut><fin>17:30</fin></listeHoraireOuverture></listeHoraireOuverture><listeHoraireOuverture><horairesAsString>08:30-12:00 14:00-17:30</horairesAsString><jour>3</jour><listeHoraireOuverture><debut>08:30</debut><fin>12:00</fin></listeHoraireOuverture><listeHoraireOuverture><debut>14:00</debut><fin>17:30</fin></listeHoraireOuverture></listeHoraireOuverture><listeHoraireOuverture><horairesAsString>08:30-12:00 14:00-17:30</horairesAsString><jour>2</jour><listeHoraireOuverture><debut>08:30</debut><fin>12:00</fin></listeHoraireOuverture><listeHoraireOuverture><debut>14:00</debut><fin>17:30</fin></listeHoraireOuverture></listeHoraireOuverture><listeHoraireOuverture><jour>1</jour></listeHoraireOuverture><localite>RIOM ES MONTAGNES</localite><nom>LA POSTE DE RIOM ES MONTAGNES</nom><poidsMaxi>20</poidsMaxi><typeDePoint>P</typeDePoint><urlGoogleMaps>http://maps.google.fr/maps?q=45.2827460,2.65948700</urlGoogleMaps></listePointRelais><qualiteReponse>1</qualiteReponse><wsRequestId></wsRequestId></return></ns1:recherchePointChronopostInterParServiceResponse></soap:Body></soap:Envelope>';
        $response = new MockResponse($body, ['http_code' => 200]);
        $httpClient = new MockHttpClient($response);

        $decodedBody = [
            '@xmlns:soap' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'soap:Body' => [
                'ns1:recherchePointChronopostInterParServiceResponse' => [
                    'return' => [
                        'errorCode' => '0',
                        'errorMessage' => 'Code retour OK',
                        'listePointRelais' => [
                            'accesPersonneMobiliteReduite' => 'true',
                            'actif' => 'true',
                            'adresse1' => '11 PLACE DU MONUMENT',
                            'adresse2' => '',
                            'adresse3' => '',
                            'codePays' => 'FR',
                            'codePostal' => '15400',
                            'coordGeolocalisationLatitude' => '45.2827460',
                            'coordGeolocalisationLongitude' => '2.65948700',
                            'distanceEnMetre' => '0',
                            'identifiant' => '7573O',
                            'indiceDeLocalisation' => '',
                            'listeHoraireOuverture' => [
                                0 => [
                                    'jour' => '7',
                                ],
                                1 => [
                                    'horairesAsString' => '09:00-12:00',
                                    'jour' => '6',
                                    'listeHoraireOuverture' => [
                                        'debut' => '09:00',
                                        'fin' => '12:00',
                                    ],
                                ],
                                2 => [
                                    'horairesAsString' => '08:30-12:00 14:00-17:30',
                                    'jour' => '5',
                                    'listeHoraireOuverture' => [
                                        0 => [
                                            'debut' => '08:30',
                                            'fin' => '12:00',
                                            ],
                                        1 => [
                                            'debut' => '14:00',
                                            'fin' => '17:30',
                                        ],
                                    ],
                                ],
                                3 => [
                                    'horairesAsString' => '08:30-12:00 14:00-17:30',
                                    'jour' => '4',
                                    'listeHoraireOuverture' => [
                                        0 => [
                                            'debut' => '08:30',
                                            'fin' => '12:00',
                                        ],
                                        1 => [
                                            'debut' => '14:00',
                                            'fin' => '17:30',
                                        ],
                                    ],
                                ],
                            ],
                            'localite' => 'RIOM ES MONTAGNES',
                            'nom' => 'LA POSTE DE RIOM ES MONTAGNES',
                            'poidsMaxi' => '20',
                            'typeDePoint' => 'P',
                            'urlGoogleMaps' => 'http://maps.google.fr/maps?q=45.2827460,2.65948700',
                            ],
                        'qualiteReponse' => '1',
                        'wsRequestId' => '',
                    ],
                ],
            ],
        ];

        $decoder = $this->createMock(DecoderInterface::class);

        $chronoRelaisClient = new ChronoRelaisClient(
            $httpClient,
            'baseUri',
            'accountNumber',
            'password',
            $decoder
        );

        $query = new ChronoRelaisQuery(
            null,
            '15300',
            null,
            null,
            new \DateTime('2030-01-01 00:00:00'),
            1
        );

        $decoder->expects(self::once())
            ->method('decode')
            ->willReturn(
                $decodedBody
            );

        $responseClient = $chronoRelaisClient->findNearBy($query);

        $this->assertSame(
            [
                'accesPersonneMobiliteReduite' => 'true',
                'actif' => 'true',
                'adresse1' => '11 PLACE DU MONUMENT',
                'adresse2' => '',
                'adresse3' => '',
                'codePays' => 'FR',
                'codePostal' => '15400',
                'coordGeolocalisationLatitude' => '45.2827460',
                'coordGeolocalisationLongitude' => '2.65948700',
                'distanceEnMetre' => '0',
                'identifiant' => '7573O',
                'indiceDeLocalisation' => '',
                'listeHoraireOuverture' => [
                    0 => [
                        'jour' => '7',
                    ],
                    1 => [
                        'horairesAsString' => '09:00-12:00',
                        'jour' => '6',
                        'listeHoraireOuverture' => [
                            'debut' => '09:00',
                            'fin' => '12:00',
                        ],
                    ],
                    2 => [
                        'horairesAsString' => '08:30-12:00 14:00-17:30',
                        'jour' => '5',
                        'listeHoraireOuverture' => [
                            0 => [
                                'debut' => '08:30',
                                'fin' => '12:00',
                            ],
                            1 => [
                                'debut' => '14:00',
                                'fin' => '17:30',
                            ],
                        ],
                    ],
                    3 => [
                        'horairesAsString' => '08:30-12:00 14:00-17:30',
                        'jour' => '4',
                        'listeHoraireOuverture' => [
                            0 => [
                                'debut' => '08:30',
                                'fin' => '12:00',
                            ],
                            1 => [
                                'debut' => '14:00',
                                'fin' => '17:30',
                            ],
                        ],
                    ],
                ],
                'localite' => 'RIOM ES MONTAGNES',
                'nom' => 'LA POSTE DE RIOM ES MONTAGNES',
                'poidsMaxi' => '20',
                'typeDePoint' => 'P',
                'urlGoogleMaps' => 'http://maps.google.fr/maps?q=45.2827460,2.65948700',
            ],
            $responseClient
        );
    }

    public function testNoChronoRelaisFound(): void
    {
        $body = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><ns1:recherchePointChronopostInterParServiceResponse xmlns:ns1="http://cxf.rechercheBt.soap.chronopost.fr/"><return><errorCode>601</errorCode><errorMessage>Applying last filter named \'DistanceFilter\' has excluded all remaining pudos.</errorMessage><qualiteReponse>0</qualiteReponse><wsRequestId></wsRequestId></return></ns1:recherchePointChronopostInterParServiceResponse></soap:Body></soap:Envelope>';
        $response = new MockResponse($body, ['http_code' => 200]);
        $httpClient = new MockHttpClient($response);

        $decodedBody = [
            '@xmlns:soap' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'soap:Body' => [
                'ns1:recherchePointChronopostInterParServiceResponse' => [
                    'return' => [
                        'errorCode' => '601',
                        'errorMessage' => "Applying last filter named 'DistanceFilter' has excluded all remaining pudos.",
                        'qualiteReponse' => '0',
                        'wsRequestId' => '',
                        ],
                ],
            ],
        ];

        $decoder = $this->createMock(DecoderInterface::class);

        $chronoRelaisClient = new ChronoRelaisClient(
            $httpClient,
            'baseUri',
            'accountNumber',
            'password',
            $decoder
        );

        $query = new ChronoRelaisQuery(
            null,
            '15300',
            null,
            null,
            new \DateTime('2030-01-01 00:00:00'),
            1
        );

        $decoder->expects(self::once())
            ->method('decode')
            ->willReturn(
                $decodedBody
            );

        $responseClient = $chronoRelaisClient->findNearBy($query);

        $this->assertSame(
            [],
            $responseClient
        );
    }
}
