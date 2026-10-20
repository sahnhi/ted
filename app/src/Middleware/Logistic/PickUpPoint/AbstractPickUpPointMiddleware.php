<?php

declare(strict_types=1);

namespace App\Middleware\Logistic\PickUpPoint;

use App\Api\ChronoPost\Exception\OrderCannotWeightZeroGram;
use App\Api\ChronoPost\OrderWeightCalculator\OrderWeightCalculator;
use App\Model\Logistic\PickUp\SearchableAddress;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractPickUpPointMiddleware implements PickUpPointMiddlewareInterface
{
    protected const CARRIER = '';

    private const DAYS_TO_ADD = 2;

    protected string $baseUrl;

    protected string $login;

    protected string $accessKey;

    protected int $daysToAdd;

    protected string $dateFrom;

    protected bool $enabled;

    protected string $shippingMethodCode;

    public function __construct(
        ParameterBagInterface $params,
        private readonly OrderWeightCalculator $orderWeightCalculator,
        private readonly HttpClientInterface $client,
        private readonly RepositoryInterface $shippingMethodRepository,
    ) {
        $this->checkCarrier();
        $this->setUp($params->get('carriers'));
    }

    public function getCarrier(): string
    {
        return static::CARRIER;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    protected function getFromApi(string $url, array $params = []): string
    {
        $response = $this->client->request(Request::METHOD_GET, $url, ['query' => $params]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \Exception(sprintf('Error while fetching data from %s', $url));
        }

        return $response->getContent();
    }

    protected function getOrderWeightInGrams(SearchableAddress $searchableAddress): float
    {
        if ($searchableAddress->getOrder() instanceof OrderInterface) {
            $weight = $this->orderWeightCalculator->weightInGramsCalculateFromTokenOrder($searchableAddress->getOrder()->getTokenValue());
        } else {
            $weight = 0;
            foreach ($searchableAddress->getProducts() as $product) {
                $weight += $product['variant']->getWeight() * 1000;
            }
        }

        if (0 === $weight) {
            throw new OrderCannotWeightZeroGram();
        }

        return (int) floor($weight);
    }

    private function checkCarrier(): void
    {
        if (empty(static::CARRIER)) {
            $msg = 'Carrier name is not defined in the child class: ' . static::class;
            throw new \LogicException($msg);
        }
    }

    private function setUp(array $data): void
    {
        if (!isset($data[strtolower((string) static::CARRIER)])) {
            throw new \Exception(sprintf('Carrier %s not found', static::CARRIER));
        }
        $carrier = $data[strtolower((string) static::CARRIER)];
        $this->baseUrl = $carrier['base_url'];
        $this->login = $carrier['login'];
        $this->accessKey = $carrier['access_key'];
        $this->daysToAdd = $carrier['days_to_add'] ?? self::DAYS_TO_ADD;
        $this->enabled = (bool) $carrier['enabled'];
        $this->dateFrom = (new \DateTime())->add(new \DateInterval(sprintf('P%dD', $this->daysToAdd)))->format('d/m/Y');
        $this->shippingMethodCode = $carrier['shipping_method_code'];
    }

    protected function getShippingMethodId(): int
    {
        $shippingMethod = $this->shippingMethodRepository->findOneBy(['code' => $this->shippingMethodCode]);

        if (null === $shippingMethod) {
            throw new \Exception(sprintf('Shipping method %s not found', $this->shippingMethodCode));
        }

        return $shippingMethod->getId();
    }
}
