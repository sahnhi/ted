<?php

declare(strict_types=1);

namespace App\Api\Logistic;

use App\Exception\Logistic\PickUpPoints\CarrierNotImplementedException;
use App\Exception\Logistic\PickUpPoints\SearchableAddressObjectNotValidException;
use App\Factory\Logistic\PickUp\SearchableAddressFactory;
use App\Model\Logistic\PickUp\SearchableAddress;
use App\Provider\Logistic\PickUpPointProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class PickUpController extends AbstractController
{
    private SearchableAddress $searchableAddress;
    private readonly Serializer $serializer;

    public function __construct(
        private readonly PickUpPointProvider $pickUpPointProvider,
        private readonly SearchableAddressFactory $searchableAddressFactory,
    ) {
        $encoders = [new JsonEncoder(), new XmlEncoder(), new YamlEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function __invoke(string $method, Request $request): Response
    {
        $this->searchableAddress = $this->searchableAddressFactory->createNewFromRequest($request);
        $format = $request->get('format', 'json');

        try {
            $data = $this->pickUpPointProvider->fetch($this->searchableAddress, $method, $format);
        } catch (CarrierNotImplementedException $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (SearchableAddressObjectNotValidException $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

        $response = $this->serializer->serialize($data, $format);

        // If we want to receive html inside json
        if ($request->get('html', false)) {
            $format = 'html';
            $response = $this->renderHtml($response);
        }

        return new Response(
            $response,
            Response::HTTP_OK,
            ['Content-Type' => $this->getContentType($format)]
        );
    }

    private function renderHtml(string $data): string
    {
        $points = json_decode($data, true);

        return $this->renderView(
            '@App/Shipment/Partial/_relays.html.twig',
            [
                'relays' => $points['points'],
                'countryCode' => $this->searchableAddress->getCountryCode(),
                'shippingMethodCode' => $points['shippingMethodCode'],
                'shippingMethodIdBalou' => $points['shippingMethodId'],
            ]
        );
    }

    private function getContentType(string $format): string
    {
        return match ($format) {
            'xml' => 'application/xml',
            'html' => 'text/html',
            'yaml' => 'application/x-yaml',
            default => 'application/json',
        };
    }
}
