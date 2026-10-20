<?php

declare(strict_types=1);

namespace App\Business\Command;

use App\Doctrine\Entity\Seller;
use App\Doctrine\Entity\Store;
use App\Doctrine\Entity\Sylius\Address;
use Sylius\Component\Core\Model\OrderInterface;

class Quotation
{
    private Store $store;

    private Seller $seller;

    private string $email;

    private ?Address $address = null;

    private string|null $phoneNumber = null;

    private string|null $lastName = null;

    private string|null $firstName = null;

    public function __construct(
        private OrderInterface $cart
    ) {
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(Store $store): void
    {
        $this->store = $store;
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function setSeller(Seller $seller): void
    {
        $this->seller = $seller;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getCart(): OrderInterface
    {
        return $this->cart;
    }

    public function setCart(OrderInterface $cart): void
    {
        $this->cart = $cart;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }
}
