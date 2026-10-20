<?php

namespace App\Form\Type\Payment;

use App\Legacy\Younited\YounitedApiInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

class YounitedGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('environment', ChoiceType::class, [
                'choices' => [
                    'app.ui.gateway.younited.form.environment.choices.production' => YounitedApiInterface::PRODUCTION,
                    'app.ui.gateway.younited.form.environment.choices.development' => YounitedApiInterface::DEVELOPMENT,
                ],
                'label' => 'app.ui.gateway.younited.form.environment.label',
            ])
            ->add('client_id', TextType::class, [
                'label' => 'app.ui.gateway.younited.form.client_id.label',
                'constraints' => [
                    new NotBlank([
                        'message' => 'app.ui.gateway.younited.form.client_id.validate.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('client_secret', TextType::class, [
                'label' => 'app.ui.gateway.younited.form.client_secret.label',
                'constraints' => [
                    new NotBlank([
                        'message' => 'app.ui.gateway.younited.form.client_secret.validate.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('resource', TextType::class, [
                'label' => 'app.ui.gateway.younited.form.resource.label',
                'constraints' => [
                    new NotBlank([
                        'message' => 'app.ui.gateway.younited.form.resource.validate.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
        ;

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $data = $event->getData();
                $data['payum.http_client'] = '@app.bridge.younited_bridge';
                $event->setData($data);
            })
        ;
    }
}
