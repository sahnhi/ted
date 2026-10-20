<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Repository\PackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

#[ORM\Table(name: 'sylius_pack')]
#[ORM\Entity(repositoryClass: PackRepository::class)]
class Pack implements ResourceInterface, TranslatableInterface, \Stringable
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

    #[ORM\ManyToMany(targetEntity: ProductVariant::class, mappedBy: 'packs', fetch: 'EXTRA_LAZY')]
    private Collection $variants;

    #[ORM\ManyToMany(targetEntity: ProductVariant::class, mappedBy: 'addons', fetch: 'EXTRA_LAZY')]
    private Collection $addons;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
        $this->variants = new ArrayCollection();
        $this->addons = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function getName(): ?string
    {
        return $this->getTranslation()->getName();
    }

    public function getTranslation(?string $locale = null): PackTranslationInterface
    {
        /** @var PackTranslationInterface $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function setVariants(Collection $variants): void
    {
        $this->variants = $variants;
    }

    public function addVariant(ProductVariant $variant): void
    {
        if (!$this->variants->contains($variant)) {
            $this->variants->add($variant);
            $variant->addPack($this);
        }
    }

    public function removeVariant(ProductVariant $variant): void
    {
        if ($this->variants->contains($variant)) {
            $this->variants->removeElement($variant);
            $variant->removePack($this);
        }
    }

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    protected function createTranslation(): PackTranslationInterface
    {
        return new PackTranslation();
    }

    public function getAddons(): Collection
    {
        return $this->addons;
    }

    public function addAddon(ProductVariant $variant): void
    {
        if (!$this->addons->contains($variant)) {
            $this->addons->add($variant);
            $variant->addAddon($this);
        }
    }

    public function removeAddon(ProductVariant $variant): void
    {
        if ($this->addons->contains($variant)) {
            $this->addons->removeElement($variant);
            $variant->removeAddon($this);
        }
    }
}
