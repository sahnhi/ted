<?php

namespace App\Form\Extension;

use App\Doctrine\Entity\Sylius\ProductOptionValue;
use Sylius\Bundle\ProductBundle\Form\Type\ProductOptionValueChoiceType;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class ProductOptionValueChoiceTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $roles = $this->tokenStorage->getToken()->getRoles();
        $adminRoles = ['ROLE_ADMINISTRATION_ACCESS', 'ROLE_ADMIN'];

        $resolver
            ->setDefaults([
                'choices' => function (Options $options) use ($adminRoles, $roles) {
                    foreach ($roles as $role) {
                        if (in_array($role->getRole(), $adminRoles, true)) {
                            return $options['option']->getValues();
                        }
                    }

                    $optionValues = [];
                    /** @var ProductOptionValue $optionValue */
                    foreach ($options['option']->getValues() as $optionValue) {
                        if ($optionValue->getEnabled()) {
                            $optionValues[] = $optionValue;
                        }
                    }

                    return $optionValues;
                },
                'choice_value' => 'code',
                'choice_label' => 'value',
                'choice_translation_domain' => false,
            ])
            ->setRequired([
                'option',
            ])
            ->addAllowedTypes('option', [ProductOptionInterface::class])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductOptionValueChoiceType::class];
    }
}
