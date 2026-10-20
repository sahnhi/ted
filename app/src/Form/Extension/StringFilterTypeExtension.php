<?php

namespace App\Form\Extension;

use Sylius\Bundle\GridBundle\Form\Type\Filter\StringFilterType;
use Sylius\Component\Grid\Filter\StringFilter;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

final class StringFilterTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!isset($options['type'])) {
            if (isset($options['first_type']) && isset($options['choices_types'])) {
                $options['choices_types'] = $this->unshiftType($options['choices_types'], $options['first_type']);
            }

            $builder
                ->add('type', ChoiceType::class, [
                    'choices' => $options['choices_types'],
                ])
            ;
        }
    }

    public static function getExtendedTypes(): iterable
    {
        return [StringFilterType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choices_types' => [
                    'sylius.ui.equal' => StringFilter::TYPE_EQUAL,
                    'sylius.ui.contains' => StringFilter::TYPE_CONTAINS,
                    'sylius.ui.not_contains' => StringFilter::TYPE_NOT_CONTAINS,
                    'sylius.ui.not_equal' => StringFilter::TYPE_NOT_EQUAL,
                    'sylius.ui.empty' => StringFilter::TYPE_EMPTY,
                    'sylius.ui.not_empty' => StringFilter::TYPE_NOT_EMPTY,
                    'sylius.ui.starts_with' => StringFilter::TYPE_STARTS_WITH,
                    'sylius.ui.ends_with' => StringFilter::TYPE_ENDS_WITH,
                    'sylius.ui.in' => StringFilter::TYPE_IN,
                    'sylius.ui.not_in' => StringFilter::TYPE_NOT_IN,
                ],
                'first_type' => StringFilter::TYPE_EQUAL,
            ])
        ;
    }

    private function unshiftType(array $types, string $type): array
    {
        $key = array_search($type, $types);
        Assert::notEq($key, false);

        $value = $types[$key];
        unset($types[$key]);

        return [$key => $value] + $types;
    }
}
