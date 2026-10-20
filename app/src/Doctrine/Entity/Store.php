<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Repository\StoreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_store')]
#[ORM\Entity(repositoryClass: StoreRepository::class)]
class Store implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', nullable: false)]
    private ?string $name = null;

    #[ORM\Column(name: 'company', type: 'string', nullable: false)]
    private ?string $company = null;

    #[ORM\Column(name: 'terminal_id', type: 'integer', nullable: false)]
    private ?int $terminalId = null;

    #[ORM\JoinColumn(name: 'channel_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Channel::class)]
    private ?Channel $channel = null;

    #[ORM\Column(name: 'location', type: 'string', nullable: true)]
    private ?string $location = null;

    #[ORM\Column(name: 'phoneNumber', type: 'string', nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\OneToMany(targetEntity: OrderStore::class, mappedBy: 'store')]
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

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    public function getTerminalId(): ?int
    {
        return $this->terminalId;
    }

    public function setTerminalId(int $terminalId): void
    {
        $this->terminalId = $terminalId;
    }

    public function getChannel(): ?Channel
    {
        return $this->channel;
    }

    public function setChannel(Channel $channel): void
    {
        $this->channel = $channel;
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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }
}
