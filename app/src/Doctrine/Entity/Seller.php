<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Repository\SellerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_seller')]
#[ORM\Entity(repositoryClass: SellerRepository::class)]
class Seller implements ResourceInterface
{
    public const OTHERS_COMMISSION = '300';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', nullable: false)]
    private ?string $name = null;

    #[ORM\JoinColumn(name: 'store_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Store::class)]
    private ?Store $store = null;

    #[ORM\Column(name: 'email', type: 'string', nullable: true)]
    private ?string $email = null;

    #[ORM\OneToMany(targetEntity: OrderStore::class, mappedBy: 'seller')]
    private ?Collection $orderStore;

    public function __construct()
    {
        $this->orderStore = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store = null): void
    {
        $this->store = $store;
    }

    public function getOrderStore(): Collection
    {
        return $this->orderStore;
    }

    public function addOrderStore(OrderStore $orderStore): void
    {
        $this->orderStore[] = $orderStore;
    }

    public function removeOrderStore(OrderStore $orderStore): void
    {
        $this->orderStore->removeElement($orderStore);
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
}
