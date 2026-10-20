<?php

namespace App\Form\Type\Bundle\FormSubscriber;

use App\Form\Type\Bundle\BundleCombinationConfigurationType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AddPreviewStepFormSubscriber implements EventSubscriberInterface
{
    public const BUNDLE_GENERATION_STEP = 'preview';

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SET_DATA => 'postSetData',
        ];
    }

    public function postSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        $form->add('combinations', CollectionType::class, [
            'entry_type' => BundleCombinationConfigurationType::class,
            'label' => false,
            'entry_options' => [
                'label' => false,
            ],
        ]);
    }
}
