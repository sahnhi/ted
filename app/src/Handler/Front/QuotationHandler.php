<?php

namespace App\Handler\Front;

use App\Business\Command\Quotation;
use App\Doctrine\Entity\OrderStore;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Order;
use App\Message\DTO\OrderDTO;
use App\Message\Mail;
use App\Services\CaptainMetrics\CaptainMetricsApi;
use App\Services\Drip\DripApiInterface;
use App\Services\Mail\MailerEmailInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Sylius\Bundle\ApiBundle\Provider\CustomerProviderInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

class QuotationHandler
{
    public function __construct(
        private readonly ObjectManager $orderManager,
        private readonly RepositoryInterface $channelRepository,
        private readonly CustomerProviderInterface $customerProvider,
        private readonly DripApiInterface $dripApi,
        private readonly CaptainMetricsApi $captainMetricsApi,
        private readonly MessageBusInterface $bus
    ) {
    }

    /**
     * @throws \Exception
     */
    public function handle(Quotation $quotation): void
    {
        $this->fillAddress($quotation);

        $customer = $this->customerProvider->provide($quotation->getEmail());

        if (null !== $quotation->getPhoneNumber()) {
            $customer->setPhoneNumber($quotation->getPhoneNumber());
        }

        if (null !== $quotation->getLastName()) {
            $customer->setLastName($quotation->getLastname());
        }

        if (null !== $quotation->getFirstName()) {
            $customer->setFirstName($quotation->getFirstname());
        }

        $orderStore = new OrderStore();
        $orderStore->setSeller($quotation->getSeller());
        $orderStore->setStore($quotation->getStore());

        /** @var Order $cart */
        $cart = $quotation->getCart();

        if (null !== $quotation->getAddress()) {
            $quotation->getAddress()->setCustomer($customer);
            $cart->setShippingAddress($quotation->getAddress());
            $cart->setBillingAddress($quotation->getAddress());
        }

        if ($cart->getCustomer()) {
            Assert::eq($cart->getCustomer(), $customer, 'Cart associated to another email');
        }

        Assert::true('paid' !== $cart->getPaymentState(), 'Order already paid');

        /** @var ChannelInterface $webChannel */
        $webChannel = $this->channelRepository->find(1);
        $cart->setChannel($webChannel);
        $cart->setCustomer($customer);
        $cart->setTokenValue(Uuid::uuid4()->toString());
        $cart->setOrderStore($orderStore);

        $orderStore->setOrder($cart);

        $this->orderManager->persist($cart);
        $this->orderManager->flush();

        $this->dripApi->sendOrder($quotation->getCart());

        if (null === $cart->getShippingAddress()) {
            $address = new Address();
            $address->setFirstName($quotation->getFirstname() ?? '');
            $address->setLastName($quotation->getLastname() ?? '');
            $address->setPostcode(($quotation->getAddress() && $quotation->getAddress()->getPostCode()) ? $quotation->getAddress()->getPostCode() : '');
            $address->setCity(($quotation->getAddress() && $quotation->getAddress()->getCity()) ? $quotation->getAddress()->getCity() : '');
            $address->setStreet(($quotation->getAddress() && $quotation->getAddress()->getStreet()) ? $quotation->getAddress()->getStreet() : '');
            $address->setCountryCode(($quotation->getAddress() && $quotation->getAddress()->getCountryCode()) ? $quotation->getAddress()->getCountryCode() : '');
            $cart->setShippingAddress($address);
        }

        $cart->setBillingAddress($cart->getShippingAddress());

        $mailer = new Mail(MailerEmailInterface::TYPE_QUOTATION_EMAIL, new OrderDTO($cart), [
            'recipients' => [
                [
                    'email' => $cart->getCustomer()->getEmail(),
                    'firstname' => $cart->getCustomer()->getFirstName(),
                    'lastname' => $cart->getCustomer()->getLastName(),
                ],
            ],
        ]);

        $this->bus->dispatch($mailer);

        $this->captainMetricsApi->sendOrderStore($orderStore);
    }

    public function fillAddress(Quotation $quotation): void
    {
        $address = $quotation->getAddress();
        if (null === $address) {
            return;
        }

        if (null === $address->getPhoneNumber() && null !== $quotation->getPhoneNumber()) {
            $address->setPhoneNumber($quotation->getPhoneNumber());
        }

        if (null === $address->getFirstName() && null !== $quotation->getFirstName()) {
            $address->setFirstName($quotation->getFirstName());
        }

        if (null === $address->getLastName() && null !== $quotation->getLastName()) {
            $address->setLastName($quotation->getLastName());
        }
    }
}
