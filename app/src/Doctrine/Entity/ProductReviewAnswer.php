<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductReview;
use App\Doctrine\Repository\ProductReviewAnswerRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_product_review_answer')]
#[ORM\Entity(repositoryClass: ProductReviewAnswerRepository::class)]
class ProductReviewAnswer implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'tablename_seq', allocationSize: 100, initialValue: 1)]
    private ?int $id = null;

    #[ORM\Column(name: 'answer', type: 'text', nullable: false)]
    private ?string $answer = null;

    #[ORM\JoinColumn(name: 'review_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductReview::class, inversedBy: 'answers')]
    private ?ProductReview $review = null;

    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductReviewQuestion::class)]
    private ?ProductReviewQuestion $question = null;

    #[ORM\JoinColumn(name: 'answer_choice_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductReviewAnswerChoices::class)]
    private ?ProductReviewAnswerChoices $answerChoices = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReview(): ?ProductReview
    {
        return $this->review;
    }

    public function setReview(ProductReview $review): void
    {
        $this->review = $review;
    }

    public function getQuestion(): ?ProductReviewQuestion
    {
        return $this->question;
    }

    public function setQuestion(ProductReviewQuestion $question): void
    {
        $this->question = $question;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }

    public function getAnswerChoices(): ?ProductReviewAnswerChoices
    {
        return $this->answerChoices;
    }

    public function setAnswerChoices(ProductReviewAnswerChoices $answerChoices): void
    {
        $this->answerChoices = $answerChoices;
    }
}
