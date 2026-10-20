<?php

namespace App\Form\Type\Bundle\FormSubscriber;

use App\Form\Type\Bundle\BundleCombinationConfigurationType;
use App\Form\Type\Bundle\BundleProductConfigurationType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddProductsStepFormSubscriber implements EventSubscriberInterface
{
    public const BUNDLE_GENERATION_STEP = 'products';

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SET_DATA => 'postSetData',
        ];
    }

    public function postSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        $form->add('products', CollectionType::class, [
                'entry_type' => BundleProductConfigurationType::class,
                'label' => false,
                'constraints' => [
                    new NotBlank(['groups' => 'sylius']),
                ],
        ])
            ->add('combinations', CollectionType::class, [
                'entry_type' => BundleCombinationConfigurationType::class,
            ])
        ;
    }
}
