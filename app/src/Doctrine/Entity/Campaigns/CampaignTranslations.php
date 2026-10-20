<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Campaigns;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\AbstractTranslation;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'app_campaign_translations')]
#[ORM\Index(columns: ['url'], name: 'campaign_translations_url_idx')]
#[ORM\Index(columns: ['start_date', 'end_date'], name: 'campaign_translations_dates_idx')]
#[ORM\Index(columns: ['enabled'], name: 'campaign_translations_enabled_idx')]
#[ORM\Entity]
class CampaignTranslations extends AbstractTranslation implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'main_image_url', type: 'string', length: 255, nullable: true)]
    private ?string $mainImageUrl = null;

    #[ORM\Column(name: 'mobile_image_url', type: 'string', length: 255, nullable: true)]
    private ?string $mobileImageUrl = null;

    #[ORM\Column(name: 'url', type: 'string', length: 255)]
    private string $url;

    #[ORM\Column(name: 'meta_title', type: 'string', length: 255, nullable: true)]
    private ?string $metaTitle = null;

    #[ORM\Column(name: 'meta_description', type: 'string', length: 255, nullable: true)]
    private ?string $metaDesciption = null;

    #[ORM\Column(name: 'meta_keywords', type: 'string', length: 255, nullable: true)]
    private ?string $metaKeywords = null;

    #[ORM\Column(name: 'indexable', type: 'boolean')]
    private ?bool $indexable = true;

    #[ORM\Column(name: 'followable', type: 'boolean')]
    private ?bool $followable = true;

    #[ORM\Column(name: 'start_date', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(name: 'end_date', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(name: 'enabled', type: 'boolean')]
    private bool $enabled = false;

    #[ORM\Column(name: 'redirect_url', type: 'string', length: 255, nullable: true)]
    private ?string $redirectUrl = null;

    #[ORM\Column(name: 'redirect_http_status_code', type: 'integer')]
    private int $redirectionHttpStatusCode = 302;

    #[ORM\Column(name: 'seo_block_title', type: 'string', length: 255, nullable: true)]
    private ?string $seoBlockTitle = null;

    #[ORM\Column(name: 'seo_block_description', type: 'text', nullable: true)]
    private ?string $seoBlockDescription = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setMetaTitle(?string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDesciption = $metaDescription;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDesciption;
    }

    public function setMetaKeywords(?string $metaKeywords): self
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setIndexable(bool $indexable): self
    {
        $this->indexable = $indexable;

        return $this;
    }

    public function IsIndexable(): bool
    {
        return $this->indexable;
    }

    public function setFollowable(bool $followable): self
    {
        $this->followable = $followable;

        return $this;
    }

    public function isFollowable(): bool
    {
        return $this->followable;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        if ($startDate instanceof \DateTime) {
            $startDate = \DateTimeImmutable::createFromMutable($startDate);
        }
        $this->startDate = $startDate;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        if ($endDate instanceof \DateTime) {
            $endDate = \DateTimeImmutable::createFromMutable($endDate);
        }
        $this->endDate = $endDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setRedirectUrl(?string $redirectUrl): self
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectionHttpStatusCode(?int $redirectionHttpStatusCode): self
    {
        if (is_null($redirectionHttpStatusCode)) {
            $redirectionHttpStatusCode = 302;
        }
        $this->redirectionHttpStatusCode = $redirectionHttpStatusCode;

        return $this;
    }

    public function getRedirectionHttpStatusCode(): int
    {
        return $this->redirectionHttpStatusCode;
    }

    public function setMainImageUrl(?string $mainImageUrl): self
    {
        $this->mainImageUrl = $mainImageUrl;

        return $this;
    }

    public function getMainImageUrl(): ?string
    {
        return $this->mainImageUrl;
    }

    public function setSeoBlockTitle(?string $seoBlockTitle): self
    {
        $this->seoBlockTitle = $seoBlockTitle;

        return $this;
    }

    public function getSeoBlockTitle(): ?string
    {
        return $this->seoBlockTitle;
    }

    public function setSeoBlockDescription(?string $seoBlockDescription): self
    {
        $this->seoBlockDescription = $seoBlockDescription;

        return $this;
    }

    public function getSeoBlockDescription(): ?string
    {
        return $this->seoBlockDescription;
    }

    public function getMobileImageUrl(): ?string
    {
        return $this->mobileImageUrl;
    }

    public function setMobileImageUrl(?string $mobileImageUrl): self
    {
        $this->mobileImageUrl = $mobileImageUrl;

        return $this;
    }
}
