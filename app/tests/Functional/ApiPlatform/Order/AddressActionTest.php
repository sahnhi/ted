<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\Order;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AddressActionTest extends ApiTestCase
{
    private readonly OrderRepository $orderRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->orderRepository = self::$container->get('sylius.repository.order');
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testAddAddressByOnlyExistingEmail(): void
    {
        self::createClient()->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72ad/address', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'email' => 'customer1@email.com',
                'billingAddress' => [],
            ],
        ]);

        self::assertResponseIsSuccessful();

        /** @var Order $cart */
        $cart = $this->orderRepository->findOneBy(['tokenValue' => '5811cf75-84a3-4673-9cc3-00755d1b72ad']);

        self::assertNotNull($cart->getBillingAddress());
        self::assertNotNull($cart->getShippingAddress());
        self::assertNotNull($cart->getCustomer());
        self::assertEquals('customer1@email.com', $cart->getBillingAddress()->getCustomer()->getEmail());
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testSubmitAddresses(): void
    {
        self::createClient()->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72ad/address', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'customer' => [
                    'email' => 'not_batman@wayneenterprise.com',
                    'phoneNumber' => '0789786756',
                    'firstName' => 'Bruce',
                    'lastName' => 'Wayne',
                    'newsletter' => false,
                    'optin' => false,
                    'optinPhone' => false,
                ],
                'shippingAddress' => [
                    'firstName' => 'Batman',
                    'lastName' => 'TheRealOne',
                    'city' => 'Gotham',
                    'street' => 'Bat Cave',
                    'countryCode' => 'FR',
                    'postcode' => '38200',
                    'provinceName' => 'I still do not know',
                    'provinceCode' => 'STILL-IDK',
                    'phoneNumber' => '0789786756',
                    'company' => 'Wayne Enterprise',
                    'details' => 'Gift for Alfred <3',
                    'tva' => '',
                ],
                'billingAddress' => [
                    'firstName' => 'Bruce',
                    'lastName' => 'Wayne',
                    'city' => 'Gotham',
                    'street' => 'Gotham Avenue',
                    'countryCode' => 'FR',
                    'postcode' => '38200',
                    'provinceName' => 'No idea',
                    'provinceCode' => 'IDK',
                    'phoneNumber' => '0789786756',
                    'company' => 'Wayne Enterprise',
                    'details' => 'This is TOTALLY NOT Batman\'s address',
                    'tva' => '',
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();

        /** @var Order $cart */
        $cart = $this->orderRepository->findOneBy(['tokenValue' => '5811cf75-84a3-4673-9cc3-00755d1b72ad']);

        /** @var Address $billingAddress */
        $billingAddress = $cart->getBillingAddress();

        self::assertNotNull($billingAddress);
        self::assertEquals('Bruce', $billingAddress->getFirstName());
        self::assertEquals('Wayne', $billingAddress->getLastName());
        self::assertEquals('Gotham', $billingAddress->getCity());
        self::assertEquals('Gotham Avenue', $billingAddress->getStreet());
        self::assertEquals('FR', $billingAddress->getCountryCode());
        self::assertEquals('38200', $billingAddress->getPostcode());
        self::assertEquals('No idea', $billingAddress->getProvinceName());
        self::assertEquals('IDK', $billingAddress->getProvinceCode());
        self::assertEquals('0789786756', $billingAddress->getPhoneNumber());
        self::assertEquals('Wayne Enterprise', $billingAddress->getCompany());
        self::assertEquals('This is TOTALLY NOT Batman\'s address', $billingAddress->getDetails());

        /** @var Address $shippingAddress */
        $shippingAddress = $cart->getShippingAddress();

        self::assertNotNull($shippingAddress);
        self::assertEquals('Batman', $shippingAddress->getFirstName());
        self::assertEquals('TheRealOne', $shippingAddress->getLastName());
        self::assertEquals('Gotham', $shippingAddress->getCity());
        self::assertEquals('Bat Cave', $shippingAddress->getStreet());
        self::assertEquals('FR', $shippingAddress->getCountryCode());
        self::assertEquals('38200', $shippingAddress->getPostcode());
        self::assertEquals('I still do not know', $shippingAddress->getProvinceName());
        self::assertEquals('STILL-IDK', $shippingAddress->getProvinceCode());
        self::assertEquals('0789786756', $shippingAddress->getPhoneNumber());
        self::assertEquals('Wayne Enterprise', $shippingAddress->getCompany());
        self::assertEquals('Gift for Alfred <3', $shippingAddress->getDetails());
    }
}
