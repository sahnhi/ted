<?php

namespace App\Form\Type;

use App\Doctrine\Entity\Pack;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Services\Channel\ChannelCode;
use Doctrine\ORM\EntityRepository;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class PackType extends AbstractResourceType
{
    public function __construct(
    ) {
        parent::__construct(Pack::class, ['sylius']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choiceLabelCallback = static fn (ProductVariant $variant): string => sprintf('%s (%s)', $variant->getName(), $variant->getCode());

        $builder
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => PackTranslationType::class,
                'label' => 'sylius.form.pack.translations',
            ])
            ->add('code', TextType::class, [
                'label' => 'sylius.form.pack.code',
            ])
            ->add('variants', EntityType::class, [
                'class' => ProductVariant::class,
                'attr' => [
                    'class' => 'select2',
                ],
                'multiple' => true,
                'required' => false,
                'choice_label' => $choiceLabelCallback,
                'by_reference' => false,
            ])
            ->add('addons', EntityType::class, [
                'class' => ProductVariant::class,
                'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('p')
                    ->leftJoin('p.product', 'product')
                    ->innerJoin('product.channels', 'channel')
                    ->andWhere('channel.code = :channel')
                    ->setParameter('channel', ChannelCode::CHANNEL_WEB),
                'attr' => [
                    'class' => 'select2',
                ],
                'multiple' => true,
                'required' => false,
                'choice_label' => $choiceLabelCallback,
                'by_reference' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_pack';
    }
}
