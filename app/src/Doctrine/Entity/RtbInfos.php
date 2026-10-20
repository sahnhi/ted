<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'app_rtb_infos')]
class RtbInfos implements ResourceInterface, TranslatableInterface
{
    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }

    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(type: 'integer')]
    private int $priority = 1;

    #[ORM\Column(type: 'string')]
    private string $imageUrl;

    public function getId(): int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        /**
         * @var RtbInfosTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getLabel();
    }

    public function setLabel(?string $label): self
    {
        /**
         * @var RtbInfosTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setLabel($label);

        return $this;
    }

    public function getDescription(): string
    {
        /**
         * @var RtbInfosTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getDescription();
    }

    public function setDescription(string $description): self
    {
        /**
         * @var RtbInfosTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setDescription($description);

        return $this;
    }

    public function getUrl(): string
    {
        /**
         * @var RtbInfosTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getUrl();
    }

    public function setUrl(string $url): self
    {
        /**
         * @var RtbInfosTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setUrl($url);

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    protected function createTranslation(): TranslationInterface
    {
        return new RtbInfosTranslation();
    }
}
