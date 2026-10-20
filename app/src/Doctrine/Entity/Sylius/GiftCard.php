<?php

namespace App\Doctrine\Entity\Sylius;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusGiftCardPlugin\Doctrine\ORM\GiftCardRepository;
use Setono\SyliusGiftCardPlugin\Model\GiftCard as BaseGiftCard;

#[ORM\Table(name: 'setono_sylius_gift_card__gift_card')]
#[ORM\Entity(repositoryClass: GiftCardRepository::class)]
class GiftCard extends BaseGiftCard
{
    #[ORM\Column(name: 'email_buyer', type: 'string', nullable: true)]
    private ?string $emailBuyer = null;

    #[ORM\Column(name: 'email_recipient', type: 'string', nullable: true)]
    private ?string $emailRecipient = null;

    #[ORM\Column(name: 'name_buyer', type: 'string', nullable: true)]
    private ?string $nameBuyer = null;

    #[ORM\Column(name: 'name_recipient', type: 'string', nullable: true)]
    private ?string $nameRecipient = null;

    #[ORM\Column(name: 'message', type: 'text', nullable: true)]
    private ?string $message = null;

    private int $refundAmount = 0;

    public function getEmailBuyer(): ?string
    {
        return $this->emailBuyer;
    }

    public function setEmailBuyer(?string $emailBuyer): void
    {
        $this->emailBuyer = $emailBuyer;
    }

    public function getEmailRecipient(): ?string
    {
        return $this->emailRecipient;
    }

    public function setEmailRecipient(?string $emailRecipient): void
    {
        $this->emailRecipient = $emailRecipient;
    }

    public function getNameBuyer(): ?string
    {
        return $this->nameBuyer;
    }

    public function setNameBuyer(?string $nameBuyer): void
    {
        $this->nameBuyer = $nameBuyer;
    }

    public function getNameRecipient(): ?string
    {
        return $this->nameRecipient;
    }

    public function setNameRecipient(?string $nameRecipient): void
    {
        $this->nameRecipient = $nameRecipient;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getRefundAmount(): int
    {
        return $this->refundAmount;
    }

    public function setRefundAmount(int $refundAmount): void
    {
        $this->refundAmount = $refundAmount;
    }
}
