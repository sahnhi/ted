<?php

namespace App\Model;

use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

final class GiftCardAddress
{
    private function __construct(
        private readonly string $emailBuyer,
        private readonly string $emailRecipient,
        private readonly string $nameBuyer,
        private readonly string $nameRecipient,
        private readonly string $message
    ) {
    }

    /**
     * @return Address
     */
    public static function createFromArray(array $address): self
    {
        Assert::keyExists($address, 'emailBuyer');
        Assert::keyExists($address, 'emailRecipient');
        Assert::keyExists($address, 'nameBuyer');
        Assert::keyExists($address, 'nameRecipient');
        Assert::keyExists($address, 'message');

        return new self(
            $address['emailBuyer'],
            $address['emailRecipient'],
            $address['nameBuyer'],
            $address['nameRecipient'],
            $address['message'] ?? null
        );
    }

    /**
     * @return Address
     */
    public static function createFromRequest(Request $request): self
    {
        return new self(
            $request->request->get('emailBuyer'),
            $request->request->get('emailRecipient'),
            $request->request->get('nameBuyer'),
            $request->request->get('nameRecipient'),
            $request->request->get('message')
        );
    }

    public function emailBuyer(): string
    {
        return $this->emailBuyer;
    }

    public function emailRecipient(): string
    {
        return $this->emailRecipient;
    }

    public function nameBuyer(): string
    {
        return $this->nameBuyer;
    }

    public function nameRecipient(): string
    {
        return $this->nameRecipient;
    }

    public function message(): string
    {
        return $this->message;
    }
}
