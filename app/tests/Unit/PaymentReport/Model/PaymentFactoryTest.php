<?php

declare(strict_types=1);

namespace App\Tests\Unit\PaymentReport\Model;

use App\PaymentReport\Model\PaymentFactory;
use PHPUnit\Framework\TestCase;

final class PaymentFactoryTest extends TestCase
{
    /**
     * @dataProvider fullNameParsingDataProvider
     */
    public function testValidPayment(string $fullName, ?string $firstName, string $lastName): void
    {
        $data = [
            'Merchant Reference' => '000470927-1117400',
            'Shopper Name' => $fullName,
            'Main Amount' => 123.45,
            'Booking Date' => '2022-12-01 01:07:21',
            'Psp Reference' => 'MP8D9GGLMFRFX632',
            'Record Type' => 'Received',
        ];

        $payment = PaymentFactory::create($data);

        $this->assertEquals($data['Merchant Reference'], $payment->getMerchantReference());
        $this->assertEquals(new \DateTimeImmutable($data['Booking Date']), $payment->getCreatedAt());
        $this->assertEquals($data['Psp Reference'], $payment->getPspReference());
        $this->assertEquals($data['Record Type'], $payment->getResultCode());
        $this->assertEquals(12345, $payment->getAmount());

        $this->assertEquals($firstName, $payment->getFirstName());
        $this->assertEquals($lastName, $payment->getLastName());
    }

    public function fullNameParsingDataProvider(): \Generator
    {
        yield 'one white space' => ['fullName' => 'Aurelie Tong', 'firstName' => 'aurelie', 'lastName' => 'tong'];
        yield 'single name' => ['fullName' => 'Aurelie', 'firstName' => null, 'lastName' => 'aurelie'];
        yield 'single slash separator' => ['fullName' => 'Aurelie/Tong', 'firstName' => 'aurelie', 'lastName' => 'tong'];
        yield 'single slash separator with multiple white space' => ['fullName' => 'Aurelie / Tong ', 'firstName' => 'aurelie', 'lastName' => 'tong'];
        yield 'single slash separator with one white space' => ['fullName' => 'Marie Aurelie/Tong ', 'firstName' => 'marie aurelie', 'lastName' => 'tong'];
        yield 'prefix "m. " with slash' => ['fullName' => 'm. Damien / Tong ', 'firstName' => 'damien', 'lastName' => 'tong'];
        yield 'prefix "mr. " with slash' => ['fullName' => 'mr. Damien / Tong ', 'firstName' => 'damien', 'lastName' => 'tong'];
        yield 'prefix "mme. " with slash' => ['fullName' => 'mme. Aurelie / Tong ', 'firstName' => 'aurelie', 'lastName' => 'tong'];
        yield 'prefix "mle. " with slash' => ['fullName' => 'mle. Aurelie / Tong ', 'firstName' => 'aurelie', 'lastName' => 'tong'];
        yield 'prefix "mlle. " with slash' => ['fullName' => 'mlle. Aurelie / Tong ', 'firstName' => 'aurelie', 'lastName' => 'tong'];
        yield 'suffix ".m" with slash' => ['fullName' => ' Damien / Tong .m', 'firstName' => 'damien', 'lastName' => 'tong'];
        yield 'suffix ".mr" with slash' => ['fullName' => 'Damien / Tong.mr ', 'firstName' => 'damien', 'lastName' => 'tong'];
        yield 'suffix ".mme" with slash' => ['fullName' => 'Aurelie / Tong .mme ', 'firstName' => 'aurelie', 'lastName' => 'tong'];
        yield 'suffix ".mle" with slash' => ['fullName' => 'Aurelie / Tong .mle', 'firstName' => 'aurelie', 'lastName' => 'tong'];
        yield 'suffix ".mlle" with slash' => ['fullName' => 'Aurelie / Tong .mlle', 'firstName' => 'aurelie', 'lastName' => 'tong'];
        yield 'prefix and suffix with slash' => ['fullName' => 'mle.Aurelie / Tong .mlle', 'firstName' => 'aurelie', 'lastName' => 'tong'];
    }
}
