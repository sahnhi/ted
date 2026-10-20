<?php

namespace App\Tests\Unit\Api;

use App\Api\TrustPilot;
use App\Doctrine\Entity\AskReviewTemplate;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\OAuth2\Provider;
use App\Services\Review\Seek\Seeker\TrustPilot as TrustPilotService;
use App\Services\Sentry\ReportingInterface;
use App\Tests\TestCase;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;

class TrustPilotTest extends TestCase
{
    private Order $order;
    private TrustPilot $trustPilot;
    private readonly AskReviewTemplate $askReviewTemplate;

    /**
     * @throws \ReflectionException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->order->setCustomer(new Customer());
        $shipment = new Shipment();
        $shipment->setEndDeliverySlot(new \DateTime());
        $this->order->addShipment($shipment);
        $this->order->setChannel(new Channel());

        $entityManager = $this->createMock(EntityManager::class);

        $this->trustPilot = new TrustPilot(
            $entityManager,
            $this->createMock(CustomerRepositoryInterface::class),
            'apiKey',
            'apiSecret',
            'login',
            'password',
            'businessUnitId',
            $this->createMock(ReportingInterface::class)
        );

        $this->trustPilot->setAskReviewTemplate(new AskReviewTemplate(1, '1', TrustPilotService::NAME, 100));

        $this->setProtectedProperty(
            $this->trustPilot,
            'provider',
            $this->createMock(Provider::class)
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testSendOrder(): void
    {
        $mock = new MockHandler([new Response(200)]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $this->setProtectedProperty(
            $this->trustPilot,
            'client',
            $client
        );

        $this->trustPilot->sendOrder($this->order);

        $this->assertTrue($this->order->getReviewEmailSent());
        $this->assertEquals(Order::REVIEW_TYPE_TRUST_PILOT, $this->order->getReviewType());
    }

    /**
     * @throws \ReflectionException
     */
    public function testSendOrderFails(): void
    {
        $mock = new MockHandler([new RequestException('exception', new Request('post', 'test'))]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $this->setProtectedProperty(
            $this->trustPilot,
            'client',
            $client
        );

        $this->trustPilot->sendOrder($this->order);

        $this->assertFalse($this->order->getReviewEmailSent());
    }
}
