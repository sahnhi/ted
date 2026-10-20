<?php

namespace App\Form\Type\Reason;

use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\Reason;
use App\Form\Type\ProductVariantAutocompleteChoiceType;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use Doctrine\ORM\EntityRepository;
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

class BisSupportType extends AbstractType
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $builder
            ->add('reason', EntityType::class, [
                'class' => Reason::class,
                'choice_label' => 'name',
                'placeholder' => 'sylius.form.order_reason.motif.placeholder',
                'label' => 'sylius.form.order_reason.motif.label',
                'constraints' => [new NotBlank(['groups' => ['sylius']])],
                'query_builder' => static function (EntityRepository $entityRepository) use ($options) {
                    $query = $entityRepository->createQueryBuilder('r')
                        ->leftJoin('r.channel', 'c')
                        ->where('r.type = :type')
                        ->andWhere('r.enable = true');

                    if (!empty($options['constraints']['order_type'])) {
                        if (!empty($options['attr']['returnType'])) {
                            switch ($options['attr']['returnType']) {
                                case 'exchange':
                                    $query->andWhere('r.returnType = :returnType OR r.returnType = :returnTypeQuality')
                                        ->setParameter('returnType', Reason::RETURN_TYPE_EXCHANGE)
                                        ->setParameter('returnTypeQuality', Reason::RETURN_TYPE_QUALITY_EXCHANGE);
                                    break;
                                case 'simple':
                                    $query->andWhere('r.returnType = :returnType OR r.returnType = :returnTypeQuality')
                                        ->setParameter('returnType', Reason::RETURN_TYPE_NORMAL)
                                        ->setParameter('returnTypeQuality', Reason::RETURN_TYPE_QUALITY);
                                    break;
                                case 'refund':
                                    $query->andWhere('r.returnType = :returnType')
                                        ->setParameter('returnType', Reason::RETURN_TYPE_REFUND);
                                    break;
                                case ReturnTypeServiceInterface::RETURN_TYPE_SENDER:
                                    $query->andWhere('r.returnType = :returnType')
                                        ->setParameter('returnType', Reason::RETURN_TYPE_SENDER);
                                    break;
                                case ReturnTypeServiceInterface::RETURN_TYPE_WAREHOUSE:
                                    $query->andWhere('r.returnType = :returnType')
                                        ->setParameter('returnType', Reason::RETURN_TYPE_WAREHOUSE);
                                    break;
                                case ReturnTypeServiceInterface::RETURN_TYPE_AUTONOMOUS:
                                    $query->andWhere('r.returnType = :returnType')
                                        ->setParameter('returnType', Reason::RETURN_TYPE_AUTONOMOUS);
                                    break;
                            }
                        } elseif ('bis' === $options['constraints']['order_type']) {
                            $query->andWhere('c.code = :code')
                                ->setParameter('code', 'bis');
                        }
                    }

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
            ->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event) use ($user): void {
                $orderReason = $event->getData();
                if (null === $orderReason->getCreatedAt()) {
                    $orderReason->setCreatedBy($user);
                }
                $orderReason->setIsProblem(false);
            })
            ->add('variant', (!empty($options['attr']['returnType'])) ? HiddenType::class : ProductVariantAutocompleteChoiceType::class, [
                'label' => 'sylius.ui.variant',
                'required' => false,
            ])
            ->add('quantity', (!empty($options['attr']['returnType'])) ? HiddenType::class : IntegerType::class, [
                'label' => 'sylius.ui.quantity',
                'required' => false,
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
            ]);
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
