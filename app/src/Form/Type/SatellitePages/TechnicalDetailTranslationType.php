<?php

declare(strict_types=1);

namespace App\Form\Type\SatellitePages;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TechnicalDetailTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'app.form.satellite_page.technical_detail_title',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'app.form.satellite_page.technical_detail_description',
            ])
        ;
    }
}
