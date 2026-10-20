<?php

namespace App\Form\Type\OrderStore;

use App\Doctrine\Entity\OrderStore;
use App\Doctrine\Entity\Sylius\Order;
use Doctrine\ORM\EntityRepository;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class LinkType extends AbstractResourceType
{
    public function __construct()
    {
        parent::__construct(OrderStore::class, ['sylius']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $form = $event->getForm();
            $data = $event->getData();

            if (null !== $data && null !== $data->getOrder()) {
                $customer = $data->getOrder()->getCustomer();
                $date = $data->getOrder()->getCreatedAt();

                $form->add('order', EntityType::class, [
                    'class' => Order::class,
                    'required' => false,
                    'placeholder' => 'sylius.ui.choose_order',
                    'empty_data' => null,
                    'label' => 'sylius.ui.order',
                    'choice_label' => fn (Order $order): string => '#' . $order->getNumber() . ' - ' . ($order->getTotal() / 100) . ' €',
                    'query_builder' => fn (EntityRepository $entityRepository) => $entityRepository->createQueryBuilder('o')
                        ->join('o.channel', 'c')
                        ->where('o.customer = :customer')
                        ->andWhere('o.paymentState = :paid')
                        ->andWhere('c.code not IN (:channels)')
                        ->andWhere('(DATE_DIFF(o.checkoutCompletedAt, :orderStoreDate)) < 9')
                        ->andWhere('o.checkoutCompletedAt > :orderStoreDate')
                        ->setParameter('customer', $customer)
                        ->setParameter('paid', 'paid')
                        ->setParameter('channels', ['return', 'bis'])
                        ->setParameter('orderStoreDate', $date),
                ]);
            }
        });
    }

    public function getBlockPrefix(): string
    {
        return 'app_admin_order_store_link';
    }
}
