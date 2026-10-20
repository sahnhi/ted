<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Campaigns;

use App\Doctrine\Entity\Campaigns\Bloc\CampaignBlocDisplay;
use App\Doctrine\Entity\Campaigns\Trait\CampaignBlocDisplayTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

#[ORM\Table(name: 'app_campaign')]
#[ORM\Entity]
class Campaign implements ResourceInterface, TranslatableInterface
{
    use TranslatableTrait {
        TranslatableTrait::__construct as private initializeTranslationsCollection;
    }

    use CampaignBlocDisplayTrait {
        CampaignBlocDisplayTrait::__construct as private initBlocDisplay;
    }

    use TimestampableEntity;
    public const PREVIEW_PREFIX = 'campaign-preview-';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'campaign_name', type: 'string', length: 255)]
    private string $campaignName;

    #[ORM\Column(name: 'campaign_slug', type: 'string', length: 255, unique: true, nullable: true)]
    #[Gedmo\Slug(fields: ['campaignName'])]
    private string $campaignSlug;

    #[ORM\Column(name: 'campaign_note', type: 'text', nullable: true)]
    private ?string $campaignNote = null;

    #[ORM\Column(name: 'uuid', type: 'string', length: 36, unique: true, nullable: true)]
    private ?string $uuid = null;

    #[ORM\Column(name: 'enabled', type: 'boolean')]
    private bool $enabled = false;

    #[ORM\OneToMany(targetEntity: CampaignBlocDisplay::class, mappedBy: 'campaign', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $campaignBlocDisplay;

    private bool $error = false;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
        $this->initBlocDisplay();
        $this->setUuid();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setCampaignName(string $campaignName): self
    {
        $this->campaignName = $campaignName;

        return $this;
    }

    public function getCampaignName(): string
    {
        return $this->campaignName;
    }

    public function setCampaignNote(?string $campaignNote): self
    {
        $this->campaignNote = $campaignNote;

        return $this;
    }

    public function getCampaignNote(): ?string
    {
        return $this->campaignNote;
    }

    public function getSlug(): string
    {
        return $this->campaignSlug;
    }

    public function setSlug(string $campaignSlug): void
    {
        $this->campaignSlug = $campaignSlug;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getStartDate();
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();
        $trans->setStartDate($startDate);

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getEndDate();
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();
        $trans->setEndDate($endDate);

        return $this;
    }

    public function getUrl(): string
    {
        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getUrl();
    }

    public function setUrl(string $url): self
    {
        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();
        $trans->setUrl($url);

        return $this;
    }

    public function createTranslation(): CampaignTranslations
    {
        return new CampaignTranslations();
    }

    public function getCampaignSlug(): string
    {
        return $this->campaignSlug;
    }

    public function getMainImageUrl(): ?string
    {
        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getMainImageUrl();
    }

    public function setMainImageUrl(?string $mainImageUrl): self
    {
        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();
        $trans->setMainImageUrl($mainImageUrl);

        return $this;
    }

    public function getSeoBlockTitle(): ?string
    {
        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getSeoBlockTitle();
    }

    public function setSeoBlockTitle(?string $seoBlockTitle): self
    {
        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();
        $trans->setSeoBlockTitle($seoBlockTitle);

        return $this;
    }

    public function getSeoBlockDescription(): ?string
    {
        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getSeoBlockDescription();
    }

    public function setSeoBlockDescription(?string $seoBlockDescription): self
    {
        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();
        $trans->setSeoBlockDescription($seoBlockDescription);

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    private function setUuid(): self
    {
        if (empty($this->uuid)) {
            $this->uuid = Uuid::uuid4()->toString();
        }

        return $this;
    }

    public function setError(bool $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getError(): ?bool
    {
        return $this->error;
    }

    public function getStatusCode(): ?int
    {
        if (!$this->getError()) {
            return null;
        }

        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getRedirectionHttpStatusCode();
    }

    public function getRedirectUrl(): ?string
    {
        if (!$this->getError()) {
            return null;
        }

        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getRedirectUrl() ?? '/';
    }

    public function getMobileImageUrl(): ?string
    {
        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getMobileImageUrl();
    }

    public function setMobileImageUrl(?string $mobileImageUrl): self
    {
        /**
         * @var CampaignTranslations $trans
         */
        $trans = $this->getTranslation();
        $trans->setMobileImageUrl($mobileImageUrl);

        return $this;
    }
}
