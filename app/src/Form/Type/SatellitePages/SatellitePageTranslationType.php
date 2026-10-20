<?php

declare(strict_types=1);

namespace App\Form\Type\SatellitePages;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class SatellitePageTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('satelliteUrl', TextType::class, [
                'label' => 'app.form.satellite_page.satellite_url',
                'required' => true,
            ])
            ->add('name', TextType::class, [
                'label' => 'app.form.satellite_page.name',
                'required' => false,
            ])
            ->add('mainPhrase', TextType::class, [
                'label' => 'app.form.satellite_page.main_phrase',
                'required' => false,
            ])
            ->add('tag', TextType::class, [
                'label' => 'app.form.satellite_page.tag',
                'required' => false,
            ])
            ->add('qualityClaim', TextType::class, [
                'label' => 'app.form.satellite_page.quality_claim',
                'required' => false,
            ])
            ->add('technicalClaim', TextType::class, [
                'label' => 'app.form.satellite_page.technical_claim',
                'required' => false,
            ])
            ->add('focus', TextareaType::class, [
                'label' => 'app.form.satellite_page.focus',
                'required' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_satellite_page_translation';
    }
}
