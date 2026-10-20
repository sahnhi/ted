<?php

namespace App\Form\Type\Filter;

use App\Doctrine\Entity\OrderReturnStatus;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminOrderReturnStatusFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ('savStatus' === $builder->getName()) {
            $type = OrderReturnStatus::TYPE_SAV;
            $choiceLabel = 'savStatus';
        } else {
            $type = OrderReturnStatus::TYPE_WAREHOUSE;
            $choiceLabel = 'warehouseStatus';
        }
        $builder
            ->add($builder->getName(), EntityType::class, [
                'class' => OrderReturnStatus::class,
                'label' => false,
                'placeholder' => 'sylius.form.order_return.sav_placeholder',
                'required' => false,
                'query_builder' => fn (EntityRepository $e) => $e->createQueryBuilder('s')
                    ->where('s.statusType = :type')
                    ->setParameter('type', $type),
                'choice_label' => $choiceLabel,
                'choice_translation_domain' => 'order-return-status',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'state' => [],
            ])
            ->setAllowedTypes('state', ['array'])
        ;
    }
}
