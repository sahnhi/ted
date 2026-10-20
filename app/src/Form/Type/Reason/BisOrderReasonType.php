<?php

namespace App\Form\Type\Reason;

use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\Reason;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BisOrderReasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reason', SupportType::class, [
                'inherit_data' => true,
                'label' => false,
                'channel' => 3, // Specify channel to remove unwanted reason
                'user' => $options['user'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderReason::class,
            'user' => null,
        ]);
    }
}
