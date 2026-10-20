<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

#[ORM\Table(name: 'app_product_cms_faq_question')]
#[ORM\Entity]
class ProductCMSFaqQuestion implements ResourceInterface, TranslatableInterface
{
    use TimestampableEntity;

    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
        getTranslation as doGetTranslation;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: ProductCMSFaq::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(name: 'product_cms_faq_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ProductCMSFaq|null $productCMSFaq = null;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuestion(): string
    {
        /**
         * @var ProductCMSFaqQuestionTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getQuestion();
    }

    public function setQuestion(string $question): self
    {
        /**
         * @var ProductCMSFaqQuestionTranslations $trans
         */
        $trans = $this->getTranslation();
        $trans->setQuestion($question);

        return $this;
    }

    public function getAnswer(): string
    {
        /**
         * @var ProductCMSFaqQuestionTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getAnswer();
    }

    public function setAnswer(string $answer): self
    {
        /**
         * @var ProductCMSFaqQuestionTranslations $trans
         */
        $trans = $this->getTranslation();
        $trans->setAnswer($answer);

        return $this;
    }

    public function getProductCMSFaq(): ProductCMSFaq|null
    {
        return $this->productCMSFaq;
    }

    public function setProductCMSFaq(ProductCMSFaq|null $productCMSFaq): self
    {
        $this->productCMSFaq = $productCMSFaq;

        return $this;
    }

    protected function createTranslation(): ProductCMSFaqQuestionTranslations
    {
        return new ProductCMSFaqQuestionTranslations();
    }

    public function getTranslation(?string $locale = null): ProductCMSFaqQuestionTranslations
    {
        /** @var ProductCMSFaqQuestionTranslations $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }
}
