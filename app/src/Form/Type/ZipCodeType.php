<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ZipCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('country', TextType::class, [
                'label' => 'sylius.ui.zip_code.country',
            ])
            ->add('code', TextType::class, [
                'label' => 'sylius.ui.zip_code.code',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_zip_code';
    }
}
