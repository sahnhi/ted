<?php

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_order_return_status')]
#[ORM\Entity]
class OrderReturnStatus implements ResourceInterface, \Stringable
{
    public const TYPE_SAV = 1;
    public const TYPE_WAREHOUSE = 2;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'statusType', type: 'integer', nullable: false)]
    private ?int $statusType = null;

    #[ORM\Column(name: 'savStatus', type: 'string', nullable: true)]
    private ?string $savStatus = null;

    #[ORM\Column(name: 'warehouseStatus', type: 'string', nullable: true)]
    private ?string $warehouseStatus = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return (string) ($this->getSavStatus() ?? $this->getWarehouseStatus());
    }

    public function getSavStatus(): ?string
    {
        return $this->savStatus;
    }

    public function setSavStatus(string $savStatus): void
    {
        $this->savStatus = $savStatus;
    }

    public function getWarehouseStatus(): ?string
    {
        return $this->warehouseStatus;
    }

    public function setWarehouseStatus(string $warehouseStatus): void
    {
        $this->warehouseStatus = $warehouseStatus;
    }

    public function getStatusType(): ?int
    {
        return $this->statusType;
    }

    public function setStatusType(int $statusType): void
    {
        $this->statusType = $statusType;
    }
}
