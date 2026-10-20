<?php

namespace App\Form\Type;

use App\Doctrine\Entity\BundleAssociation;
use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceAutocompleteChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class BundleAssociationType extends AbstractResourceType
{
    public function __construct(array $validationGroups = [])
    {
        parent::__construct(BundleAssociation::class, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'variant',
            ResourceAutocompleteChoiceType::class,
            [
                'disabled' => true,
                'label' => false,
                'choice_name' => 'descriptor',
                'choice_value' => 'code',
                'resource' => 'sylius.product_variant',
                'constraints' => [
                    new NotBlank(
                        [
                            'groups' => 'app_product_variant',
                        ]
                    ),
                ],
            ]
        )
            ->add(
                'quantity',
                IntegerType::class,
                [
                    'label' => false,
                    'disabled' => true,
                    'constraints' => [
                        new NotBlank(
                            [
                                'groups' => 'app_product_variant',
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'price',
                MoneyType::class,
                [
                    'label' => false,
                    'constraints' => [
                        new NotBlank(
                            [
                                'groups' => 'app_product_variant',
                            ]
                        ),
                    ],
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'app_admin_product_variant_bundle_association';
    }
}
