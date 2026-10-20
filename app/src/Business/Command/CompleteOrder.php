<?php

namespace App\Business\Command;

use Symfony\Component\HttpFoundation\Request;

final class CompleteOrder
{
    /**
     * @var array
     */
    private $body;

    /**
     * CompleteOrder constructor.
     */
    public function __construct(
        private readonly string $orderToken,
        private readonly string $email,
        private readonly \DateTime $checkoutCompletedAt,
        Request $request
    ) {
        $this->body['version'] = $request->request->get('version') ?: null;
        $this->body['user-agent'] = $request->request->get('user-agent') ?: null;
    }

    public function getOrderToken(): string
    {
        return $this->orderToken;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCheckoutCompletedAt(): \DateTime
    {
        return $this->checkoutCompletedAt;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function hasFrontData(): bool
    {
        if (null !== $this->body['version'] || null !== $this->body['user-agent']) {
            return true;
        }

        return false;
    }
}
