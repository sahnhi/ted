<?php

declare(strict_types=1);

namespace App\Tests\Unit\Utils;

use App\Utils\Url;
use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UrlTest extends TestCase
{
    public function testValidUrl(): void
    {
        $url = new Url('https://www.tediber.fr');

        $this->assertEquals('https://www.tediber.fr', (string) $url);
    }

    public function testInvalidUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "www.tediber.fr" was expected to be a valid URL starting with http or https');

        $url = new Url('www.tediber.fr');
    }
}
