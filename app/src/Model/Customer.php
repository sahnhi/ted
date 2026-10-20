<?php

namespace App\Model;

use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

final class Customer
{
    private function __construct(
        private readonly string $email,
        private readonly ?string $phoneNumber = null,
        private readonly ?string $firstName = null,
        private readonly ?string $lastName = null,
        private readonly ?bool $newsletter = false,
        private readonly ?bool $optin = null,
        private readonly ?bool $optinPhone = false
    ) {
    }

    public static function createFromArray(array $customer): self
    {
        Assert::keyExists($customer, 'email');

        return new self(
            $customer['email'],
            $customer['phoneNumber'] ?? null,
            $customer['firstName'] ?? null,
            $customer['lastName'] ?? null,
            $customer['newsletter'] ?? false,
            $customer['optin'] ?? null,
            $customer['optinPhone'] ?? false,
        );
    }

    public static function createFromRequest(Request $request): self
    {
        return new self(
            $request->request->get('email'),
            $request->request->get('phoneNumber'),
            $request->request->get('firstName'),
            $request->request->get('lastName'),
            $request->request->get('newsletter'),
            $request->request->get('optin') ?? null,
            $request->request->get('optinPhone') ?? false
        );
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function phoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function firstName(): ?string
    {
        return $this->firstName;
    }

    public function lastName(): ?string
    {
        return $this->lastName;
    }

    public function newsletter(): ?bool
    {
        return $this->newsletter;
    }

    public function optin(): ?bool
    {
        return $this->optin;
    }

    public function optinPhone(): ?bool
    {
        return $this->optinPhone;
    }
}
