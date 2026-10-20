<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Repository\ReasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_reason')]
#[ORM\Entity(repositoryClass: ReasonRepository::class)]
class Reason implements ResourceInterface, \Stringable
{
    public const TYPE_PRODUCT_SUPPORT = 1;
    public const TYPE_COMMERCIAL_OFFER = 2;

    public const PRODUCT_RULE_NO_SELECTION = 1;
    public const PRODUCT_RULE_IDENTICAL = 2;
    public const PRODUCT_RULE_SIMPLE = 3;
    public const PRODUCT_RULE_QUALITY = 4;

    public const RETURN_TYPE_EXCHANGE = 0;
    public const RETURN_TYPE_NORMAL = 1;
    public const RETURN_TYPE_QUALITY = 2;
    public const RETURN_TYPE_REFUND = 3;
    public const RETURN_TYPE_SENDER = 4;
    public const RETURN_TYPE_QUALITY_EXCHANGE = 5;
    public const RETURN_TYPE_WAREHOUSE = 6;
    public const RETURN_TYPE_AUTONOMOUS = 7;

    public const TEMPLATE_OPTIONS = [
        'sylius.ui.reason.options.refund_needed' => 1,
        'sylius.ui.reason.options.100_nights' => 2,
    ];

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'type', type: 'integer')]
    private ?int $type = null;

    #[ORM\Column(name: 'name', type: 'string')]
    private ?string $name = null;

    #[ORM\Column(name: 'placeholder', type: 'string', nullable: true)]
    private ?string $placeholder = null;

    #[ORM\Column(name: 'productRule', type: 'integer', nullable: true)]
    private ?int $productRule = null;

    #[ORM\Column(name: 'returnType', type: 'integer', nullable: true)]
    private ?int $returnType = null;

    #[ORM\Column(name: 'template_options', type: 'ordered_array', nullable: true)]
    private ?array $templateOptions = null;

    #[ORM\Column(name: 'enable', type: 'boolean', nullable: false)]
    private bool $enable = true;

    #[ORM\OneToMany(targetEntity: Quality::class, mappedBy: 'reason', fetch: 'EXTRA_LAZY')]
    private ?Collection $quality;

    #[ORM\JoinColumn(name: 'channel_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Channel::class)]
    private ?Channel $channel = null;

    #[ORM\JoinColumn(name: 'groupName_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ReasonGroup::class)]
    private ?ReasonGroup $groupName = null;

    #[ORM\JoinTable(name: 'products_reasons')]
    #[ORM\JoinColumn(name: 'reason_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'reasons')]
    private ?Collection $products;

    public function __construct()
    {
        $this->quality = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getGroupName(): ?ReasonGroup
    {
        return $this->groupName;
    }

    public function setGroupName(ReasonGroup $groupName): void
    {
        $this->groupName = $groupName;
    }

    public function getChannel(): ?Channel
    {
        return $this->channel;
    }

    public function setChannel(?Channel $channel): void
    {
        $this->channel = $channel;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function setPlaceholder(?string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    public function getProductRule(): ?int
    {
        return $this->productRule;
    }

    public function setProductRule(int $productRule): void
    {
        $this->productRule = $productRule;
    }

    public function getReturnType(): ?int
    {
        return $this->returnType;
    }

    public function setReturnType(?int $returnType): void
    {
        $this->returnType = $returnType;
    }

    public function getTemplateOptions(): ?array
    {
        return $this->templateOptions;
    }

    public function setTemplateOptions(?array $templateOptions): void
    {
        $this->templateOptions = $templateOptions;
    }

    public function getQuality(): ?Collection
    {
        return $this->quality;
    }

    public function setQuality(?Collection $quality): Reason
    {
        $this->quality = $quality;

        return $this;
    }

    public function getProducts(): ?Collection
    {
        return $this->products;
    }

    public function setProducts(Collection $products): void
    {
        $this->products = $products;
    }

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): void
    {
        $this->enable = $enable;
    }
}
