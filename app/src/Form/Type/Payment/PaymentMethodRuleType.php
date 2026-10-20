<?php

namespace App\Form\Type\Payment;

use App\Doctrine\Entity\PaymentMethodRule;
use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class PaymentMethodRuleType extends AbstractResourceType
{
    public function __construct()
    {
        parent::__construct(PaymentMethodRule::class, ['sylius']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'app.form.payment_method.type',
                'choices' => [
                    'None' => null,
                    'app.form.payment_method.price_rule.lower' => '<',
                    'app.form.payment_method.price_rule.greater' => '>',
                ],
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'sylius.ui.amount',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_payment_method_rule';
    }
}
