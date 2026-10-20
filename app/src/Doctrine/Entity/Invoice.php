<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\InvoiceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'invoice')]
#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice implements InvoiceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(name: 'ref', type: 'string')]
        private string $ref,
        #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'cascade')]
        #[ORM\ManyToOne(targetEntity: Order::class, cascade: ['persist'], inversedBy: 'invoice')]
        private Order $order,
        #[ORM\Column(name: 'file_name', type: 'string')]
        private string $fileName
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRef(): string
    {
        return $this->ref;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}
