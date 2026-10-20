<?php

namespace App\Form\Extension;

use Sylius\Bundle\AddressingBundle\Form\Type\CountryType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

final class CountryTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('paidReturn', CheckboxType::class, [
            'label' => 'sylius.ui.country.paidReturn',
            'required' => false,
        ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [CountryType::class];
    }
}
