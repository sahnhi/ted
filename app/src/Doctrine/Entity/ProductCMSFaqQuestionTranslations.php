<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\AbstractTranslation;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'app_product_cms_faq_question_translations')]
#[ORM\Entity]
class ProductCMSFaqQuestionTranslations extends AbstractTranslation implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'question', type: 'string', length: 255)]
    private string|null $question = null;

    #[ORM\Column(name: 'answer', type: 'text')]
    private string|null $answer = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuestion(): string|null
    {
        return $this->question;
    }

    public function setQuestion(string|null $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): string|null
    {
        return $this->answer;
    }

    public function setAnswer(string|null $answer): self
    {
        $this->answer = $answer;

        return $this;
    }
}
