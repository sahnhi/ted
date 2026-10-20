<?php

declare(strict_types=1);

namespace App\Form\Type\SatellitePages;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ClaimTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('claim', TextType::class, [
            'label' => 'app.form.satellite_page.claim_field_label',
            'required' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_satellite_page_claim_translation';
    }
}
