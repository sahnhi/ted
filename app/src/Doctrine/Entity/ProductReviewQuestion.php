<?php

namespace App\Doctrine\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\ApiPlatform\State\ProductReviewQuestionCartTokenCollectionProvider;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Repository\ProductReviewQuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

#[ORM\Table(name: 'sylius_product_review_question')]
#[ORM\Entity(repositoryClass: ProductReviewQuestionRepository::class)]
#[ApiResource(
    collectionOperations: [
        'shop_get_product_reviews_for_cart' => [
            'method' => 'GET',
            'path' => '/reviews/questions',
            'provider' => ProductReviewQuestionCartTokenCollectionProvider::class,
            'openapi_context' => [
                'parameters' => [
                    [
                        'name' => 'cartToken',
                        'in' => 'query',
                        'required' => true,
                    ],
                ],
            ],
        ],
    ],
    itemOperations: [
        'shop_get_product_review_question' => [
            'method' => 'GET',
            'path' => '/reviews/questions/{id}',
        ],
    ],
    normalizationContext: [
        'groups' => [
            'reviews:question:read',
        ],
    ],
)]
class ProductReviewQuestion implements ResourceInterface, TranslatableInterface
{
    use TranslatableTrait {
        __construct as initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }
    public const TYPE_RATING = 'rating';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'tablename_seq', allocationSize: 100, initialValue: 1)]
    private ?int $id = null;

    #[ORM\Column(name: 'question', type: 'string', nullable: false)]
    private ?string $question = null;

    #[ORM\Column(name: 'enabled', type: 'boolean', nullable: false)]
    private ?bool $enabled = null;

    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productReviewQuestions')]
    private ?Product $product = null;

    #[ORM\Column(name: 'questionType', type: 'string', nullable: false)]
    private ?string $questionType = null;

    #[ORM\Column(name: 'position', type: 'integer', nullable: false)]
    private ?int $position = null;

    #[ORM\ManyToMany(targetEntity: ProductReviewAnswerChoices::class, mappedBy: 'questions', cascade: ['persist', 'remove'])]
    private ?Collection $productReviewAnswerChoices;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
        $this->productReviewAnswerChoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): void
    {
        $this->product = $product;
    }

    public function setName($name): void
    {
        $this->getTranslation()->setName($name);
    }

    public function getTranslation(?string $locale = null): ProductReviewQuestionTranslationInterface
    {
        /** @var ProductReviewQuestionTranslationInterface $translation */
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

    public function getQuestionType(): ?string
    {
        return $this->questionType;
    }

    public function setQuestionType(string $questionType): void
    {
        $this->questionType = $questionType;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getProductReviewAnswerChoices(): ?Collection
    {
        return $this->productReviewAnswerChoices;
    }

    public function addProductReviewAnswerChoice(ProductReviewAnswerChoices $productReviewAnswerChoice): void
    {
        if (!$this->hasProductReviewAnswerChoice($productReviewAnswerChoice)) {
            $productReviewAnswerChoice->addQuestion($this);
        }
    }

    public function hasProductReviewAnswerChoice(ProductReviewAnswerChoices $productReviewAnswerChoice): bool
    {
        return $this->productReviewAnswerChoices->contains($productReviewAnswerChoice);
    }

    public function removeProductReviewAnswerChoice(ProductReviewAnswerChoices $productReviewAnswerChoice): void
    {
        if ($this->hasProductReviewAnswerChoice($productReviewAnswerChoice)) {
            $productReviewAnswerChoice->removeQuestion($this);
        }
    }

    protected function createTranslation(): ProductReviewQuestionTranslationInterface
    {
        return new ProductReviewQuestionTranslation();
    }
}
