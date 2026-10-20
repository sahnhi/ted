<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\ZipCode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Addressing\Model\Zone as BaseZone;
use Sylius\Component\Addressing\Model\ZoneMember;

#[ORM\Table(name: 'sylius_zone')]
#[ORM\Entity]
class Zone extends BaseZone
{
    #[ORM\ManyToMany(targetEntity: ZipCode::class, mappedBy: 'zone', fetch: 'EXTRA_LAZY')]
    private Collection $zipCode;

    #[ORM\Column(name: 'message', type: 'string', nullable: true)]
    private ?string $message = null;

    #[ORM\OneToMany(mappedBy: 'belongsTo', targetEntity: ZoneMember::class, cascade: ['all'], orphanRemoval: true)]
    protected $members;

    public function __construct()
    {
        parent::__construct();
        $this->zipCode = new ArrayCollection();
    }

    /**
     * @return Collection<ZipCode>
     */
    public function getZipCode(): Collection
    {
        return $this->zipCode;
    }

    public function addZipCode(ZipCode $zipCode): void
    {
        if (!$this->zipCode->contains($zipCode)) {
            $this->zipCode->add($zipCode);
            $zipCode->addZone($this);
        }
    }

    public function removeZipCode(ZipCode $zipCode): void
    {
        if ($this->zipCode->contains($zipCode)) {
            $this->zipCode->removeElement($zipCode);
            $zipCode->removeZone($this);
        }
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }
}
