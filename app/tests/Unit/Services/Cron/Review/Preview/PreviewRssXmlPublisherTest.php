<?php

namespace App\Tests\Unit\Services\Cron\Review\Preview;

use App\Doctrine\Repository\Sylius\ProductReviewRepositoryInterface;
use App\Services\Cron\Review\Preview\PreviewRssXmlPublisher;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class PreviewRssXmlPublisherTest extends TestCase
{
    /** @var ProductReviewRepositoryInterface&MockObject */
    private $productReviewRepository;
    private PreviewRssXmlPublisher $previewRssXmlPublisher;
    /** @var MessageBusInterface&MockObject */
    private $bus;
    /** @var LoggerInterface&MockObject */
    private $logger;

    public function setUp(): void
    {
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->productReviewRepository = $this->createMock(ProductReviewRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->previewRssXmlPublisher = new PreviewRssXmlPublisher($this->bus, $this->productReviewRepository, $this->logger);

        parent::setUp();
    }

    /**
     * @dataProvider publishDataProvider
     */
    public function testPublish(int $reviewsCount, int $expectedDispatchCall): void
    {
        $this->productReviewRepository->method('countRssReview')->willReturn($reviewsCount);
        $envelope = new Envelope(new \stdClass());
        $this->bus->expects($this->exactly($expectedDispatchCall))->method('dispatch')->willReturn($envelope);
        $this->previewRssXmlPublisher->publish(0, $reviewsCount);

        $this->assertTrue(true);
    }

    public function publishDataProvider(): array
    {
        return [
            [
                'reviewsCount' => 2001,
                'expectedDispatchCall' => 1,
            ],
            [
                'reviewsCount' => 2000,
                'expectedDispatchCall' => 1,
            ],
        ];
    }
}
