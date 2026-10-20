<?php

declare(strict_types=1);

namespace App\Analytic\Model;

use Webmozart\Assert\Assert;

final class User
{
    private bool $authenticated = true;
    private string $timeZone = 'Europe/Paris';
    private readonly string $country;
    private bool $userCentricConsent = false;
    private readonly string $email;

    public function __construct(
        private readonly string $externalId,
        private readonly \DateTimeInterface $createdAt,
        private readonly \DateTimeInterface $signedUpAt,
        private readonly string $language,
        string $country,
        private readonly ?string $firstName,
        private readonly ?string $lastName,
        string $email,
        private readonly ?\DateTimeInterface $birthday = null,
        private readonly ?string $phoneNumber = null,
        private readonly ?string $addressLine = null,
        private readonly ?string $city = null,
        private readonly ?string $postalCode = null
    ) {
        Assert::email($email);
        $this->country = trim($country);
        $this->email = trim($email);
    }

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'created_at' => $this->createdAt->format(DATE_ATOM),
            'is_authenticated' => $this->authenticated,
            'signed_up_at' => $this->signedUpAt->format(DATE_ATOM),
            'timezone' => $this->nullifyEmptyString($this->timeZone),
            'language' => $this->nullifyEmptyString($this->language),
            'country' => $this->nullifyEmptyString($this->country),
            'user_centric_consent' => $this->userCentricConsent,
            'first_name' => is_string($this->firstName) ? $this->nullifyEmptyString($this->firstName) : null,
            'last_name' => is_string($this->lastName) ? $this->nullifyEmptyString($this->lastName) : null,
            'email' => $this->nullifyEmptyString($this->email),
            'telephone' => is_string($this->phoneNumber) ? $this->nullifyEmptyString($this->phoneNumber) : null,
            'birthday' => $this->birthday instanceof \DateTimeInterface ? $this->birthday->format('Y-m-d') : null,
            'address_line_1' => is_string($this->addressLine) ? $this->nullifyEmptyString($this->addressLine) : null,
            'city' => is_string($this->city) ? $this->nullifyEmptyString($this->city) : null,
            'postal_code' => is_string($this->postalCode) ? $this->nullifyEmptyString($this->postalCode) : null,
            'state' => $this->nullifyEmptyString($this->country),
        ];
    }

    public function getId(): string
    {
        return $this->externalId;
    }

    private function nullifyEmptyString(string $toNullify): ?string
    {
        return !empty(trim($toNullify)) ? $toNullify : null;
    }
}
