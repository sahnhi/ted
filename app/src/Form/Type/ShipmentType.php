<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Warehouse;
use App\Doctrine\Repository\WarehouseRepository;
use App\Form\Type\Shipment\RelayType;
use App\Services\Channel\ChannelCode;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ShipmentType extends AbstractResourceType
{
    private readonly ?Warehouse $warehouse;

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function __construct(array $validationGroups = [], ?WarehouseRepository $warehouseRepository = null)
    {
        parent::__construct(Shipment::class, $validationGroups);
        $this->warehouse = $warehouseRepository?->findDefault();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('method', ShippingMethodChoiceType::class, [
                'required' => true,
                'label' => 'sylius.form.checkout.shipping_method',
                'attr' => ['data-shipment' => 'true'],
            ])
            ->add('startDeliverySlot', DateTimeType::class, [
                'date_widget' => 'single_text',
                'attr' => ['data-start-slot' => 'true'],
                'required' => false,
                'constraints' => [
                    new Callback([
                        'callback' => function ($data, ExecutionContextInterface $context): void {
                            /** @var FormInterface $form */
                            $form = $context->getObject();

                            if (null === $data && null === $form->getParent()->get('relayId')->getData()) {
                                $context
                                    ->buildViolation('Cannot be empty')
                                    ->addViolation();
                            }
                        },
                        'groups' => ['sylius'],
                    ]),
                ],
                'data' => new \DateTimeImmutable(),
            ])
            ->add('endDeliverySlot', DateTimeType::class, [
                'date_widget' => 'single_text',
                'attr' => ['data-end-slot' => 'true'],
                'required' => false,
                'constraints' => [
                    new Callback([
                        'callback' => function ($data, ExecutionContextInterface $context): void {
                            /** @var FormInterface $form */
                            $form = $context->getObject();

                            if (null === $data && null === $form->getParent()->get('relayId')->getData()) {
                                $context
                                    ->buildViolation('Cannot be empty')
                                    ->addViolation();
                            }
                        },
                        'groups' => ['sylius'],
                    ]),
                ],
                'data' => new \DateTimeImmutable('+1 hour'),
            ])
            ->add('idShippingMethodBalou', null, [
                'attr' => ['data-shipment-id' => 'true'],
                'required' => true,
            ])
            ->add('relayId', null, [
                'attr' => ['data-relay-id' => 'true'],
                'required' => true,
            ])
            ->add('relay', RelayType::class, [
                'mapped' => false,
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options): void {
            $form = $event->getForm();
            $channel = $options['channel'] ?? null;

            if (null !== $channel && ChannelCode::CHANNEL_RETURN === $channel->getCode()) {
                $form->add('warehouse', EntityType::class, [
                    'class' => Warehouse::class,
                    'required' => false,
                    'empty_data' => null,
                    'placeholder' => 'sylius.ui.warehouse_empty',
                    'label' => 'sylius.ui.service_rule.warehouse',
                    'data' => $this->warehouse,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'channel' => null,
        ]);
    }
}
