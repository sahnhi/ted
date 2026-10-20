<?php

declare(strict_types=1);

namespace App\Form\Type\Campaigns\Bloc;

use App\Form\Type\ProductVariantAutocompleteChoiceType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class VariantsType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'variant',
                ProductVariantAutocompleteChoiceType::class,
                [
                    'label' => 'app.form.bloc_campaign.variants.search_variant',
                    'required' => true,
                ]
            )
            ->add(
                'satellitePageUrl',
                TextType::class,
                [
                    'label' => 'app.form.bloc_campaign.variants.satellite_page_url_label',
                    'required' => false,
                    'help' => 'app.form.bloc_campaign.variants.satellite_page_url_label_help',
                ]
            )
            ->add(
                'position',
                TextType::class,
                [
                    'label' => 'app.form.bloc_campaign.variants.position',
                    'required' => true,
                    'help' => 'app.form.bloc_campaign.variants.position_help',
                    'empty_data' => 1,
                ]
            )
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_campaigns_variants_type';
    }
}
