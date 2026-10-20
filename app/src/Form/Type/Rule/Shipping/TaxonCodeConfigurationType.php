<?php

declare(strict_types=1);

namespace App\Form\Type\Rule\Shipping;

use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;

class TaxonCodeConfigurationType extends AbstractType
{
    public function __construct(
        private readonly DataTransformerInterface $taxonToCodeTransformer
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('taxon', TaxonAutocompleteChoiceType::class, [
                'label' => 'sylius.ui.taxons',
                'multiple' => false,
            ]);

        $builder->get('taxon')->addModelTransformer($this->taxonToCodeTransformer);
    }
}
