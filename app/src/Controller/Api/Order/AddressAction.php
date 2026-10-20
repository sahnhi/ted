<?php

namespace App\Controller\Api\Order;

use App\Business\Command\AddressOrder;
use App\Exception\Api\Address\InvalidBodyException;
use App\Model\Address;
use App\Model\Customer;
use App\Services\Sentry\ReportingInterface;
use League\Tactician\CommandBus;
use Sentry\Severity;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class AddressAction
{
    public function __construct(
        private readonly CommandBus $bus,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ReportingInterface $reporting,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function __invoke(Request $request): OrderInterface
    {
        $token = $request->attributes->get('tokenValue');

        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneBy(['tokenValue' => $token]);
        Assert::notNull($order, $this->translator->trans('app.api.order.address.error.not_found', ['token' => $token]));

        $shippingRequired = true;
        foreach ($order->getItems() as $item) {
            if (!$item->getVariant()->isShippingRequired()) {
                $shippingRequired = false;
            }
        }

        $content = json_decode($request->getContent(), true);

        $email = $content['email'] ?? null;
        if (isset($email)) {
            $customer['newsletter'] = true;
            $customer['email'] = $email;
            $customer['optin'] = true;

            $addressOrder = new AddressOrder(
                $token,
                Address::createFromArray([], false),
                Address::createFromArray([], false),
                Customer::createFromArray($customer),
                $order
            );
        } else {
            $shippingAddress = $content['shippingAddress'] ?? null;
            Assert::notNull($shippingAddress);
            $billingAddress = $content['billingAddress'] ?? null;

            if (null === $billingAddress) {
                $billingAddress = $shippingAddress;
            } else {
                Assert::notNull($billingAddress);
            }
            $customer = $content['customer'];
            Assert::notNull($customer);
            if ($shippingRequired) {
                /** @var array $shippingAddress */
                $shippingAddress = $this->formatEmptyAddress($shippingAddress);
                /** @var array $billingAddress */
                $billingAddress = $this->formatEmptyAddress($billingAddress);
                if (empty($shippingAddress)) {
                    /* @var array $customer */
                    $addressOrder = new AddressOrder(
                        $token,
                        Address::createFromArray($shippingAddress, false),
                        Address::createFromArray($billingAddress, false),
                        Customer::createFromArray($customer),
                        $order
                    );
                    $this->bus->handle($addressOrder);
                    $this->reporting->reportIssue('Address may be empty here', Severity::info(), [
                        'order_number' => $order->getNumber(),
                        'shipping' => 'Y',
                        'address' => json_encode($shippingAddress),
                    ]);
                    throw new InvalidBodyException('Field empty');
                }
            }
            $addressOrder = new AddressOrder(
                $token,
                Address::createFromArray($shippingAddress),
                Address::createFromArray($billingAddress),
                Customer::createFromArray($customer),
                $order
            );
            $this->reporting->reportIssue('Address should not be empty here', Severity::info(), [
                'order_number' => $order->getNumber(),
                'shipping' => $shippingRequired ? 'Y' : 'N',
                'address' => json_encode($shippingAddress),
            ]);
        }

        $this->bus->handle($addressOrder);

        return $order;
    }

    private function addressValidator(array $address): void
    {
        Assert::keyExists($address, 'firstName');
        Assert::keyExists($address, 'lastName');
        Assert::keyExists($address, 'city');
        Assert::keyExists($address, 'street');
        Assert::keyExists($address, 'countryCode');
        Assert::keyExists($address, 'postcode');
        Assert::keyExists($address, 'phoneNumber');
    }

    private function formatEmptyAddress(array $address): array
    {
        $this->addressValidator($address);
        if (empty($address['firstName'])
            || empty($address['lastName'])
            || empty($address['city'])
            || empty($address['street'])
            || empty($address['countryCode'])
            || empty($address['postcode'])
            || empty($address['phoneNumber'])
        ) {
            return [];
        }

        return $address;
    }
}
