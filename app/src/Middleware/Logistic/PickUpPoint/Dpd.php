<?php

declare(strict_types=1);

namespace App\Middleware\Logistic\PickUpPoint;

use App\Doctrine\Entity\Sylius\Order;
use App\Model\Logistic\PickUp\OpeningHours;
use App\Model\Logistic\PickUp\PickUpPoint;
use App\Model\Logistic\PickUp\PickUpPoints;
use App\Model\Logistic\PickUp\SearchableAddress;

final class Dpd extends AbstractPickUpPointMiddleware implements PickUpPointMiddlewareInterface
{
    protected const CARRIER = 'dpd';

    public function fetchPoints(SearchableAddress $searchableAddress)
    {
        $params = [
            'carrier' => $this->login,
            'key' => $this->accessKey,
            'address' => $searchableAddress->getAddress(),
            'zipCode' => $searchableAddress->getZipCode(),
            'city' => $searchableAddress->getCity(),
            'countryCode' => $searchableAddress->getCountryCode(),
            'requestID' => $this->generateRequestId($searchableAddress->getOrder()),
            'date_from' => $this->dateFrom,
            'WSDL' => null,
            'weight' => $this->getOrderWeightInGrams($searchableAddress),
            'GetPudoList' => null,
            // the parameters are setted by default following the documentation
            'max_pudo_number' => 10,
            'max_distance_search' => 15,
            'category' => 1,
            'holiday_tolerant' => 1,
        ];

        $response = $this->getFromApi($this->baseUrl, $params);

        $data = simplexml_load_string($response);

        return $this->parse($data, $searchableAddress);
    }

    private function parse(\SimpleXMLElement $data, SearchableAddress $searchableAddress): PickUpPoints
    {
        $pickUpPoints = new PickUpPoints(
            $this->shippingMethodCode,
            $this->getShippingMethodId()
        );

        $floatFormatter = new \NumberFormatter('fr-FR', \NumberFormatter::DECIMAL);

        foreach ($data->PUDO_ITEMS->children() as $item) {
            $pickUpPoint = (new PickUpPoint())
                ->setId((string) $item->PUDO_ID)
                ->setDistance((string) $item->DISTANCE)
                ->setName((string) $item->NAME)
                ->setAddress((string) $item->ADDRESS1)
                ->setAddressBis((string) $item->ADDRESS2)
                ->setAddressTer((string) $item->ADDRESS3)
                ->setLocalHint((string) $item->LOCAL_HINT)
                ->setZipCode((string) $item->ZIPCODE)
                ->setCity((string) $item->CITY)
                ->setCountryCode($searchableAddress->getCountryCode())
                ->setLongitude($floatFormatter->parse((string) $item->LONGITUDE))
                ->setLatitude($floatFormatter->parse((string) $item->LATITUDE))
                ->setMapUrl((string) $item->MAP_URL)
                ->setAvailable((string) $item->AVAILABLE);

            $this->getOpeningHours($item, $pickUpPoint);

            $pickUpPoints->addPoint($pickUpPoint);
        }

        return $pickUpPoints;
    }

    private function getOpeningHours(\SimpleXMLElement $data, PickUpPoint $pickUpPoint): void
    {
        foreach ($data->OPENING_HOURS_ITEMS->children() as $item) {
            $weekDay = 7 === (int) $item->DAY_ID ? 0 : (int) $item->DAY_ID;
            $openingHours = (new OpeningHours())
                ->setDay($weekDay)
                ->setOpenTime((string) $item->START_TM)
                ->setCloseTime((string) $item->END_TM);
            $pickUpPoint->addOpeningHours($openingHours);
        }
    }

    private function generateRequestId(?Order $order): string
    {
        if ($order) {
            $requestId = $order->getTokenValue();
        } else {
            $requestId = uniqid();
        }

        return substr((string) $requestId, 0, 29);
    }
}
