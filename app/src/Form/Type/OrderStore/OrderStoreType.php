<?php

namespace App\Form\Type\OrderStore;

use App\Doctrine\Entity\OrderStore;
use App\Doctrine\Entity\Seller;
use App\Doctrine\Entity\Store;
use Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderStoreType extends AbstractType
{
    protected ChannelInterface $channel;

    public function __construct(ChannelContextInterface $context)
    {
        $this->channel = $context->getChannel();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('store', EntityType::class, [
                'choice_label' => 'name',
                'class' => Store::class,
                'placeholder' => 'app.form.order.store.placeholder',
                'label' => 'app.form.order.store.label',
                'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('s')
                    ->where('s.channel = :channel')
                    ->setParameter('channel', $this->channel),
            ])
            ->add('seller', EntityType::class, [
                'choice_label' => 'name',
                'class' => Seller::class,
                'placeholder' => 'app.form.order.seller.placeholder',
                'label' => 'app.form.order.seller.label',
                'group_by' => function (Seller $seller) {
                    if (null === $seller->getStore()) {
                        return 'Autres';
                    }

                    return $seller->getStore()->getName();
                },
                'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('se')
                    ->leftJoin('se.store', 's')
                    ->where('s.channel = :channel')
                    ->orWhere('se.store IS NULL')
                    ->setParameter('channel', $this->channel),
            ])
            ->add('order', HiddenType::class)
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                $order = $event->getForm()->getParent()->getData();
                $data = $event->getData();
                $data['order'] = $order;
                $event->setData($data);
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => OrderStore::class]);
    }
}
