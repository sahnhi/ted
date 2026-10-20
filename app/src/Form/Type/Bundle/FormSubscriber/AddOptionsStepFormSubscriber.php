<?php

namespace App\Form\Type\Bundle\FormSubscriber;

use App\Doctrine\Entity\Sylius\ShippingCategory;
use App\Form\Type\Bundle\BundleOptionConfigurationType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class AddOptionsStepFormSubscriber implements EventSubscriberInterface
{
    public const BUNDLE_GENERATION_STEP = 'options';

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SET_DATA => 'postSetData',
        ];
    }

    public function postSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        $form->add('options', CollectionType::class, [
                'entry_type' => BundleOptionConfigurationType::class,
                'label' => false,
                'prototype' => false,
        ])
            ->add('shippingCategory', EntityType::class, [
                'class' => ShippingCategory::class,
                'label' => 'sylius.ui.shipping_categories',
                'required' => true,
                'placeholder' => '',
            ])
            ->add('nbProducts', IntegerType::class, [
                'label' => 'app.ui.bundle_association.labels.number_of_products',
                'required' => true,
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 2,
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
        ;
    }
}
