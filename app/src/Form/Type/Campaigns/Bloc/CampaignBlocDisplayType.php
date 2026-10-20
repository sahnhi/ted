<?php

declare(strict_types=1);

namespace App\Form\Type\Campaigns\Bloc;

use App\Doctrine\Entity\Campaigns\Bloc;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

final class CampaignBlocDisplayType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'seoBlock',
                CheckboxType::class,
                [
                    'label' => 'app.form.campaign.bloc.seo_block',
                    'help' => 'app.form.campaign.bloc.seo_block_help',
                ]
            )
            ->add(
                'bloc',
                EntityType::class,
                [
                    'class' => Bloc::class,
                    'choice_label' => 'name',
                    'label' => 'app.form.campaign.bloc.bloc_title',
                ]
            )
            ->add(
                'position',
                IntegerType::class,
                [
                    'label' => 'app.form.campaign.bloc.priority',
                    'help' => 'app.form.campaign.bloc.priority_help',
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_campaign_bloc_display';
    }
}
