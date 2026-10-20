<?php

declare(strict_types=1);

namespace App\Form\Type\Promotion\Action;

use App\Form\Type\ProductVariantAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;

class GiftProductVariantConfigurationType extends AbstractType
{
    public function __construct(
        private readonly DataTransformerInterface $variantsToCodesTransformer
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('variants', ProductVariantAutocompleteChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'label' => 'sylius.ui.variants',
            ]);

        $builder->get('variants')->addModelTransformer($this->variantsToCodesTransformer);
    }
}
