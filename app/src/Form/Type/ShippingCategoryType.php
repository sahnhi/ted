<?php

namespace App\Form\Type;

use App\Doctrine\Entity\Sylius\ShippingMethod;
use Doctrine\ORM\EntityRepository;
use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ShippingCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventSubscriber(new AddCodeFormSubscriber())
            ->add('name', TextType::class, [
                'label' => 'sylius.form.shipping_category.name',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => ShippingCategoryTranslationType::class,
                'label' => 'sylius.form.pack.translations',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'sylius.form.shipping_category.description',
            ])
            ->add('shippingMethod', EntityType::class, [
                'label' => 'sylius.ui.shipping_category.shipping_method',
                'class' => ShippingMethod::class,
                'attr' => [
                    'data-form-collection' => 'update',
                ],
                'multiple' => true,
                'required' => false,
                'expanded' => true,
                'query_builder' => static fn (EntityRepository $entityRepository) => $entityRepository->createQueryBuilder('s')
                    ->andWhere('s.archivedAt IS NULL'),
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_shipping_category';
    }
}
