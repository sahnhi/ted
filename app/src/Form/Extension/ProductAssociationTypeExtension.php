<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Doctrine\Entity\Sylius\ProductAssociationTypeInterface;
use Sylius\Bundle\ProductBundle\Form\Type\ProductAssociationsType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProductAssociationTypeExtension extends AbstractTypeExtension
{
    private const PRODUCT_CROSS_SELLING_MAX_PRODUCTS = 7;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($builder->has(ProductAssociationTypeInterface::TYPE_PRODUCT_CROSS_SELLING)) {
            $baseForm = $builder->get(ProductAssociationTypeInterface::TYPE_PRODUCT_CROSS_SELLING);

            $builder->remove(ProductAssociationTypeInterface::TYPE_PRODUCT_CROSS_SELLING);

            $builder->add(ProductAssociationTypeInterface::TYPE_PRODUCT_CROSS_SELLING, TextType::class, [
                ...$baseForm->getOptions(),
                ...[
                    'constraints' => [
                        new Callback([
                            'callback' => static function (string|null $value, ExecutionContextInterface $context): void {
                                if (null === $value) {
                                    return;
                                }

                                $associatedProducts = explode(',', $value);

                                if (count($associatedProducts) > self::PRODUCT_CROSS_SELLING_MAX_PRODUCTS) {
                                    $context->buildViolation('app.product_association.max_count_error')
                                        ->setParameter('{{limit}}', self::PRODUCT_CROSS_SELLING_MAX_PRODUCTS)
                                        ->addViolation();
                                }
                            },
                            'groups' => ['sylius', 'default'],
                        ]),
                    ],
                ],
            ]);
        }
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductAssociationsType::class];
    }
}
