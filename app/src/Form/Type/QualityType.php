<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Doctrine\Entity\OrderItemLogistic;
use App\Doctrine\Entity\Quality;
use App\Doctrine\Entity\QualityImage;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\OrderItemLogisticRepository;
use App\Doctrine\Repository\ReasonRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class QualityType extends AbstractResourceType
{
    protected bool $new = true;

    public function __construct(
        private readonly OrderItemLogisticRepository $orderItemLogisticRepository,
        private readonly ReasonRepositoryInterface $reasonRepository,
        private readonly Security $security,
        array $validationGroups = []
    ) {
        parent::__construct(Quality::class, $validationGroups);
    }

    public function isNew(): bool
    {
        return $this->new;
    }

    public function getBlockPrefix(): string
    {
        return 'app_quality';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['order' => null])
            ->setAllowedTypes('order', Order::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Order $order */
        $order = $options['order'];

        $this->addCreatedBy($builder);

        $builder
            ->add('title', TextType::class, [
                'label' => 'app.ui.quality.title',
            ]);

        $this->addStatusField($builder, $options);
        $this->addOrderItemLogisticField($builder, $options);

        $builder
            ->add('reason', EntityType::class, [
                'placeholder' => 'sylius.form.attribute_type.select',
                'label' => 'sylius.ui.reasons',
                'class' => Reason::class,
                'choices' => $this->reasonRepository->findForLogisticProductsInOrder($order),
                'group_by' => function (Reason $reason) use ($order): array {
                    $labels = [];

                    /** @var OrderItemLogistic $orderItemLogistic */
                    foreach ($order->getOrderItemLogistics() as $orderItemLogistic) {
                        if (null !== $orderItemLogistic->getProductVariantLogistic()) {
                            if ($reason->getProducts()->contains($orderItemLogistic->getProductVariantLogistic()->getProduct())) {
                                $labels[] = sprintf('%s (%s)', $orderItemLogistic->getProductVariantLogistic()->getName(), $orderItemLogistic->getId());
                            }
                        }
                    }

                    return $labels;
                },
            ]);

        $builder
            ->add('validationRequired', CheckboxType::class, [
                'label' => 'app.ui.quality.validation_required',
                'disabled' => !$this->isNew(),
            ])
            ->add('savComment', TextareaType::class, [
                'label' => 'app.ui.quality.sav_comment',
            ]);

        $this->addQualityCommentField($builder, $options);

        $this->addImageField($builder, $options);
    }

    protected function addOrderItemLogisticField(FormBuilderInterface $builder, array $options)
    {
        $builder->add('orderItemLogistic', EntityType::class, [
            'label' => 'sylius.ui.item_logistic',
            'class' => OrderItemLogistic::class,
            'choices' => $this->orderItemLogisticRepository->findForOrderAndNotLinked($options['order']),
            'required' => true,
            'choice_label' => fn (OrderItemLogistic $orderItemLogistic): string => sprintf('%s - %s', $orderItemLogistic->getId(), $orderItemLogistic->getProductVariantLogistic()->getName()),
        ]);
    }

    protected function addQualityCommentField(FormBuilderInterface $builder, array $options)
    {
    }

    protected function addStatusField(FormBuilderInterface $builder, array $options)
    {
    }

    protected function addImageField(FormBuilderInterface $builder, array $options)
    {
        if (count($options['data']->getImages()) > 0) {
            $builder
                ->add('images', CollectionType::class, [
                    'entry_type' => QualityImageType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'entry_options' => ['quality' => $options['data']],
                    'label' => 'app.ui.quality.images',
                    'button_add_label' => 'Ajouter une image',
                ]);
        } else {
            $builder
                ->add('images', CollectionType::class, [
                    'entry_type' => QualityImageType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'entry_options' => ['quality' => $options['data']],
                    'label' => 'app.ui.quality.images',
                    'button_add_label' => 'Ajouter une image',
                    'data' => [
                        new QualityImage(),
                    ],
                ]);
        }
    }

    private function addCreatedBy(FormBuilderInterface $builder): void
    {
        if ($this->isNew()) {
            $user = $this->security->getUser();

            $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($user): void {
                $form = $event->getData();
                $form->setCreatedBy($user);
            });

            return;
        }

        $builder
            ->add('created_by', TextType::class, [
                'label' => 'app.ui.quality.created_by',
                'disabled' => true,
            ]);
    }
}
