<?php

namespace App\Api\TrustPilot\DTO\Review;

use App\Api\TrustPilot\DTO\Review\Review\BusinessUnit;
use App\Api\TrustPilot\DTO\Review\Review\BusinessUnitHistory;
use App\Api\TrustPilot\DTO\Review\Review\CompanyReply;
use App\Api\TrustPilot\DTO\Review\Review\Consumer;
use App\Api\TrustPilot\DTO\Review\Review\FindReviewer;
use App\Api\TrustPilot\DTO\Review\Review\Invitation;
use App\Api\TrustPilot\DTO\Review\Review\Location;
use App\Api\TrustPilot\DTO\Review\Review\ReportData;
use App\Api\TrustPilot\DTO\Review\Review\Tag;
use DateTime;
use JMS\Serializer\Annotation\Type;

class Review
{
    /**
     * @var string
     *
     * @Type("string")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @Type("DateTime")
     */
    private $createdAt;

    /**
     * @var array
     *
     * @Type("array")
     */
    private $complianceLabels = [];

    /**
     * @var bool
     *
     * @Type("bool")
     */
    private $countsTowardsTrustScore;

    /**
     * @var bool
     *
     * @Type("bool")
     */
    private $countsTowardsLocationTrustScore;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $reviewVerificationLevel;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $status;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $source;

    /**
     * @var bool
     *
     * @Type("bool")
     */
    private $isVerified;

    /**
     * @var int
     *
     * @Type("int")
     */
    private $stars;

    /**
     * @var \DateTime
     *
     * @Type("DateTime")
     */
    private $updatedAt;

    /**
     * @var \DateTime
     *
     * @Type("DateTime")
     */
    private $experiencedAt;

    /**
     * @var int
     *
     * @Type("int")
     */
    private $numberOfLikes;

    /**
     * @var string|null
     *
     * @Type("string")
     */
    private $referenceId;

    /**
     * @var string|null
     *
     * @Type("string")
     */
    private $referralEmail;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $language;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $title;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $text;

    /**
     * @var BusinessUnit
     *
     * @Type("App\Api\TrustPilot\DTO\Review\Review\BusinessUnit")
     */
    private $businessUnit;

    /**
     * @var Location
     *
     * @Type("App\Api\TrustPilot\DTO\Review\Review\Location")
     */
    private $location;

    /**
     * @var Link[]
     *
     * @Type("array<App\Api\TrustPilot\DTO\Review\Link>")
     */
    private $links;

    /**
     * @var ReportData
     *
     * @Type("App\Api\TrustPilot\DTO\Review\Review\ReportData")
     */
    private $reportData;

    /**
     * @var FindReviewer
     *
     * @Type("App\Api\TrustPilot\DTO\Review\Review\FindReviewer")
     */
    private $findReviewer;

    /**
     * @var CompanyReply
     *
     * @Type("App\Api\TrustPilot\DTO\Review\Review\CompanyReply")
     */
    private $companyReply;

    /**
     * @var Tag[]
     *
     * @Type("array<App\Api\TrustPilot\DTO\Review\Review\Tag>")
     */
    private $tags;

    /**
     * @var Consumer
     *
     * @Type("App\Api\TrustPilot\DTO\Review\Review\Consumer")
     */
    private $consumer;

    /**
     * @var Invitation
     *
     * @Type("App\Api\TrustPilot\DTO\Review\Review\Invitation")
     */
    private $invitation;

    /**
     * @var BusinessUnitHistory
     *
     * @Type("App\Api\TrustPilot\DTO\Review\Review\BusinessUnitHistory")
     */
    private $businessUnitHistory;

    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getComplianceLabels(): array
    {
        return $this->complianceLabels;
    }

    public function isCountsTowardsTrustScore(): bool
    {
        return $this->countsTowardsTrustScore;
    }

    public function isCountsTowardsLocationTrustScore(): bool
    {
        return $this->countsTowardsLocationTrustScore;
    }

    public function getReviewVerificationLevel(): string
    {
        return $this->reviewVerificationLevel;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function getStars(): int
    {
        return $this->stars;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function getExperiencedAt(): \DateTime
    {
        return $this->experiencedAt;
    }

    public function getNumberOfLikes(): int
    {
        return $this->numberOfLikes;
    }

    public function getReferenceId(): ?string
    {
        return $this->referenceId;
    }

    public function getReferralEmail(): ?string
    {
        return $this->referralEmail;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getBusinessUnit(): BusinessUnit
    {
        return $this->businessUnit;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @return Link[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    public function getReportData(): ReportData
    {
        return $this->reportData;
    }

    public function getFindReviewer(): FindReviewer
    {
        return $this->findReviewer;
    }

    public function getCompanyReply(): CompanyReply
    {
        return $this->companyReply;
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getConsumer(): Consumer
    {
        return $this->consumer;
    }

    public function getInvitation(): Invitation
    {
        return $this->invitation;
    }

    public function getBusinessUnitHistory(): BusinessUnitHistory
    {
        return $this->businessUnitHistory;
    }
}
