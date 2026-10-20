<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Repository\QualityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sylius\Component\Core\Model\AdminUser as BaseAdminUser;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\ImagesAwareInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'quality')]
#[ORM\Entity(repositoryClass: QualityRepository::class)]
class Quality implements ResourceInterface, ImagesAwareInterface
{
    public const SUFFIX = 'FQ';
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REFUSED = 'refused';

    public const STATUS = [
        self::STATUS_NEW,
        self::STATUS_IN_PROGRESS,
        self::STATUS_ACCEPTED,
        self::STATUS_REFUSED,
    ];

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'status', type: 'string')]
    private ?string $status = self::STATUS_NEW;

    #[ORM\Column(name: 'validation_required', type: 'boolean')]
    private bool $validationRequired = true;

    #[ORM\Column(name: 'title', type: 'string')]
    private ?string $title = null;

    #[ORM\Column(name: 'number', type: 'string', unique: true)]
    private ?string $number = null;

    /**
     * @Assert\NotNull()
     */
    #[ORM\Column(name: 'sav_comment', type: 'text')]
    private ?string $savComment = null;

    #[ORM\Column(name: 'quality_comment', type: 'text', nullable: true)]
    private ?string $qualityComment = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\JoinColumn(name: 'order_item_logistic_id', referencedColumnName: 'id', unique: true)]
    #[ORM\OneToOne(targetEntity: OrderItemLogistic::class, inversedBy: 'quality')]
    private ?OrderItemLogistic $orderItemLogistic = null;

    #[ORM\OneToMany(targetEntity: QualityImage::class, mappedBy: 'owner', cascade: ['all'], orphanRemoval: true)]
    private ?Collection $images;

    #[ORM\JoinColumn(name: 'reason_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Reason::class, inversedBy: 'quality')]
    private ?Reason $reason = null;

    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: BaseAdminUser::class)]
    private ?BaseAdminUser $createdBy = null;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderItemLogistic(): ?OrderItemLogistic
    {
        return $this->orderItemLogistic;
    }

    public function setOrderItemLogistic(?OrderItemLogistic $orderItemLogistic): Quality
    {
        $this->orderItemLogistic = $orderItemLogistic;

        return $this;
    }

    public function getReason(): ?Reason
    {
        return $this->reason;
    }

    public function setReason(Reason $reason): Quality
    {
        $this->reason = $reason;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): Quality
    {
        if (!in_array($status, self::STATUS)) {
            throw new \Exception('Invalid status');
        }
        $this->status = $status;

        return $this;
    }

    public function isValidationRequired(): bool
    {
        return $this->validationRequired;
    }

    public function setValidationRequired(bool $validationRequired): Quality
    {
        $this->validationRequired = $validationRequired;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): Quality
    {
        $this->title = $title;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): Quality
    {
        $this->number = $number;

        return $this;
    }

    public function getSavComment(): ?string
    {
        return $this->savComment;
    }

    public function setSavComment(?string $savComment): Quality
    {
        $this->savComment = $savComment;

        return $this;
    }

    public function getQualityComment(): ?string
    {
        return $this->qualityComment;
    }

    public function setQualityComment(?string $qualityComment): Quality
    {
        $this->qualityComment = $qualityComment;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): Quality
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function getImagesByType(string $type): Collection
    {
        return $this->images->filter(fn (ImageInterface $image): bool => $type === $image->getType());
    }

    public function hasImages(): bool
    {
        return !$this->images->isEmpty();
    }

    public function hasImage(ImageInterface $image): bool
    {
        return $this->images->contains($image);
    }

    public function addImage(ImageInterface $image): void
    {
        $image->setOwner($this);
        $this->images->add($image);
    }

    public function removeImage(ImageInterface $image): void
    {
        if ($this->hasImage($image)) {
            $image->setOwner(null);
            $this->images->removeElement($image);
        }
    }

    public function getCreatedBy(): ?BaseAdminUser
    {
        return $this->createdBy;
    }

    public function setCreatedBy(BaseAdminUser $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getCode(): string
    {
        return $this->orderItemLogistic
            ->getOrderItem()
            ->getOrder()
            ->getShippingAddress()
            ->getCountryCode();
    }
}
