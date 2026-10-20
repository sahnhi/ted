<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\SatellitePages;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;

#[ORM\Table(name: 'app_satellite_page_technical_info')]
#[ORM\Entity]
class TechnicalInfo implements ResourceInterface, TranslatableInterface
{
    use TimestampableEntity;

    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToMany(targetEntity: TechnicalDetail::class, mappedBy: 'technicalInfo', cascade: ['all'])]
    private Collection $technicalDetails;

    #[ORM\ManyToOne(targetEntity: SatellitePage::class, inversedBy: 'technicalInfos')]
    private ?SatellitePage $satellitePage = null;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
        $this->technicalDetails = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        /**
         * @var TechnicalInfoTranslation $translation
         */
        $translation = $this->getTranslation();

        return $translation->getTitle();
    }

    public function setTitle(string $title): self
    {
        /**
         * @var TechnicalInfoTranslation $translation
         */
        $translation = $this->getTranslation();

        $translation->setTitle($title);

        return $this;
    }

    public function getTechnicalDetails(): Collection
    {
        return $this->technicalDetails;
    }

    public function addTechnicalDetail(TechnicalDetail $technicalDetail): self
    {
        if (!$this->hasTechnicalDetail($technicalDetail)) {
            $technicalDetail->setTechnicalInfo($this);
            $this->technicalDetails->add($technicalDetail);
        }

        return $this;
    }

    public function removeTechnicalDetail(TechnicalDetail $technicalDetail): self
    {
        if ($this->hasTechnicalDetail($technicalDetail)) {
            $this->technicalDetails->removeElement($technicalDetail);
        }

        return $this;
    }

    public function hasTechnicalDetail(TechnicalDetail $technicalDetail): bool
    {
        return $this->technicalDetails->contains($technicalDetail);
    }

    protected function createTranslation(): TranslationInterface
    {
        return new TechnicalInfoTranslation();
    }

    public function getSatellitePage(): SatellitePage
    {
        return $this->satellitePage;
    }

    public function setSatellitePage(?SatellitePage $satellitePage): self
    {
        $this->satellitePage = $satellitePage;

        return $this;
    }
}
