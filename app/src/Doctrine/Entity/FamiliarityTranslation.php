<?php

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\AbstractTranslation;

#[ORM\Table(name: 'sylius_familiarity_translation')]
#[ORM\Entity]
class FamiliarityTranslation extends AbstractTranslation implements FamiliarityTranslationInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'tablename_seq', allocationSize: 100, initialValue: 1)]
    private ?int $id = null;

    #[ORM\Column(name: 'wording', type: 'string', nullable: false)]
    private ?string $wording = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWording(): ?string
    {
        return $this->wording;
    }

    public function setWording(?string $wording): void
    {
        $this->wording = $wording;
    }
}
