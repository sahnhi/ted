<?php

namespace App\Tests\Unit\Business\Command;

use App\Business\Command\PickupCart;
use PHPUnit\Framework\TestCase;

class PickupCartTest extends TestCase
{
    /** @var string */
    private $channelCode;
    /** @var string */
    private $localeCode;
    /** @var array */
    private $body;

    protected function setUp(): void
    {
        parent::setUp();
        $this->channelCode = 'default';
        $this->localeCode = 'fr_FR';
        $this->body = [
            'version' => 'test',
            'user-agent' => 'test',
        ];
    }

    /**
     * @throws \Exception
     */
    public function testChannelCode(): void
    {
        $cart = new PickupCart($this->channelCode, $this->localeCode, $this->body);
        $this->assertEquals($this->channelCode, $cart->channelCode());
    }

    /**
     * @throws \Exception
     */
    public function testLocaleCode(): void
    {
        $cart = new PickupCart($this->channelCode, $this->localeCode, $this->body);
        $this->assertEquals($this->localeCode, $cart->localeCode());
    }

    /**
     * @throws \Exception
     */
    public function testBody(): void
    {
        $cart = new PickupCart($this->channelCode, $this->localeCode, $this->body);
        $this->assertEquals($this->body, $cart->body());
    }

    /**
     * @throws \Exception
     */
    public function testHasFrontDataWithVersionAndUserAgent(): void
    {
        $cart = new PickupCart($this->channelCode, $this->localeCode, $this->body);
        $this->assertTrue($cart->hasFrontData());
    }

    /**
     * @throws \Exception
     */
    public function testHasFrontDataWithoutVersionAndUserAgent(): void
    {
        $body = [
            'version' => null,
            'user-agent' => null,
        ];
        $cart = new PickupCart($this->channelCode, $this->localeCode, $body);
        $this->assertNotTrue($cart->hasFrontData());
    }

    /**
     * @throws \Exception
     */
    public function testHasFrontDataWithVersion(): void
    {
        $body = [
            'version' => 'test',
            'user-agent' => null,
        ];
        $cart = new PickupCart($this->channelCode, $this->localeCode, $body);
        $this->assertTrue($cart->hasFrontData());
    }

    /**
     * @throws \Exception
     */
    public function testHasFrontDataWithUserAgent(): void
    {
        $body = [
            'version' => null,
            'user-agent' => 'test',
        ];
        $cart = new PickupCart($this->channelCode, $this->localeCode, $body);
        $this->assertTrue($cart->hasFrontData());
    }
}
