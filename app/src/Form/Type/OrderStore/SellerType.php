<?php

namespace App\Form\Type\OrderStore;

use App\Doctrine\Entity\Seller;
use App\Doctrine\Entity\Store;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SellerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'sylius.ui.name'])
            ->add('email', EmailType::class, ['label' => 'sylius.ui.email'])
            ->add('store', EntityType::class, [
                'class' => Store::class,
                'choice_label' => 'name',
                'label' => 'sylius.form.store.label',
                'placeholder' => 'app.form.order.store.placeholder',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Seller::class]);
    }
}
