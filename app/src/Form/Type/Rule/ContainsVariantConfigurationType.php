<?php

declare(strict_types=1);

namespace App\Form\Type\Rule;

use App\Form\Type\ProductVariantAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;

class ContainsVariantConfigurationType extends AbstractType
{
    public function __construct(
        private readonly DataTransformerInterface $variantsToCodesTransformer,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('variants', ProductVariantAutocompleteChoiceType::class, [
                'label' => 'app.ui.catalog_promotion.form.promotion_rule.contains_variant_configuration.variants',
                'multiple' => true,
            ]);

        $builder->get('variants')->addModelTransformer($this->variantsToCodesTransformer);
    }
}
