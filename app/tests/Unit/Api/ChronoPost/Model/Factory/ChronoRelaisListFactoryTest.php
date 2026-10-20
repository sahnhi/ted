<?php

declare(strict_types=1);

namespace App\Tests\Unit\Api\ChronoPost\Model\Factory;

use App\Api\ChronoPost\Model\ChronoRelaisList;
use App\Api\ChronoPost\Model\Factory\ChronoRelaisListFactory;
use App\Tests\TestCase;

final class ChronoRelaisListFactoryTest extends TestCase
{
    private ChronoRelaisListFactory $chronoRelaisListFactory;

    protected function setUp(): void
    {
        $this->chronoRelaisListFactory = new ChronoRelaisListFactory();
    }

    public function testEmptyList(): void
    {
        $chronoRelaisList = $this->chronoRelaisListFactory->createFromSoapResponse([]);
        $this->assertEquals(new ChronoRelaisList([]), $chronoRelaisList);
    }

    public function testListContainOneChronoRelais(): void
    {
        $argument =
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
                [
                    'jour' => '7',
                ],
                [
                    'horairesAsString' => '09:00-12:00',
                    'jour' => '6',
                    'listeHoraireOuverture' => [
                        'debut' => '09:00',
                        'fin' => '12:00',
                    ],
                ],
                [
                    'horairesAsString' => '08:30-12:00 14:00-17:30',
                    'jour' => '5',
                    'listeHoraireOuverture' => [
                        [
                            'debut' => '08:30',
                            'fin' => '12:00',
                        ],
                        [
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
        ];

        $chronoRelaisList = $this->chronoRelaisListFactory->createFromSoapResponse($argument);

        $this->assertSame(
            [
                [
                    'accesPersonneMobiliteReduite' => true,
                    'actif' => true,
                    'address_1' => '11 PLACE DU MONUMENT',
                    'address_2' => '',
                    'address_3' => '',
                    'countryCode' => 'FR',
                    'postalCode' => '15400',
                    'latitude' => '45.2827460',
                    'longitude' => '2.65948700',
                    'distance' => 0,
                    'identifier' => '7573O',
                    'localisationNumber' => '',
                    'openingTime' => [
                        [
                            'jour' => '7',
                        ],
                        [
                            'horairesAsString' => '09:00-12:00',
                            'jour' => '6',
                            'listeHoraireOuverture' => [
                                'debut' => '09:00',
                                'fin' => '12:00',
                            ],
                        ],
                        [
                            'horairesAsString' => '08:30-12:00 14:00-17:30',
                            'jour' => '5',
                            'listeHoraireOuverture' => [
                                [
                                    'debut' => '08:30',
                                    'fin' => '12:00',
                                ],
                                [
                                    'debut' => '14:00',
                                    'fin' => '17:30',
                                ],
                            ],
                        ],
                    ],
                    'city' => 'RIOM ES MONTAGNES',
                    'name' => 'LA POSTE DE RIOM ES MONTAGNES',
                    'weight' => 20,
                    'type' => 'P',
                    'urlGoogleMaps' => 'http://maps.google.fr/maps?q=45.2827460,2.65948700',
                ],
            ],
            $chronoRelaisList->toArray());
    }

    public function testListContainMultipleChronoRelais(): void
    {
        $argument =
            [
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
                        [
                            'jour' => '7',
                        ],
                        [
                            'horairesAsString' => '09:00-12:00',
                            'jour' => '6',
                            'listeHoraireOuverture' => [
                                'debut' => '09:00',
                                'fin' => '12:00',
                            ],
                        ],
                        [
                            'horairesAsString' => '08:30-12:00 14:00-17:30',
                            'jour' => '5',
                            'listeHoraireOuverture' => [
                                [
                                    'debut' => '08:30',
                                    'fin' => '12:00',
                                ],
                                [
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
                            [
                                'jour' => '7',
                            ],
                            [
                                'horairesAsString' => '09:00-12:00',
                                'jour' => '6',
                                'listeHoraireOuverture' => [
                                    'debut' => '09:00',
                                    'fin' => '12:00',
                                ],
                            ],
                            [
                                'horairesAsString' => '08:30-12:00 14:00-17:30',
                                'jour' => '5',
                                'listeHoraireOuverture' => [
                                    [
                                        'debut' => '08:30',
                                        'fin' => '12:00',
                                    ],
                                    [
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
                    ];

        $chronoRelaisList = $this->chronoRelaisListFactory->createFromSoapResponse($argument);

        $this->assertSame(
            [
                [
                    'accesPersonneMobiliteReduite' => true,
                    'actif' => true,
                    'address_1' => '11 PLACE DU MONUMENT',
                    'address_2' => '',
                    'address_3' => '',
                    'countryCode' => 'FR',
                    'postalCode' => '15400',
                    'latitude' => '45.2827460',
                    'longitude' => '2.65948700',
                    'distance' => 0,
                    'identifier' => '7573O',
                    'localisationNumber' => '',
                    'openingTime' => [
                        [
                            'jour' => '7',
                        ],
                        [
                            'horairesAsString' => '09:00-12:00',
                            'jour' => '6',
                            'listeHoraireOuverture' => [
                                'debut' => '09:00',
                                'fin' => '12:00',
                            ],
                        ],
                        [
                            'horairesAsString' => '08:30-12:00 14:00-17:30',
                            'jour' => '5',
                            'listeHoraireOuverture' => [
                                [
                                    'debut' => '08:30',
                                    'fin' => '12:00',
                                ],
                                [
                                    'debut' => '14:00',
                                    'fin' => '17:30',
                                ],
                            ],
                        ],
                    ],
                    'city' => 'RIOM ES MONTAGNES',
                    'name' => 'LA POSTE DE RIOM ES MONTAGNES',
                    'weight' => 20,
                    'type' => 'P',
                    'urlGoogleMaps' => 'http://maps.google.fr/maps?q=45.2827460,2.65948700',
                ],
                [
                    'accesPersonneMobiliteReduite' => true,
                    'actif' => true,
                    'address_1' => '11 PLACE DU MONUMENT',
                    'address_2' => '',
                    'address_3' => '',
                    'countryCode' => 'FR',
                    'postalCode' => '15400',
                    'latitude' => '45.2827460',
                    'longitude' => '2.65948700',
                    'distance' => 0,
                    'identifier' => '7573O',
                    'localisationNumber' => '',
                    'openingTime' => [
                        [
                            'jour' => '7',
                        ],
                        [
                            'horairesAsString' => '09:00-12:00',
                            'jour' => '6',
                            'listeHoraireOuverture' => [
                                'debut' => '09:00',
                                'fin' => '12:00',
                            ],
                        ],
                        [
                            'horairesAsString' => '08:30-12:00 14:00-17:30',
                            'jour' => '5',
                            'listeHoraireOuverture' => [
                                [
                                    'debut' => '08:30',
                                    'fin' => '12:00',
                                ],
                                [
                                    'debut' => '14:00',
                                    'fin' => '17:30',
                                ],
                            ],
                        ],
                    ],
                    'city' => 'RIOM ES MONTAGNES',
                    'name' => 'LA POSTE DE RIOM ES MONTAGNES',
                    'weight' => 20,
                    'type' => 'P',
                    'urlGoogleMaps' => 'http://maps.google.fr/maps?q=45.2827460,2.65948700',
                ],
            ],
            $chronoRelaisList->toArray());
    }
}
