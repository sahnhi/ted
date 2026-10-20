<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\ProductVariant;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sylius\Component\Core\Model\AdminUser;
use Sylius\Component\Core\Model\AdminUser as BaseAdminUser;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_order_reason')]
#[ORM\Entity]
class OrderReason implements ResourceInterface
{
    public const COMMERCIAL_OFFER_ID = 73;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'quantity', type: 'integer', nullable: true)]
    private ?int $quantity = null;

    #[ORM\Column(name: 'comment', type: 'string', nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'isProblem', type: 'boolean', nullable: false)]
    private bool $isProblem = false;

    #[ORM\JoinColumn(name: 'reason_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Reason::class)]
    private ?Reason $reason = null;

    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'cascade')]
    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'orderReason', cascade: ['persist'])]
    private ?Order $order = null;

    #[ORM\JoinColumn(name: 'commercialOffer_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Reason::class)]
    private ?Reason $commercialOffer = null;

    #[ORM\JoinColumn(name: 'variant_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductVariant::class)]
    private ?ProductVariant $variant = null;

    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: AdminUser::class)]
    private ?BaseAdminUser $createdBy = null;

    #[ORM\Column(name: 'customer_comment', type: 'string', nullable: true)]
    private ?string $customerComment = null;

    public function getIsProblem(): ?bool
    {
        return $this->isProblem;
    }

    public function setIsProblem(?bool $isProblem): void
    {
        $this->isProblem = $isProblem;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    public function getVariant(): ?ProductVariant
    {
        return $this->variant;
    }

    public function setVariant(ProductVariant $variant): void
    {
        $this->variant = $variant;
    }

    public function getReason(): ?Reason
    {
        return $this->reason;
    }

    public function setReason(?Reason $reason): void
    {
        $this->reason = $reason;
    }

    public function getCommercialOffer(): ?Reason
    {
        return $this->commercialOffer;
    }

    public function setCommercialOffer(Reason $commercialOffer): void
    {
        $this->commercialOffer = $commercialOffer;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedBy(): ?BaseAdminUser
    {
        return $this->createdBy;
    }

    public function setCreatedBy(BaseAdminUser $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getCustomerComment(): ?string
    {
        return $this->customerComment;
    }

    public function setCustomerComment(?string $customerComment): void
    {
        $this->customerComment = $customerComment;
    }

    public function getProductName(): ?string
    {
        return $this->variant?->getProduct()->getName();
    }
}
