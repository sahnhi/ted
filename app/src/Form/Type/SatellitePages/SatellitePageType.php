<?php

declare(strict_types=1);

namespace App\Form\Type\SatellitePages;

use App\Doctrine\Entity\RtbInfos;
use App\Doctrine\Entity\SatellitePages\Claim;
use App\Doctrine\Entity\SatellitePages\SatellitePage;
use App\Doctrine\Entity\SatellitePages\TechnicalDetail;
use App\Doctrine\Entity\SatellitePages\TechnicalInfo;
use App\Form\Type\ProductVariantAutocompleteChoiceType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SatellitePageType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'productVariant',
                ProductVariantAutocompleteChoiceType::class,
                [
                    'label' => 'app.form.bloc_campaign.variants.search_variant',
                    'required' => true,
                ]
            )
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius.ui.enabled',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => SatellitePageTranslationType::class,
                'label' => false,
            ])
            ->add('claims', CollectionType::class, [
                'entry_type' => ClaimType::class,
                'label' => false,
            ])
            ->add('technicalInfos', CollectionType::class, [
                'entry_type' => TechnicalInfoType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
            ])
            ->add(
                'rtbInfos',
                EntityType::class,
                [
                    'class' => RtbInfos::class,
                    'choice_label' => 'label',
                    'multiple' => true,
                ]
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $data = $event->getData();
                for ($i = $data->getClaims()->count(); $i < Claim::MAX_CLAIMS; ++$i) {
                    $claim = new Claim();
                    $data->addClaim($claim);
                }
            })
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                /** @var SatellitePage $data */
                $data = $event->getData();
                $totalInfos = $data->getTechnicalInfos()->count();
                for ($i = $totalInfos; $i < 2; ++$i) {
                    $tempInfo = new TechnicalInfo();
                    for ($j = 0; $j < 3; ++$j) {
                        $tempDetail = new TechnicalDetail();
                        $tempDetail->setCurrentLocale('fr_FR');
                        $tempInfo->addTechnicalDetail($tempDetail);
                    }
                    $data->addTechnicalInfo($tempInfo);
                }
            })
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_satellite_page';
    }
}
