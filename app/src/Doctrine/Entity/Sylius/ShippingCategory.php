<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\ServiceRules;
use App\Doctrine\Entity\ShippingCategoryTranslation;
use App\Doctrine\Repository\Sylius\ShippingCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;
use Sylius\Component\Shipping\Model\ShippingCategory as BaseShippingCategory;

#[ORM\Table(name: 'sylius_shipping_category')]
#[ORM\Entity(repositoryClass: ShippingCategoryRepository::class)]
class ShippingCategory extends BaseShippingCategory implements TranslatableInterface
{
    use TranslatableTrait {
        __construct as initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }

    #[ORM\JoinTable(name: 'sylius_shipping_category_method')]
    #[ORM\JoinColumn(name: 'shipping_category_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'shipping_method_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: ShippingMethod::class, inversedBy: 'shippingCategory')]
    private ?Collection $shippingMethod;

    #[ORM\JoinTable(name: 'sylius_shipping_category_service_rules')]
    #[ORM\JoinColumn(name: 'shipping_category_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'service_rules_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: ServiceRules::class, inversedBy: 'shippingCategory')]
    private ?Collection $serviceRules;

    public function __construct()
    {
        parent::__construct();

        $this->shippingMethod = new ArrayCollection();
        $this->serviceRules = new ArrayCollection();

        $this->initializeTranslationsCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function getShippingMethod(): ?Collection
    {
        return $this->shippingMethod;
    }

    public function addShippingMethod(ShippingMethod $shippingMethod): void
    {
        if (!$this->shippingMethod->contains($shippingMethod)) {
            $this->shippingMethod[] = $shippingMethod;
            $shippingMethod->addShippingCategory($this);
        }
    }

    public function removeShippingMethod(ShippingMethod $shippingMethod): void
    {
        $this->shippingMethod->removeElement($shippingMethod);
    }

    public function getServiceRules(): Collection
    {
        return $this->serviceRules;
    }

    public function addServiceRules(ServiceRules $serviceRules): void
    {
        if (!$this->serviceRules->contains($serviceRules)) {
            $this->serviceRules[] = $serviceRules;
            $serviceRules->addShippingCategory($this);
        }
    }

    public function removeServiceRules(ServiceRules $serviceRules): void
    {
        $this->serviceRules->removeElement($serviceRules);
    }

    public function getTranslation(?string $locale = null): ShippingCategoryTranslation
    {
        /** @var ShippingCategoryTranslation $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }

    protected function createTranslation(): TranslationInterface
    {
        return new ShippingCategoryTranslation();
    }
}
