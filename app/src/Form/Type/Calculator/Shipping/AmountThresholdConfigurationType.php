<?php

declare(strict_types=1);

namespace App\Form\Type\Calculator\Shipping;

use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;

class AmountThresholdConfigurationType extends AbstractType
{
    public const THRESHOLD_CONFIGURATION_NAME = 'amountThreshold';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(self::THRESHOLD_CONFIGURATION_NAME, MoneyType::class, [
                'label' => 'app.form.shipping_calculator.amount_threshold.label',
                'currency' => $options['currency'],
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                    new Range(['min' => 0, 'minMessage' => 'sylius.shipping_method.calculator.min', 'groups' => ['sylius']]),
                    new Type(['type' => 'integer', 'groups' => ['sylius']]),
                ],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'sylius.ui.price',
                'currency' => $options['currency'],
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                    new Range(['min' => 0, 'minMessage' => 'sylius.shipping_method.calculator.min', 'groups' => ['sylius']]),
                    new Type(['type' => 'integer', 'groups' => ['sylius']]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => null,
            ])
            ->setRequired('currency')
            ->setAllowedTypes('currency', 'string')
        ;
    }
}
