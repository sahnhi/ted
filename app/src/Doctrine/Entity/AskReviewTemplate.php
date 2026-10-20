<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Doctrine\Repository\AskReviewTemplateRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'ask_review_template')]
#[ORM\Entity(repositoryClass: AskReviewTemplateRepository::class)]
class AskReviewTemplate implements ResourceInterface
{
    #[ORM\Column(name: 'order', type: 'smallint')]
    private ?int $order = null;

    public function __construct(
        #[ORM\Column(name: 'id', type: 'integer')]
        #[ORM\Id]
        #[ORM\GeneratedValue(strategy: 'IDENTITY')]
        private ?int $id,
        /**
         * @Assert\Range(
         *       min = 0,
         *       max = 100,
         *       notInRangeMessage = "Percentage must be between {{ min }} and {{ max }}",
         *  )
         */
        #[ORM\Column(name: 'templateId', type: 'string', nullable: true)]
        private ?string $templateId,
        #[ORM\Column(name: 'serviceName', type: 'string')]
        private ?string $serviceName,
        #[ORM\Column(name: 'percentage', type: 'smallint')]
        private int $percentage
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTemplateId(): ?string
    {
        return $this->templateId;
    }

    public function setTemplateId(?string $templateId): void
    {
        $this->templateId = $templateId;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function setServiceName(string $serviceName): void
    {
        $this->serviceName = $serviceName;
    }

    public function getPercentage(): int
    {
        return $this->percentage;
    }

    public function setPercentage(int $percentage): AskReviewTemplate
    {
        $this->percentage = $percentage;

        return $this;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): AskReviewTemplate
    {
        $this->order = $order;

        return $this;
    }
}
