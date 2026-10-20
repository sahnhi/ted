<?php

namespace App\Form\Extension;

use App\Doctrine\Entity\Familiarity;
use App\Doctrine\Entity\Friend;
use App\Doctrine\Entity\Osteopath;
use App\Doctrine\Entity\Sylius\PaymentMethod;
use App\Model\Role;
use Doctrine\ORM\EntityRepository;
use Sylius\Bundle\CustomerBundle\Form\Type\CustomerType;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class CustomerTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly RepositoryInterface $localeRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->authorizationChecker->isGranted(Role::ROLE_MANAGER)) {
            $builder
                ->add('paymentMethod', EntityType::class, [
                    'class' => PaymentMethod::class,
                    'required' => false,
                ])
            ;
        }

        $localeParse = [];
        foreach ($this->localeRepository->findAll() as $locale) {
            $localeParse[$locale->getName()] = substr((string) $locale->getCode(), 0, 2);
        }

        $friendId = 0;
        if (!empty($options['data']->getFriend())) {
            $friendId = $options['data']->getFriend()->getId();
        }

        $osteopathId = 0;
        if (!empty($options['data']->getOsteopath())) {
            $osteopathId = $options['data']->getOsteopath();
        }

        $builder
            ->add('locale', ChoiceType::class, [
                'choices' => $localeParse,
                'placeholder' => 'sylius.form.locale.select',
            ])
            ->add('familiarities', EntityType::class, [
                'class' => Familiarity::class,
                'multiple' => true,
                'expanded' => true,
                'label' => 'sylius.form.familiarities',
            ])
            ->add('referral', TextType::class)
            ->add('osteopath', EntityType::class, [
                'class' => Osteopath::class,
                'query_builder' => fn (EntityRepository $entityRepository) => $entityRepository->createQueryBuilder('o')
                    ->where('o.id = :osteopathId')
                    ->setParameter('osteopathId', $osteopathId),
                'label' => 'sylius.form.osteopath',
                'required' => false,
            ])
            ->add('friend', EntityType::class, [
                'class' => Friend::class,
                'query_builder' => fn (EntityRepository $entityRepository) => $entityRepository->createQueryBuilder('f')
                    ->where('f.id = :friendId')
                    ->setParameter('friendId', $friendId),
                'label' => 'sylius.form.friends',
                'required' => false,
            ])
            ->add('optin', CheckboxType::class, [
                'label' => 'sylius.form.customer.optin',
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $form = $event->getForm();

            $form->remove('osteopath');
            $form->remove('friend');
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [CustomerType::class];
    }
}
