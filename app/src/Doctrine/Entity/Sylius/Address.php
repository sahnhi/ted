<?php
/**
 * Created by PhpStorm.
 * User: guillaume
 * Date: 11/10/18
 * Time: 11:06.
 */

namespace App\Doctrine\Entity\Sylius;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Address as BaseAddress;
use Sylius\Component\Core\Model\AddressInterface;

#[ORM\Entity()]
#[ORM\Table(name: 'sylius_address')]
class Address extends BaseAddress implements AddressInterface
{
    #[ORM\Column(name: 'details', type: 'string', length: 255, nullable: true)]
    private ?string $details = null;
    #[ORM\Column(name: 'tva', type: 'string', length: 100, nullable: true)]
    private ?string $tva = null;

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): void
    {
        $this->details = $details;
    }

    public function getTva(): ?string
    {
        return $this->tva;
    }

    public function setTva(?string $tva): void
    {
        $this->tva = $tva;
    }
}
