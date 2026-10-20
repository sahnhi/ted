<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\SatellitePages;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;

#[ORM\Table(name: 'app_satellite_page_technical_details')]
#[ORM\Entity]
class TechnicalDetail implements ResourceInterface, TranslatableInterface
{
    use TranslatableTrait {
        TranslatableTrait::__construct as private initializeTranslationsCollection;
    }

    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: TechnicalInfo::class, inversedBy: 'technicalDetails')]
    private TechnicalInfo $technicalInfo;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        /** @var TechnicalDetailTranslation $translation */
        $translation = $this->getTranslation();

        return $translation->getTitle();
    }

    public function setTitle(string $title): self
    {
        /** @var TechnicalDetailTranslation $translation */
        $translation = $this->getTranslation();
        $translation->setTitle($title);

        return $this;
    }

    public function getDescription(): ?string
    {
        /** @var TechnicalDetailTranslation $translation */
        $translation = $this->getTranslation();

        return $translation->getDescription();
    }

    public function setDescription(?string $description): self
    {
        /** @var TechnicalDetailTranslation $translation */
        $translation = $this->getTranslation();
        $translation->setDescription($description);

        return $this;
    }

    public function getTechnicalInfo(): TechnicalInfo
    {
        return $this->technicalInfo;
    }

    public function setTechnicalInfo(TechnicalInfo $technicalInfo): self
    {
        $this->technicalInfo = $technicalInfo;

        return $this;
    }

    protected function createTranslation(): TranslationInterface
    {
        return new TechnicalDetailTranslation();
    }
}
