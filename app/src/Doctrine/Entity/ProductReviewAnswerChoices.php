<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Repository\ProductReviewAnswerChoicesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

#[ORM\Table(name: 'sylius_product_review_answer_choices')]
#[ORM\Entity(repositoryClass: ProductReviewAnswerChoicesRepository::class)]
class ProductReviewAnswerChoices implements ResourceInterface, TranslatableInterface
{
    use TranslatableTrait {
        __construct as initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'tablename_seq', allocationSize: 100, initialValue: 1)]
    private ?int $id = null;

    #[ORM\Column(name: 'code', type: 'string', nullable: false)]
    private ?string $code = null;

    #[ORM\JoinTable(name: 'sylius_questions_answer_choices')]
    #[ORM\JoinColumn(name: 'answer_choice_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'question_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: ProductReviewQuestion::class, inversedBy: 'productReviewAnswerChoices')]
    private ?Collection $questions = null;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestions(): ?Collection
    {
        return $this->questions;
    }

    public function addQuestion(ProductReviewQuestion $question): void
    {
        if (!$this->hasQuestion($question)) {
            $this->questions->add($question);
        }
    }

    public function hasQuestion(ProductReviewQuestion $question): bool
    {
        return $this->questions->contains($question);
    }

    public function removeQuestion(ProductReviewQuestion $question): void
    {
        if ($this->hasQuestion($question)) {
            $this->questions->removeElement($question);
        }
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function setName($name): void
    {
        $this->getTranslation()->setName($name);
    }

    public function getTranslation(?string $locale = null): ProductReviewAnswerChoicesTranslationInterface
    {
        /** @var ProductReviewAnswerChoicesTranslationInterface $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }

    public function getName(?string $locale = null): string
    {
        return $this->getTranslation($locale)->getName();
    }

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    protected function createTranslation(): ProductReviewAnswerChoicesTranslationInterface
    {
        return new ProductReviewAnswerChoicesTranslation();
    }
}
