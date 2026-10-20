<?php

namespace App\Form\Extension;

use App\Form\Type\NewOrderType;
use App\Services\OrderReturn\ReturnType\AutonomousService;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class AutonomousReturnNewOrderTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly AutonomousService $autonomous
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
            if (!empty($options['attr']['returnType']) && ReturnTypeServiceInterface::RETURN_TYPE_AUTONOMOUS === $options['attr']['returnType']) {
                $event->getForm()->remove('shipments');
            }
        })->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options): void {
            if (!empty($options['attr']['returnType']) && ReturnTypeServiceInterface::RETURN_TYPE_AUTONOMOUS === $options['attr']['returnType']) {
                try {
                    $orderData = $event->getData();
                    $form = $event->getForm();

                    $this->autonomous->checkCarrier($orderData);
                } catch (BadRequestException $e) {
                    $form->addError(new FormError($e->getMessage()));
                }
            }
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [NewOrderType::class];
    }
}
