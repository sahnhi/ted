<?php

namespace App\Tests\Unit\Services\Cron\Review\Preview;

use App\Services\Cron\Review\Preview\PreviewRssZipPublisher;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class PreviewRssZipPublisherTest extends TestCase
{
    private PreviewRssZipPublisher $previewRssZipPublisher;
    /** @var MessageBusInterface&MockObject */
    private $bus;
    /** @var LoggerInterface&MockObject */
    private $logger;

    public function setUp(): void
    {
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->previewRssZipPublisher = new PreviewRssZipPublisher($this->bus, $this->logger);

        parent::setUp();
    }

    public function testPublish(): void
    {
        $envelope = new Envelope(new \stdClass());
        $this->bus->expects($this->once())->method('dispatch')->willReturn($envelope);
        $this->previewRssZipPublisher->publish();

        $this->assertTrue(true);
    }
}
