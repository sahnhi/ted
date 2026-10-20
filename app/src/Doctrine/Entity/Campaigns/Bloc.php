<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Campaigns;

use App\Doctrine\Entity\Campaigns\Bloc\CampaignBlocDisplay;
use App\Doctrine\Entity\Campaigns\Bloc\Focus;
use App\Doctrine\Entity\Campaigns\Bloc\Variant;
use App\Doctrine\Entity\Campaigns\Trait\CampaignBlocDisplayTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Entity]
#[ORM\Table(name: 'app_campaigns_blocs')]
class Bloc implements ResourceInterface
{
    use TimestampableEntity;
    use CampaignBlocDisplayTrait {
        __construct as private initBlocDisplay;
    }
    public const PREVIEW_URL_PREFIX = 'bloc-preview-';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled;

    #[ORM\OneToMany(targetEntity: Variant::class, mappedBy: 'bloc', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $variants;

    #[ORM\OneToMany(targetEntity: Focus::class, mappedBy: 'campaignBloc', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $focus;

    #[ORM\Column(name: 'offline_code', type: 'string')]
    private string $offlineCode;

    #[ORM\OneToMany(targetEntity: CampaignBlocDisplay::class, mappedBy: 'bloc', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $campaignBlocDisplay;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $buttonText = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $buttonLink = null;

    public function __construct()
    {
        $this->initBlocDisplay();
        $this->variants = new ArrayCollection();
        $this->focus = new ArrayCollection();
        $this->setOfflineCode();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(Variant $variant): self
    {
        if (!$this->hasVariant($variant)) {
            $variant->setBloc($this);
            $this->variants->add($variant);
        }

        return $this;
    }

    public function hasVariant(Variant $variant): bool
    {
        return $this->variants->contains($variant);
    }

    public function hasVariants(): bool
    {
        return !$this->variants->isEmpty();
    }

    public function removeVariant(Variant $variant): self
    {
        if ($this->hasVariant($variant)) {
            $this->variants->removeElement($variant);
        }

        return $this;
    }

    public function getFocus(): Collection
    {
        return $this->focus;
    }

    public function addFocu(Focus $focus): self
    {
        if (!$this->hasFocus($focus)) {
            $focus->setCampaignBloc($this);
            $this->focus->add($focus);
        }

        return $this;
    }

    public function hasFocus(Focus $focus): bool
    {
        return $this->focus->contains($focus);
    }

    public function hasFocusElements(): bool
    {
        return !$this->focus->isEmpty();
    }

    public function removeFocu(Focus $focus): self
    {
        if ($this->hasFocus($focus)) {
            $this->focus->remove($focus);
        }

        return $this;
    }

    public function getOfflineCode(): string
    {
        return $this->offlineCode;
    }

    private function setOfflineCode(): self
    {
        if (empty($this->offlineCode)) {
            $offlineCode = Uuid::uuid4();
            $this->offlineCode = $offlineCode->toString();
        }

        return $this;
    }

    public function getTotalProducts(): int
    {
        return $this->variants->count();
    }

    public function getButtonText(): ?string
    {
        return $this->buttonText;
    }

    public function setButtonText(?string $buttonText): self
    {
        $this->buttonText = $buttonText;

        return $this;
    }

    public function getButtonLink(): ?string
    {
        return $this->buttonLink;
    }

    public function setButtonLink(?string $buttonLink): self
    {
        $this->buttonLink = $buttonLink;

        return $this;
    }
}
