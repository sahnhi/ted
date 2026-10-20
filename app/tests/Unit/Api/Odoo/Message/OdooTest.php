<?php

namespace App\Tests\Unit\Api\Odoo\Message;

use App\Api\Odoo\Message\Odoo;
use App\Api\Odoo\Model\ObjectMessageOdooInterface;
use PHPUnit\Framework\TestCase;

class OdooTest extends TestCase
{
    /** @var Odoo */
    private $message;

    public function setUpMessage()
    {
        $this->message = new Odoo(
            $this->createMock(ObjectMessageOdooInterface::class),
            'apiCall'
        );
    }

    public function testConstruct(): void
    {
        $this->setUpMessage();
        $this->message->getApiCall();
        $this->message->getSubject();
        $this->message->getMethod();

        $this->assertTrue(true);
    }
}
