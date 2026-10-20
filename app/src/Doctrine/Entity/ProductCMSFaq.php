<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

#[ORM\Table(name: 'app_product_cms_faq')]
#[ORM\Entity]
class ProductCMSFaq implements ResourceInterface, TranslatableInterface
{
    use TimestampableEntity;

    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    protected int $id;

    #[ORM\OneToMany(mappedBy: 'productCMSFaq', targetEntity: ProductCMSFaqQuestion::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->initializeTranslationsCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string|null
    {
        return $this->getTranslation()->getTitle();
    }

    public function setTitle(string $title): self
    {
        $trans = $this->getTranslation();
        $trans->setTitle($title);

        return $this;
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(ProductCMSFaqQuestion $question): self
    {
        if (!$this->hasQuestion($question)) {
            $this->questions->add($question);
            $question->setProductCMSFaq($this);
        }

        return $this;
    }

    public function removeQuestion(ProductCMSFaqQuestion $question): self
    {
        if ($this->hasQuestion($question)) {
            $this->questions->removeElement($question);
            $question->setProductCMSFaq(null);
        }

        return $this;
    }

    public function hasQuestion(ProductCMSFaqQuestion $question): bool
    {
        return $this->questions->contains($question);
    }

    protected function createTranslation(): ProductCMSFaqTranslations
    {
        return new ProductCMSFaqTranslations();
    }

    public function getTranslation(?string $locale = null): ProductCMSFaqTranslations
    {
        /** @var ProductCMSFaqTranslations $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }
}
