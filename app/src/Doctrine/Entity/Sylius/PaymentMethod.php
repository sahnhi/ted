<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\PaymentMethodRule;
use App\Doctrine\Entity\PaymentMethodRuleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentMethodRepository;
use Sylius\Component\Core\Model\PaymentMethod as BasePaymentMethod;

#[ORM\Table(name: 'sylius_payment_method')]
#[ORM\Entity(repositoryClass: PaymentMethodRepository::class)]
class PaymentMethod extends BasePaymentMethod implements PaymentMethodInterface
{
    #[ORM\OneToMany(mappedBy: 'paymentMethod', targetEntity: PaymentMethodRule::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $rules;

    public function __construct()
    {
        parent::__construct();

        $this->rules = new ArrayCollection();
    }

    /**
     * @return Collection<PaymentMethodRule>
     */
    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function addRule(PaymentMethodRuleInterface $rule): void
    {
        if (!$this->rules->contains($rule)) {
            $this->rules->add($rule);
            $rule->setPaymentMethod($this);
        }
    }

    public function removeRule(PaymentMethodRuleInterface $rule): void
    {
        if ($this->rules->contains($rule)) {
            $this->rules->removeElement($rule);
            $rule->setPaymentMethod(null);
        }
    }
}
