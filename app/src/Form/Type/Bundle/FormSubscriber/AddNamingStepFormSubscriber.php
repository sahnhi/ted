<?php

namespace App\Form\Type\Bundle\FormSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddNamingStepFormSubscriber implements EventSubscriberInterface
{
    public const BUNDLE_GENERATION_STEP = 'naming';

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SET_DATA => 'postSetData',
        ];
    }

    public function postSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $form->add('prefix', TextType::class, [
                'label' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
        ])
            ->add('suffix', TextType::class, [
                'label' => false,
                'required' => false,
                'empty_data' => '',
            ])
        ;
    }
}
