<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\ProductReviewAnswer;
use App\Doctrine\Entity\ProductReviewQuestion;
use App\Doctrine\Repository\Sylius\ProductReviewRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductReview as BaseProductReview;

#[ORM\Table(name: 'sylius_product_review')]
#[ORM\Entity(repositoryClass: ProductReviewRepository::class)]
#[ORM\Index(columns: ['status'])]
#[ORM\Index(columns: ['product_id', 'status'])]
#[ORM\Index(columns: ['title', 'locale', 'status'])]
#[ORM\Index(columns: ['created_at'])]
class ProductReview extends BaseProductReview
{
    #[ORM\Column(name: 'locale', type: 'string', nullable: true)]
    private ?string $locale = null;

    #[ORM\Column(name: 'remercie_date', type: 'datetime', nullable: true)]
    private ?\DateTime $remercieDate = null;

    #[ORM\Column(name: 'remercie', type: 'boolean', nullable: true)]
    private ?bool $remercie = null;

    #[ORM\Column(name: 'star', type: 'boolean', nullable: true)]
    private ?bool $star = false;

    #[ORM\Column(name: 'sav_answer', type: 'text', nullable: true)]
    private ?string $savAnswer = null;

    #[ORM\OneToMany(mappedBy: 'review', targetEntity: ProductReviewAnswer::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private ?Collection $answers;

    private ?Collection $questions;

    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Product::class, fetch: 'EXTRA_LAZY')]
    private ?Product $product = null;

    #[ORM\JoinColumn(name: 'shipping_method_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ShippingMethod::class)]
    private ?ShippingMethod $shippingMethod = null;

    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Order::class)]
    private ?Order $order = null;

    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductReview::class)]
    private ?ProductReview $parent = null;

    public function __construct()
    {
        parent::__construct();

        $this->questions = new ArrayCollection();
        $this->answers = new ArrayCollection();
    }

    public function getShippingMethod(): ?ShippingMethod
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(ShippingMethod $shippingMethod): void
    {
        $this->shippingMethod = $shippingMethod;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    public function getParent(): ?ProductReview
    {
        return $this->parent;
    }

    public function setParent(ProductReview $parent): void
    {
        $this->parent = $parent;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function getRemercie(): ?bool
    {
        return $this->remercie;
    }

    public function setRemercie($remercie = null): static
    {
        $this->remercie = $remercie;

        $this->setRemercieDate(new \DateTime());

        return $this;
    }

    public function addAnswer(ProductReviewAnswer $answer): static
    {
        $this->answers[] = $answer;

        return $this;
    }

    public function removeAnswer(ProductReviewAnswer $answer)
    {
        return $this->answers->removeElement($answer);
    }

    public function addQuestion(ProductReviewQuestion $question): static
    {
        $this->questions[] = $question;

        return $this;
    }

    public function removeQuestion(ProductReviewQuestion $question)
    {
        return $this->questions->removeElement($question);
    }

    public function getRemercieDate(): ?\DateTime
    {
        return $this->remercieDate;
    }

    public function setRemercieDate($remercieDate = null): static
    {
        $this->remercieDate = $remercieDate;

        return $this;
    }

    public function getStar(): ?bool
    {
        return $this->star;
    }

    public function setStar(?bool $star): void
    {
        $this->star = $star;
    }

    public function getSavAnswer(): ?string
    {
        return $this->savAnswer;
    }

    public function setSavAnswer(?string $savAnswer): void
    {
        $this->savAnswer = $savAnswer;
    }
}
