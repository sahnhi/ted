<?php

namespace App\Api\Shipup;

use App\Api\Shipup\DTO\ShipupMessageInterface;
use App\Api\Shipup\DTO\ShipupOrder;
use App\Doctrine\Entity\Sylius\ShipmentInterface;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Message\Shipup;
use App\Services\Sentry\ReportingInterface;
use GuzzleHttp\Client;
use JMS\Serializer\Expression\ExpressionEvaluator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Sentry;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Messenger\MessageBusInterface;

class ShipupApi implements ShipupApiInterface
{
    private readonly SerializerInterface $serializer;
    private readonly Client $client;

    public function __construct(
        string $shipupApiUrl,
        private readonly string $shipupApiKey,
        private readonly LoggerInterface $logger,
        private readonly string $env,
        private readonly MessageBusInterface $bus,
        private readonly ReportingInterface $reporting
    ) {
        $this->client = new Client(['base_uri' => $shipupApiUrl]);

        $this->serializer = SerializerBuilder::create()
            ->setExpressionEvaluator(new ExpressionEvaluator(new ExpressionLanguage()))
            ->build();
    }

    public function request(ShipupMessageInterface $subject, string $path, string $method = 'post'): void
    {
        $payload = $this->serializer->serialize($subject, 'json', SerializationContext::create()->setGroups([$path, ShipupMessageInterface::SHIPUP_DEFAULT_CONTEXT]));

        $this->logger->log(LogLevel::INFO, 'Sending To Shipup...', [
            'route' => $path,
            'payload' => $payload,
            'method' => $method,
        ]);

        try {
            $this->client->$method($path, ['body' => $payload, 'headers' => ['Authorization' => 'Bearer ' . $this->shipupApiKey, 'accept' => 'application/json', 'Content-Type' => 'application/json']]);
        } catch (\Exception $e) {
            $this->reporting->reportIssue(
                $e,
                Sentry\Severity::error(),
                ['shipup' => $path],
                ['payload' => $payload]
            );

            $this->logger->log(LogLevel::ERROR, 'Failed to send order to Shipup', [
                'route' => $path,
                'payload' => $payload,
                'method' => $method,
                'error' => $e->getMessage(),
                'codeError' => $e->getCode(),
            ]);
        }
    }

    public function dispatchShipupMessage(ShipupMessageInterface $subject, string $path, string $method = 'post'): void
    {
        $this->bus->dispatch(new Shipup($subject, $path, $method));
    }

    public function order(OrderInterface $order): void
    {
        if (!$this->sendOrderToShipup($order)) {
            return;
        }

        try {
            $orderDTO = new ShipupOrder($order, 'prod' !== $this->env);
            $this->dispatchShipupMessage($orderDTO, self::SHIPUP_ORDER_PATH);
        } catch (\Throwable $exception) {
            $this->handleException($order, $exception, self::SHIPUP_ORDER_PATH, 'Failed Sylius -> Shipup Order Message Creation');
        }
    }

    public function tracker(ShipmentInterface $shipment): void
    {
        if (!$this->sendTrackerToShipup($shipment)) {
            return;
        }

        try {
            $orderDTO = new ShipupOrder($shipment->getOrder(), 'prod' !== $this->env);

            $this->dispatchShipupMessage($orderDTO, self::SHIPUP_ORDER_PATH);
        } catch (\Throwable $exception) {
            $this->handleException($shipment->getOrder(), $exception, self::SHIPUP_ORDER_PATH, 'Failed Sylius -> Shipup Tracker Message Creation');
        }
    }

    private function handleException(
        OrderInterface $order,
        \Throwable $exception,
        string $path,
        string $message
    ): void {
        $this->logger->log(LogLevel::ERROR, $message, [
            'route' => $path,
            'number' => $order->getNumber(),
            'error' => $exception->getMessage(),
        ]);

        $this->reporting->reportIssue(
            $exception,
            Sentry\Severity::error(),
            ['shipup' => $path],
            ['order' => $order]
        );
    }

    private function sendOrderToShipup(OrderInterface $order): bool
    {
        return $this->hasDeliveryMethod($order);
    }

    private function sendTrackerToShipup(ShipmentInterface $shipment): bool
    {
        return $this->sendOrderToShipup($shipment->getOrder()) && null !== $shipment->getTrackingNumber();
    }

    private function hasDeliveryMethod(OrderInterface $order): bool
    {
        foreach ($order->getShipments() as $shipment) {
            /** @var ShippingMethodInterface|null $shippingMethod */
            $shippingMethod = $shipment->getMethod();

            if ($shippingMethod instanceof ShippingMethodInterface && null !== $shippingMethod->getShipupCarrierCode()) {
                return true;
            }
        }

        return false;
    }
}
