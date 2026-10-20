<?php

namespace App\Form\Extension;

use App\Doctrine\Entity\Pack;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Form\Type\BundleAssociationType;
use App\Form\Type\ProductGoogleRssType;
use App\Form\Type\ProductVariantEcotaxType;
use App\Form\Type\ShippingStockType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantType;
use Sylius\Bundle\TaxationBundle\Form\Type\TaxCategoryChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;

final class ProductVariantTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('packs', EntityType::class, [
                'class' => Pack::class,
                'multiple' => true,
                'required' => false,
                'label' => 'sylius.form.product.variant.packs',
                'attr' => ['class' => 'select2'],
                'group_by' => function ($pack, $key, $value) {
                    $splited = explode('_', (string) $pack->getCode());

                    return array_shift($splited);
                },
            ])
            ->add('taxCategory', TaxCategoryChoiceType::class, [
                'required' => false,
                'placeholder' => null,
                'label' => 'sylius.form.product_variant.tax_category',
            ])
            ->add('productVariantEcotax', CollectionType::class, [
                'label' => false,
                'entry_type' => ProductVariantEcotaxType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('shippingStock', CollectionType::class, [
                'label' => false,
                'entry_type' => ShippingStockType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'button_add_label' => 'sylius.ui.shipping_stock.add_shipping_date',
            ])
            ->add('productGoogleRss', ProductGoogleRssType::class, [
                'required' => false,
                'label' => 'sylius.ui.products.google_rss',
            ])
            ->add('excludeFromPromotions', CheckboxType::class, [
                'label' => 'app.ui.product_variant.exclude_from_promotions',
                'required' => false,
            ])
            ->add('joinStoryLink', TextareaType::class, [
                'required' => false,
                'label' => 'sylius.ui.product.join_story_link',
            ]);

        $variant = $builder->getData();

        if ($variant instanceof ProductVariantInterface && $variant->isBundle()) {
            $builder->add('bundleAssociations', CollectionType::class, [
                'entry_type' => BundleAssociationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => false,
                'label' => false,
                'constraints' => [
                    new Valid([
                        'groups' => ['app_product_variant'],
                    ]),
                ],
            ]);
        }

        if ($builder->get('productGoogleRss')->has('urlReview')) {
            $builder->get('productGoogleRss')->remove('urlReview');
        }
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductVariantType::class];
    }
}
