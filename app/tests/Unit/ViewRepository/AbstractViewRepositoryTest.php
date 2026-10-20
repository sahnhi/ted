<?php

namespace App\Tests\Unit\ViewRepository;

use App\Services\Channel\ChannelCode;
use App\Tests\TestCase;
use App\ViewRepository\AbstractViewRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Bundle\ChannelBundle\Doctrine\ORM\ChannelRepository;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Locale\Model\Locale;
use Webmozart\Assert\InvalidArgumentException;

class AbstractViewRepositoryTest extends TestCase
{
    /** @var Locale */
    private $locale;
    /** @var Channel */
    private $channel;

    /** @var MockObject|ChannelRepository */
    private $channelRepository;

    /** @var AbstractViewRepository|MockObject */
    private $viewRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->locale = new Locale();
        $this->locale->setCode(self::LOCALE_CODE);
        $this->channel = new Channel();
        $this->channel->setCode(ChannelCode::CHANNEL_WEB);
        $this->channel->setDefaultLocale($this->locale);
        $this->channel->addLocale($this->locale);

        $this->channelRepository = $this->createMock(ChannelRepository::class);
    }

    private function setUpObject()
    {
        $this->viewRepository = $this->getMockBuilder(AbstractViewRepository::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->viewRepository->setChannelRepository($this->channelRepository);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetChannel(): void
    {
        $this->channelRepository
            ->method('findOneByCode')
            ->willReturn($this->channel);
        $this->setUpObject();

        $channel = $this->invokeMethod(
            $this->viewRepository,
            'getChannel',
            [ChannelCode::CHANNEL_WEB]
        );

        $this->assertEquals($this->channel, $channel);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetChannelNotFound(): void
    {
        $this->channelRepository
            ->method('findOneByCode')
            ->willReturn(null);
        $this->setUpObject();

        $this->expectException(InvalidArgumentException::class);
        $channel = $this->invokeMethod(
            $this->viewRepository,
            'getChannel',
            [ChannelCode::CHANNEL_WEB]
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetLocaleCode(): void
    {
        $this->setUpObject();

        $localeCode = $this->invokeMethod(
            $this->viewRepository,
            'getLocaleCode',
            [$this->channel]
        );

        $this->assertEquals($this->locale->getCode(), $localeCode);
    }

    /**
     * @throws \ReflectionException
     */
    public function testAssertLocaleSupport(): void
    {
        $this->setUpObject();

        $locale = new Locale();
        $locale->setCode(self::LOCALE_CODE);

        $result = $this->invokeMethod(
            $this->viewRepository,
            'assertLocaleSupport',
            [$this->locale->getCode(), $this->channel->getLocales()]
        );

        $this->assertTrue(true);
    }

    /**
     * @throws \ReflectionException
     */
    public function testAssertLocaleSupportDoesNotSupport(): void
    {
        $this->setUpObject();

        $this->expectException(InvalidArgumentException::class);
        $result = $this->invokeMethod(
            $this->viewRepository,
            'assertLocaleSupport',
            [$this->locale->getCode(), []]
        );
    }
}
