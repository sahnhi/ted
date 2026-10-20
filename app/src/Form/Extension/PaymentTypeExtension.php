<?php

namespace App\Form\Extension;

use App\Doctrine\Entity\Sylius\PaymentMethod;
use Doctrine\ORM\EntityRepository;
use Sylius\AdminOrderCreationPlugin\Form\Type\PaymentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaymentTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('method', EntityType::class, [
            'required' => true,
            'label' => 'sylius.form.checkout.payment_method',
            'class' => PaymentMethod::class,
            'query_builder' => fn (EntityRepository $entityRepository) => $options['channel'] ? $entityRepository->createQueryBuilder('pm')
                ->join('pm.channels', 'c')
                ->where('c.code = :code')
                ->setParameter('code', $options['channel']->getCode()) : null,
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [PaymentType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'channel' => null,
        ]);
    }
}
