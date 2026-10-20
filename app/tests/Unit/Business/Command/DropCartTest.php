<?php

namespace App\Tests\Unit\Business\Command;

use App\Business\Command\DropCart;
use PHPUnit\Framework\TestCase;

class DropCartTest extends TestCase
{
    public function testOrderToken(): void
    {
        $dropCart = new DropCart('test');
        $this->assertEquals('test', $dropCart->orderToken());
    }
}
