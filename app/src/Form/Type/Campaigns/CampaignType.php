<?php

declare(strict_types=1);

namespace App\Form\Type\Campaigns;

use App\Form\Type\Campaigns\Bloc\CampaignBlocDisplayType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class CampaignType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'campaignName',
                TextType::class,
                [
                    'label' => 'app.form.campaign.campaign_name',
                    'required' => true,
                ]
            )
            ->add(
                'campaignNote',
                TextareaType::class,
                [
                    'label' => 'app.form.campaign.campaign_note',
                    'required' => false,
                ]
            )
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'label' => 'app.form.campaign.enabled',
                    'required' => false,
                ]
            )
            ->add(
                'translations',
                ResourceTranslationsType::class,
                [
                    'entry_type' => CampaignTranslationsType::class,
                ]
            )
            ->add(
                'campaignBlocDisplay',
                CollectionType::class,
                [
                    'entry_type' => CampaignBlocDisplayType::class,
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_campaign';
    }
}
