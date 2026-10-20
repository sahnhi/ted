<?php

declare(strict_types=1);

namespace App\Form\Type\SatellitePages;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TechnicalDetailType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, [
            'label' => 'app.form.technical_detail.title_field_label',
        ]);
        $builder->add('description', TextareaType::class, [
            'label' => 'app.form.technical_detail.description_field_label',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_satellite_page_technical_detail';
    }
}
