<?php

namespace App\Form\Type\Order;

use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\Reason;
use App\Form\Type\ProductVariantAutocompleteChoiceType;
use Doctrine\ORM\EntityRepository;
use Sylius\Component\User\Model\UserInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class BisExchangeType extends AbstractType
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var UserInterface $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $builder
            ->add('reason', EntityType::class, [
                'class' => Reason::class,
                'choice_label' => 'name',
                'placeholder' => 'sylius.form.order_reason.motif.placeholder',
                'label' => 'sylius.form.order_reason.motif.label',
                'constraints' => [new NotBlank(['groups' => ['sylius']])],
                'query_builder' => static function (EntityRepository $entityRepository) {
                    $query = $entityRepository->createQueryBuilder('r')
                        ->leftJoin('r.channel', 'c')
                        ->where('r.type = :type')
                        ->andWhere('c.code = :code')
                        ->setParameter('code', 'bis');

                    return $query->setParameter('type', Reason::TYPE_PRODUCT_SUPPORT);
                },
                'group_by' => static function (Reason $reason) {
                    if (!empty($reason->getGroupName())) {
                        return $reason->getGroupName()->getName();
                    }
                },
                'attr' => ['class' => 'reason_motif'],
                'required' => true,
            ])
            ->add('commercialOffer', EntityType::class, [
                'class' => Reason::class,
                'choice_label' => 'name',
                'placeholder' => 'sylius.form.order_reason.commercial_offer.placeholder',
                'label' => 'sylius.form.order_reason.commercial_offer.label',
                'query_builder' => static fn (EntityRepository $entityRepository) => $entityRepository->createQueryBuilder('r')
                    ->andWhere('r.type = :type')
                    ->setParameter('type', Reason::TYPE_COMMERCIAL_OFFER),
                'required' => false,
            ])
            ->add('variant', ProductVariantAutocompleteChoiceType::class, [
                'label' => 'sylius.ui.variant',
                'required' => false,
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'sylius.ui.quantity',
                'required' => false,
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
            ])
            ->add('createdById', HiddenType::class, [
                'mapped' => false,
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($user): void {
                $orderData = $event->getData();
                if (isset($orderData['createdById'])) {
                    $orderData['createdById'] = $user->getId();

                    $event->setData($orderData);
                }
            })
            ->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event) use ($user): void {
                $orderReason = $event->getData();
                if (null === $orderReason->getCreatedAt()) {
                    $orderReason->setCreatedBy($user);
                }
                $orderReason->setIsProblem(false);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderReason::class,
            'channel' => null,
            'order' => null,
            'user' => null,
        ]);
    }
}
