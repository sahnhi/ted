<?php

namespace App\Form\Type\Payment;

use Sylius\Component\Core\Model\Payment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionIdType extends AbstractType
{
    public const NAME = 'sylius_payment_transaction_id';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('transactionId', TextType::class, [
            'required' => true,
            'mapped' => false,
            'label' => false,
        ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $payment = $event->getData();
            $details = $payment->getDetails();

            if (!empty($details['TRANSACTIONID'])) {
                $event->getForm()->get('transactionId')->setData($details['TRANSACTIONID']);
            }
        });
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
            'label' => false,
        ]);
    }
}
