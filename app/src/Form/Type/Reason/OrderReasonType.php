<?php

namespace App\Form\Type\Reason;

use App\Doctrine\Entity\Sylius\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderReasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderReason', CollectionType::class, [
                'label' => false,
                'entry_type' => SupportType::class,
                'entry_options' => [
                    'order' => $options['order'],
                    'user' => $options['user'],
                    'attr' => $options['attr'],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'button_add_label' => 'sylius.ui.trouble_new',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'order' => null,
            'user' => null,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'order_reason';
    }
}
