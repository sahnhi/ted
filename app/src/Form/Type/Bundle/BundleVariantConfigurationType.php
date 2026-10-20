<?php

namespace App\Form\Type\Bundle;

use App\Doctrine\Entity\BundleGeneratorVariant;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BundleVariantConfigurationType extends AbstractType
{
    public function __construct(
        private readonly LocaleContextInterface $localeContext
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            /** @var BundleGeneratorVariant $data */
            $data = $event->getData();
            $form = $event->getForm();

            $product = $data->getProduct()->getProduct();

            $form->add('variant', EntityType::class, [
                'class' => ProductVariant::class,
                'label' => false,
                'required' => false,
                'query_builder' => fn (ProductVariantRepositoryInterface $productVariantRepository): QueryBuilder => $productVariantRepository->createQueryBuilderByProductId($this->localeContext->getLocaleCode(), $product->getId())->orderBy('o.code', 'ASC'),
                'choice_label' => 'code',
                'placeholder' => 'sylius.ui.select',
                'attr' => [
                    'class' => 'tabulator-select2 variant-selector',
                    'data-product' => $product->getId(),
                ],
                'constraints' => [
                    new NotBlank(['groups' => 'sylius']),
                ],
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BundleGeneratorVariant::class,
            'validation_groups' => ['sylius'],
        ]);
    }
}
