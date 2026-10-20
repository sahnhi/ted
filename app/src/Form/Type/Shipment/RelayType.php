<?php

namespace App\Form\Type\Shipment;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class RelayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company', HiddenType::class, [
                'attr' => ['data-relay-company' => 'true'],
            ])
            ->add('street', HiddenType::class, [
                'attr' => ['data-relay-street' => 'true'],
            ])
            ->add('postcode', HiddenType::class, [
                'attr' => ['data-relay-postcode' => 'true'],
            ])
            ->add('city', HiddenType::class, [
                'attr' => ['data-relay-city' => 'true'],
            ])
            ->add('countryCode', HiddenType::class, [
                'attr' => ['data-relay-country-code' => 'true'],
            ])
            ->add('details', HiddenType::class, [
                'attr' => ['data-relay-details' => 'true'],
            ]);
    }
}
