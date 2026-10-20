<?php

declare(strict_types=1);

namespace App\Form\Type\Promotion\Configuration;

use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;

final class RefuseTaxonConfigurationType extends AbstractType
{
    public function __construct(
        private readonly DataTransformerInterface $taxonsToCodesTransformer
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('taxons', TaxonAutocompleteChoiceType::class, [
                'label' => 'app.ui.refuse_taxon',
                'multiple' => true,
            ])
        ;

        $builder->get('taxons')->addModelTransformer($this->taxonsToCodesTransformer);
    }

    public function getBlockPrefix(): string
    {
        return 'app_promotion_rule_refuse_taxon_configuration';
    }
}
