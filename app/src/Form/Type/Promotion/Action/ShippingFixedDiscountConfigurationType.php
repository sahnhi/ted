<?php

declare(strict_types=1);

namespace App\Form\Type\Promotion\Action;

use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;

class ShippingFixedDiscountConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', MoneyType::class, [
                'label' => 'sylius.ui.amount',
                'constraints' => [
                    new GreaterThan([
                        'value' => 0,
                    ]),
                ],
            ]);
    }
}
