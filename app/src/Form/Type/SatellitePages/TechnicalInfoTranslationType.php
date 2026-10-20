<?php

declare(strict_types=1);

namespace App\Form\Type\SatellitePages;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TechnicalInfoTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'app.form.satellite_page.technical_info_title',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_satellite_page_technical_info_translation';
    }
}
