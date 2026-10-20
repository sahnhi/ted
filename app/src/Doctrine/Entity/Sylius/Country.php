<?php

namespace App\Doctrine\Entity\Sylius;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Addressing\Model\Country as BaseCountry;
use Sylius\Component\Addressing\Model\CountryInterface;

#[ORM\Table(name: 'sylius_country')]
#[ORM\Entity]
class Country extends BaseCountry implements CountryInterface
{
    #[ORM\Column(name: 'paid_return', type: 'boolean')]
    private ?bool $paidReturn = null;

    public function isPaidReturn(): ?bool
    {
        return $this->paidReturn;
    }

    public function setPaidReturn(bool $paidReturn): void
    {
        $this->paidReturn = $paidReturn;
    }
}
