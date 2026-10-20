<?php

declare(strict_types=1);

namespace App\Form\Type\Promotion\Action;

use Sylius\Bundle\CoreBundle\Form\Type\Promotion\Filter\ProductFilterConfigurationType;
use Sylius\Bundle\CoreBundle\Form\Type\Promotion\Filter\TaxonFilterConfigurationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;

final class UnitPercentageDiscountExclusionConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('percentage', PercentType::class, [
                'label' => 'sylius.form.promotion_action.percentage_discount_configuration.percentage',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                    new Type(['type' => 'numeric', 'groups' => ['sylius']]),
                    new Range([
                        'min' => 0,
                        'max' => 1,
                        'minMessage' => 'sylius.promotion_action.percentage_discount_configuration.min',
                        'maxMessage' => 'sylius.promotion_action.percentage_discount_configuration.max',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
        ;

        $builder->add('taxons_filter', TaxonFilterConfigurationType::class, [
            'label' => false,
            'required' => false,
        ]);
        $builder->add('products_filter', ProductFilterConfigurationType::class, [
            'label' => false,
            'required' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_promotion_action_unit_percentage_discount_exclusion_configuration';
    }
}
