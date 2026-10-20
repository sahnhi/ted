<?php

declare(strict_types=1);

namespace App\Factory\Calendar\Request;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Request\CalendarAvailabilityRequest;
use App\Services\Channel\ChannelCode;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;

class CalendarAvailabilityRequestFactory
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function createFromRequest(Request $request): CalendarAvailabilityRequest
    {
        $date = $request->request->get('date');
        $now = $request->request->get('now');

        $order = null;
        $tokenValue = $request->request->get('tokenValue');
        if (null !== $tokenValue) {
            /** @var Order|null $order */
            $order = $this->orderRepository->findOneBy(['tokenValue' => $tokenValue]);
        }

        if (null !== $order) {
            $channelCode = $order->getChannel()?->getCode();
            $countryCode = $order->getShippingAddress()?->getCountryCode();
            $postalCode = $order->getShippingAddress()?->getPostcode();
            $products = $this->formatOrderItemsAsArray($order->getItems());
        } else {
            $channelCode = $request->request->get('channelCode');
            $countryCode = $request->request->get('countryCode');
            $postalCode = $request->request->get('postalCode');
            $products = (array) $request->request->get('products', []);
        }

        return new CalendarAvailabilityRequest(
            date: empty($date) ? new \DateTime() : \DateTime::createFromFormat('Y-m-d', $date),
            now: empty($now) ? new \DateTime() : \DateTime::createFromFormat('Y-m-d', $now),
            countryCode: $countryCode,
            postalCode: $postalCode,
            channelCode: $channelCode,
            products: $products,
            limit: $request->request->getInt('limit'),
            withoutStock: $request->request->get('withoutStock', false),
            calendar: (bool) $request->request->get('calendar', true),
            order: $order,
            selectedShippingMethodCategoryCode: $request->request->get('selectedShippingMethodCategoryCode'),
        );
    }

    public function createForAutonomousCreate(array $orderData): CalendarAvailabilityRequest
    {
        return new CalendarAvailabilityRequest(
            date: new \DateTime(),
            now: new \DateTime(),
            countryCode: $orderData['shippingAddress']['countryCode'],
            postalCode: $orderData['shippingAddress']['postcode'],
            channelCode: ChannelCode::CHANNEL_RETURN,
            products: array_map(static fn (array $item): array => [
                'code' => $item['variant'],
                'quantity' => $item['quantity'],
            ], $orderData['items']),
        );
    }

    public function createForAutonomousReturn(Order $order): CalendarAvailabilityRequest
    {
        return new CalendarAvailabilityRequest(
            date: new \DateTime(),
            now: new \DateTime(),
            countryCode: $order->getShippingAddress()->getCountryCode(),
            postalCode: $order->getShippingAddress()->getPostcode(),
            channelCode: ChannelCode::CHANNEL_RETURN,
            products: $this->formatOrderItemsAsArray($order->getItems()),
            order: $order
        );
    }

    public function createForDeliveryDateEstimate(Order $order): CalendarAvailabilityRequest
    {
        return new CalendarAvailabilityRequest(
            date: new \DateTime(),
            now: new \DateTime(),
            countryCode: $order->getShippingAddress()?->getCountryCode(),
            postalCode: $order->getShippingAddress()?->getPostcode(),
            channelCode: $order->getChannel()?->getCode(),
            products: array_map(static fn (OrderItem $item): array => [
                'code' => $item->getVariant()?->getCode(),
                'quantity' => $item->getQuantity(),
            ], $order->getItems()->toArray()),
            calendar: true,
            order: $order,
        );
    }

    private function formatOrderItemsAsArray(Collection $items): array
    {
        return array_map(static fn (OrderItem $item): array => [
            'code' => $item->getVariant()?->getCode(),
            'quantity' => $item->getQuantity(),
        ], $items->toArray());
    }
}
