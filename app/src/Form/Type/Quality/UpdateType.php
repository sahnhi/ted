<?php

namespace App\Form\Type\Quality;

use App\Doctrine\Entity\Quality;
use App\Doctrine\Entity\Sylius\Order;
use App\Form\Type\QualityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends QualityType
{
    protected bool $new = false;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['order' => null])
            ->setAllowedTypes('order', Order::class);
    }

    protected function addOrderItemLogisticField(FormBuilderInterface $builder, array $options)
    {
        $builder->add('orderItemLogistic', TextType::class, [
            'label' => 'sylius.ui.item_logistic',
            'disabled' => true,
        ]);
    }

    protected function addQualityCommentField(FormBuilderInterface $builder, array $options)
    {
        $builder->add('qualityComment', TextareaType::class, [
            'label' => 'app.ui.quality.quality_comment',
        ]);
    }

    protected function addStatusField(FormBuilderInterface $builder, array $options)
    {
        $builder->add('status', ChoiceType::class, [
            'label' => 'app.ui.quality.status',
            'choices' => [
                'app.ui.quality.statuses.new' => Quality::STATUS_NEW,
                'app.ui.quality.statuses.in_progress' => Quality::STATUS_IN_PROGRESS,
                'app.ui.quality.statuses.accepted' => Quality::STATUS_ACCEPTED,
                'app.ui.quality.statuses.refused' => Quality::STATUS_REFUSED,
            ],
        ]);
    }
}
