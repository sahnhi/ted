<?php

namespace App\Form\Type;

use App\Doctrine\Entity\Ecotax;
use App\Doctrine\Entity\ProductVariantEcotax;
use App\Doctrine\Entity\Sylius\ProductVariant;
use Doctrine\ORM\EntityRepository;
use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcotaxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $productVariantEcotaxPaid = $productVariantEcotaxNotPaid = $productVariantEcotaxReturn = [];
        foreach ($builder->getData()->getProductVariantEcotax() as $productVariantEcotax) {
            switch ($productVariantEcotax->getManufacturerPaid()) {
                case 1:
                    $productVariantEcotaxPaid[] = $productVariantEcotax->getVariant();
                    break;
                case 0:
                    $productVariantEcotaxNotPaid[] = $productVariantEcotax->getVariant();
                    break;
                case 2:
                    $productVariantEcotaxReturn[] = $productVariantEcotax->getVariant();
                    break;
            }
        }
        $builder
            ->add(
                'code',
                TextType::class,
                [
                    'label' => 'sylius.form.ecotax.code',
                    'empty_data' => 0,
                    'required' => true,
                ]
            )
            ->add(
                'amountHT',
                MoneyType::class,
                [
                    'label' => 'sylius.form.ecotax.amountHT',
                    'required' => true,
                ]
            )
            ->add(
                'amountTTC',
                MoneyType::class,
                [
                    'label' => 'sylius.form.ecotax.amountTTC',
                    'empty_data' => 0,
                    'required' => true,
                ]
            )
            ->addEventListener(
                FormEvents::POST_SET_DATA,
                function (FormEvent $event) use ($productVariantEcotaxPaid, $productVariantEcotaxNotPaid, $productVariantEcotaxReturn): void {
                    $event->getForm()->get('productVariantEcotaxPaid')->setData($productVariantEcotaxPaid);
                    $event->getForm()->get('productVariantEcotaxNotPaid')->setData($productVariantEcotaxNotPaid);
                    $event->getForm()->get('productVariantEcotaxReturn')->setData($productVariantEcotaxReturn);
                }
            )
            ->add(
                'productVariantEcotaxPaid',
                EntityType::class,
                [
                    'class' => ProductVariant::class,
                    'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('p'),
                    'attr' => [
                        'class' => 'select2',
                    ],
                    'multiple' => true,
                    'required' => false,
                    'mapped' => false,
                    'choice_label' => fn ($entity): string => $entity->getName() . ' (' . $entity->getCode() . ')',
                ]
            )
            ->add(
                'productVariantEcotaxNotPaid',
                EntityType::class,
                [
                    'class' => ProductVariant::class,
                    'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('p'),
                    'attr' => [
                        'class' => 'select2',
                    ],
                    'multiple' => true,
                    'required' => false,
                    'mapped' => false,
                    'choice_label' => fn ($entity): string => $entity->getName() . ' (' . $entity->getCode() . ')',
                ]
            )
            ->add(
                'productVariantEcotaxReturn',
                EntityType::class,
                [
                    'class' => ProductVariant::class,
                    'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('p'),
                    'attr' => [
                        'class' => 'select2',
                    ],
                    'multiple' => true,
                    'required' => false,
                    'mapped' => false,
                    'choice_label' => fn ($entity): string => $entity->getName() . ' (' . $entity->getCode() . ')',
                ]
            )
            ->addEventListener(
                FormEvents::SUBMIT,
                function (FormEvent $event): void {
                    $ecotaxData = $event->getData();
                    $ecotaxPaid = $event->getForm()->get('productVariantEcotaxPaid')->getData();
                    $ecotaxNotPaid = $event->getForm()->get('productVariantEcotaxNotPaid')->getData();
                    $ecotaxReturn = $event->getForm()->get('productVariantEcotaxReturn')->getData();

                    foreach ($ecotaxData->getProductVariantEcotax() as $variantEcotax) {
                        $ecotaxData->removeProductVariantEcotax($variantEcotax);
                    }

                    if (!empty($ecotaxPaid)) {
                        foreach ($ecotaxPaid as $productVariant) {
                            $productVariantEcotax = new ProductVariantEcotax();
                            $productVariantEcotax->setVariant($productVariant);
                            $productVariantEcotax->setManufacturerPaid(ProductVariantEcotax::MANUFACTURER_PAID);
                            $ecotaxData->addProductVariantEcotax($productVariantEcotax);
                        }
                    }
                    if (!empty($ecotaxNotPaid)) {
                        foreach ($ecotaxNotPaid as $productVariant) {
                            $productVariantEcotax = new ProductVariantEcotax();
                            $productVariantEcotax->setVariant($productVariant);
                            $productVariantEcotax->setManufacturerPaid(ProductVariantEcotax::MANUFACTURER_NOTPAID);
                            $ecotaxData->addProductVariantEcotax($productVariantEcotax);
                        }
                    }
                    if (!empty($ecotaxReturn)) {
                        foreach ($ecotaxReturn as $productVariant) {
                            $productVariantEcotax = new ProductVariantEcotax();
                            $productVariantEcotax->setVariant($productVariant);
                            $productVariantEcotax->setManufacturerPaid(ProductVariantEcotax::MANUFACTURER_RETURN);
                            $ecotaxData->addProductVariantEcotax($productVariantEcotax);
                        }
                    }
                }
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Ecotax::class,
            ]
        );
    }
}
