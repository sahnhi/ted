<?php

namespace App\Form\Type\Rule;

use App\Form\Type\ProductVariantAutocompleteChoiceType;
use Sylius\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class VariantConfigurationType extends AbstractType
{
    public function __construct(
        private readonly RepositoryInterface $productVariantRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product_variant_code', ProductVariantAutocompleteChoiceType::class, [
                'label' => 'app.form.promotion_action.add_product_configuration.product_variant',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                    new Type(['type' => 'string', 'groups' => ['sylius']]),
                ],
            ]);

        $builder->get('product_variant_code')->addModelTransformer(
            new ReversedTransformer(new ResourceToIdentifierTransformer($this->productVariantRepository, 'code'))
        );
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_promotion_rule_contains_product_configuration';
    }
}
