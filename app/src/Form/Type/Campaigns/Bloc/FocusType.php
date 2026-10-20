<?php

declare(strict_types=1);

namespace App\Form\Type\Campaigns\Bloc;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class FocusType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'imageUrl',
                UrlType::class,
                [
                    'label' => 'app.form.bloc_campaign.focus.image_url',
                    'required' => true,
                    'help' => 'app.form.bloc_campaign.focus.image_url_help',
                ]
            )
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'app.form.bloc_campaign.focus.title',
                    'required' => false,
                    'help' => 'app.form.bloc_campaign.focus.title_help',
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'label' => 'app.form.bloc_campaign.focus.description',
                    'help' => 'app.form.bloc_campaign.focus.description_help',
                    'required' => false,
                ]
            )
            ->add(
                'tag',
                TextType::class,
                [
                    'label' => 'app.form.bloc_campaign.focus.tag',
                    'required' => false,
                ]
            )
            ->add(
                'buttonText',
                TextType::class,
                [
                    'label' => 'app.form.bloc_campaign.focus.button_text',
                    'help' => 'app.form.bloc_campaign.focus.button_text_help',
                    'required' => false,
                ]
            )
            ->add(
                'buttonUrl',
                UrlType::class,
                [
                    'label' => 'app.form.bloc_campaign.focus.button_url',
                    'help' => 'app.form.bloc_campaign.focus.button_url_help',
                    'required' => false,
                ]
            )
            ->add(
                'position',
                TextType::class,
                [
                    'label' => 'app.form.bloc_campaign.focus.position',
                    'help' => 'app.form.bloc_campaign.focus.position_help',
                    'required' => true,
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_campaign_bloc_add_type';
    }
}
