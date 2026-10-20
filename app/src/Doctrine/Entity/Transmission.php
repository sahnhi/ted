<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Repository\TransmissionRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

#[ORM\Table(name: 'erp_transmission')]
#[ORM\Entity(repositoryClass: TransmissionRepository::class)]
class Transmission implements ResourceInterface
{
    use TimestampableTrait;

    public const STATE_TRANSMITTED = 'transmitted';
    public const STATE_NOT_TRANSMITTED = 'not transmitted';
    public const STATE_FAILED = 'error transmission';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'tablename_seq', allocationSize: 100, initialValue: 1)]
    private ?int $id = null;

    #[ORM\Column(name: 'transmission_state', type: 'string', nullable: false)]
    private string $transmissionState = self::STATE_TRANSMITTED;

    #[ORM\Column(name: 'root_folder_warehouse', type: 'string', nullable: true)]
    private ?string $rootFolderWarehouse = null;

    #[ORM\Column(name: 'root_folder_shipping_method', type: 'string', nullable: true)]
    private ?string $rootFolderShippingMethod = null;

    #[ORM\JoinColumn(name: 'shipping_method_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ShippingMethod::class)]
    private ?ShippingMethod $shippingMethod = null;

    #[ORM\JoinColumn(name: 'warehouse_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Warehouse::class)]
    private ?Warehouse $warehouse = null;

    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Order::class)]
    private ?Order $order = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Timestampable(on: 'create')]
    protected $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    #[Timestampable(on: 'update')]
    protected $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getShippingMethod(): ShippingMethod
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(ShippingMethod $shippingMethod): void
    {
        $this->shippingMethod = $shippingMethod;
    }

    public function getWarehouse(): Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Warehouse $warehouse): void
    {
        $this->warehouse = $warehouse;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    public function getTransmissionState(): ?string
    {
        return $this->transmissionState;
    }

    public function setTransmissionState(string $transmissionState): void
    {
        $this->transmissionState = $transmissionState;
    }

    public function getRootFolderWarehouse(): ?string
    {
        return $this->rootFolderWarehouse;
    }

    public function setRootFolderWarehouse(?string $rootFolderWarehouse): void
    {
        $this->rootFolderWarehouse = $rootFolderWarehouse;
    }

    public function getRootFolderShippingMethod(): ?string
    {
        return $this->rootFolderShippingMethod;
    }

    public function setRootFolderShippingMethod(string $rootFolderShippingMethod): void
    {
        $this->rootFolderShippingMethod = $rootFolderShippingMethod;
    }
}
