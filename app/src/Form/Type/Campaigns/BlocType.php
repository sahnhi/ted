<?php

declare(strict_types=1);

namespace App\Form\Type\Campaigns;

use App\Form\Type\Campaigns\Bloc\FocusType;
use App\Form\Type\Campaigns\Bloc\VariantsType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

final class BlocType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'app.form.bloc_campaign.bloc_name',
                    'required' => true,
                    'help' => 'app.form.bloc_campaign.bloc_name_helper',
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'label' => 'app.form.bloc_campaign.description',
                    'required' => false,
                    'help' => 'app.form.bloc_campaign.description_helper',
                ]
            )
            ->add(
                'variants',
                CollectionType::class,
                [
                    'entry_type' => VariantsType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ]
            )
            ->add(
                'focus',
                CollectionType::class,
                [
                    'entry_type' => FocusType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ]
            )
            ->add('buttonText', TextType::class, [
                'label' => 'app.form.bloc_campaign.button_text',
                'help' => 'app.form.bloc_campaign.button_text_help',
                'required' => false,
            ])
            ->add('buttonLink', UrlType::class, [
                'label' => 'app.form.bloc_campaign.button_link',
                'help' => 'app.form.bloc_campaign.button_link_help',
                'required' => false,
            ])
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'label' => 'sylius.ui.enabled',
                ]
            )
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_campaign_bloc';
    }
}
