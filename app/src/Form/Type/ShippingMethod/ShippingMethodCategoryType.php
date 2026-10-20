<?php

declare(strict_types=1);

namespace App\Form\Type\ShippingMethod;

use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ShippingMethodCategoryType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventSubscriber(new AddCodeFormSubscriber(
                options: ['constraints' => [new NotBlank(['groups' => $this->validationGroups])]]
            ))
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius.ui.enabled',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'label' => 'sylius.ui.translations',
                'entry_type' => ShippingMethodCategoryTranslationType::class,
            ])
            ->add('claims', CollectionType::class, [
                'label' => false,
                'entry_type' => ShippingMethodCategoryClaimType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('shippingMethods', CollectionType::class, [
                'label' => false,
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => ShippingMethod::class,
                    'choice_label' => static fn (ShippingMethod $shippingMethod): string => sprintf('%s (%s)', $shippingMethod->getName(), $shippingMethod->getCode()),
                    'query_builder' => function (EntityRepository $entityRepository) use ($builder): QueryBuilder {
                        $queryBuilder = $entityRepository
                            ->createQueryBuilder('sm')
                            ->andWhere('sm.shippingMethodCategory IS NULL')
                            ->orderBy('sm.position');

                        /** @var ShippingMethodCategory $shippingMethodCategory */
                        $shippingMethodCategory = $builder->getData();
                        if (null !== $shippingMethodCategory->getId()) {
                            $queryBuilder
                                ->orWhere('sm.shippingMethodCategory = :shippingMethodCategory')
                                ->setParameter('shippingMethodCategory', $shippingMethodCategory);
                        }

                        return $queryBuilder;
                    },
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('shippingMethodCategoryServices', CollectionType::class, [
                'label' => false,
                'entry_type' => ShippingMethodCategoryServiceType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);
    }
}
