<?php

namespace App\Form\Type\Reason;

use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariant;
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

class SupportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isProblem', HiddenType::class, [
                'required' => false,
                'attr' => ['isProblem' => !empty($options['attr']) && 1 === $options['attr']['isProblem'] ? 1 : 0],
            ])
            ->add('reason', EntityType::class, [
                'class' => Reason::class,
                'choice_label' => 'name',
                'placeholder' => 'sylius.form.order_reason.motif.placeholder',
                'label' => 'sylius.form.order_reason.motif.label',
                'query_builder' => static function (EntityRepository $entityRepository) use ($options) {
                    $qb = $entityRepository->createQueryBuilder('r')
                        ->where('r.type = :type');

                    if (!empty($options['attr']['isProblem']) && 1 === $options['attr']['isProblem']) {
                        $qb->andWhere('r.channel IS NULL');
                    } else {
                        $qb->andWhere('r.channel = :channel ')
                            ->setParameter('channel', $options['order']->getChannel());
                    }

                    return $qb->setParameter('type', Reason::TYPE_PRODUCT_SUPPORT);
                },
                'group_by' => static function (Reason $reason) {
                    if (!empty($reason->getGroupName())) {
                        return $reason->getGroupName()->getName();
                    }
                },
                'attr' => ['class' => 'reason_motif'],
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
            ->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($options): void {
                if (!$options['channel'] && $order = $options['order']) {
                    $form = $event->getForm();
                    $variants = [];
                    /** @var OrderItem $item */
                    foreach ($order->getItems() as $item) {
                        $variants['variants'][$item->getId()] = $item->getVariant();
                        $variants['quantity'][$item->getId()] = $item->getQuantity();
                    }
                    $form->add('variant', EntityType::class, [
                        'class' => ProductVariant::class,
                        'placeholder' => 'sylius.form.order_reason.variant_placeholder',
                        'choices' => !empty($variants) ? $variants['variants'] : [],
                        'choice_attr' => fn ($choice, $key, $val): array => ['data-variant-quantity' => $variants['quantity'][$key]],
                        'choice_label' => fn (ProductVariant $variant): string => $variant->getCode() . ' - ' . $variant->getName(),
                        'required' => false,
                    ]);
                    $form->add('comment', TextareaType::class, [
                        'label' => 'sylius.form.order_reason.comment',
                    ]);
                    $form->add('quantity', IntegerType::class, [
                        'label' => 'sylius.form.order_reason.quantity',
                        'required' => false,
                    ]);
                }
            })
            ->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event) use ($options): void {
                $orderReason = $event->getData();
                if (null === $orderReason->getCreatedAt()) {
                    $orderReason->setCreatedBy($options['user']);
                }
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
