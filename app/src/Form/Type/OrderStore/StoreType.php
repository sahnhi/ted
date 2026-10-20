<?php

namespace App\Form\Type\OrderStore;

use App\Doctrine\Entity\Store;
use Sylius\Component\Core\Model\Channel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'sylius.ui.name'])
            ->add('company', TextType::class, ['label' => 'sylius.ui.company'])
            ->add('terminalId', IntegerType::class, ['label' => 'sylius.ui.terminalId'])
            ->add('channel', EntityType::class, [
                'class' => Channel::class,
                'choice_label' => 'name',
                'label' => 'sylius.ui.channel',
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'sylius.ui.phone_number',
                'required' => false,
            ])
            ->add('location', TextType::class, [
                'label' => 'sylius.ui.location',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Store::class]);
    }
}
