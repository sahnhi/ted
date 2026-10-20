<?php

namespace App\Form\Extension;

use Sylius\Bundle\CoreBundle\Form\Type\Customer\CustomerGuestType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

final class CustomerGuestTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('subscribedToNewsletter', CheckboxType::class, [
            'label' => 'sylius.form.customer.subscribed_to_newsletter',
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [CustomerGuestType::class];
    }
}
