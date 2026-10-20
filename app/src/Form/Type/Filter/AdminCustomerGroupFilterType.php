<?php

namespace App\Form\Type\Filter;

use App\Doctrine\Entity\Sylius\CustomerGroupInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminCustomerGroupFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($builder->getName(), ChoiceType::class, [
                'label' => 'app.form.customer_group.label',
                'choices' => [
                    'Tous' => '',
                    'app.form.customer_group.recommendee_code' => CustomerGroupInterface::GROUP_RECOMMENDEE_CODE,
                    'app.form.customer_group.sponsor_code' => CustomerGroupInterface::GROUP_SPONSOR_CODE,
                    'app.form.customer_group.sponsor_recommendee_code' => CustomerGroupInterface::GROUP_SPONSOR_RECOMMENDEE_CODE,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'locale' => [],
            ])
            ->setAllowedTypes('locale', ['array'])
        ;
    }
}
