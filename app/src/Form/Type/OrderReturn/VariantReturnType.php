<?php

namespace App\Form\Type\OrderReturn;

use App\Doctrine\Entity\OrderReturnItem;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Services\OrderReturn\EnumOrderReturnDestination;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VariantReturnType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $variantIds = !empty($options['attr']) ? $options['attr'] : null;
        $builder
            ->add('quantity', IntegerType::class, [
                'label' => 'sylius.form.order_return.items.quantity',
                'required' => false,
            ])
            ->add('variant', EntityType::class, [
                'class' => ProductVariant::class,
                'choice_label' => 'name',
                'choice_value' => 'code',
                'label' => 'sylius.form.order_return.items.product',
                'required' => false,
                'query_builder' => function (EntityRepository $e) use ($variantIds) {
                    $query = $e->createQueryBuilder('s');
                    if (null !== $variantIds) {
                        $query->andWhere('s.id IN (:ids)')
                            ->setParameter('ids', $variantIds);
                    }

                    return $query;
                },
            ])
            ->add('destination', ChoiceType::class, [
                'choices' => EnumOrderReturnDestination::AVAILABLE_DESTINATION_TYPE,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => OrderReturnItem::class]);
    }
}
