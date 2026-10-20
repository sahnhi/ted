<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\Reason;
use App\Form\Type\Reason\BisSupportType;
use App\Services\Channel\ChannelCode;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CancelOrderType extends AbstractType
{
    public function __construct(
        private ReturnTypeServiceInterface $returnTypeService
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('orderReason', CollectionType::class, [
            'label' => false,
            'entry_type' => BisSupportType::class,
            'entry_options' => [
                'user' => $options['user'],
                'constraints' => $options['constraints'],
                'attr' => $options['attr'],
            ],
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event): void {
            $order = $event->getData();
            $order->addOrderReason(new OrderReason());
            $event->setData($order);
        });

        $returnTypeService = $this->returnTypeService;
        $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event) use ($returnTypeService): void {
            $order = $event->getData();

            if (ChannelCode::CHANNEL_RETURN === $order->getChannel()->getCode() && Reason::RETURN_TYPE_SENDER === $returnTypeService->getReturnType($order)) {
                $order->setState(OrderInterface::STATE_NEW);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['user' => null]);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_order_cancel';
    }
}
