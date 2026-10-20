<?php

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Webmozart\Assert\Assert;

#[ORM\Table(name: 'notification')]
#[ORM\Entity]
class Notification
{
    public const ADYEN_PAYMENT_REPORT = 'AdyenPaymentReport';
    public const SHIPUP_TRACK_EVENT = 'ShipupTrackEvent';

    private const NOTIFICATION_TYPES = [
        self::ADYEN_PAYMENT_REPORT,
        self::SHIPUP_TRACK_EVENT,
    ];

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'type', type: 'string', nullable: false)]
    private ?string $type = null;

    public function __construct(
        string $type,
        #[ORM\Column(name: 'content', type: 'text', nullable: false)]
        private ?string $content,
        #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
        #[Gedmo\Timestampable(on: 'create')]
        private ?\DateTimeInterface $createdAt
    ) {
        Assert::oneOf($type, self::NOTIFICATION_TYPES);
        $this->type = $type;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
