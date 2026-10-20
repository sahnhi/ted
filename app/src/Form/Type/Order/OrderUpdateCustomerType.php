<?php

namespace App\Form\Type\Order;

use App\Doctrine\Entity\Sylius\CustomerInterface;
use App\Doctrine\Entity\Sylius\Order;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

final class OrderUpdateCustomerType extends AbstractResourceType
{
    public function __construct(
        private readonly RepositoryInterface $customerRepository,
        array $validationGroups = []
    ) {
        parent::__construct(Order::class, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('updatedEmail', EmailType::class, [
                'mapped' => false,
                'label' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'app.order_update_customer.updated_email.not_blank',
                        'groups' => ['app_order_update_customer'],
                    ]),
                    new Email([
                        'message' => 'app.order_update_customer.updated_email.email',
                        'groups' => ['app_order_update_customer'],
                    ]),
                ],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                $form = $event->getForm();

                /** @var Order $order */
                $order = $form->getData();

                $customerDataEmail = $form->get('updatedEmail')->getData();

                /** @var CustomerInterface $customer */
                if (!$customer = $this->customerRepository->findOneByEmail($customerDataEmail)) {
                    $customer = $order->getCustomer();
                    $customer->setEmail($customerDataEmail);
                    $customer->setEmailCanonical($customerDataEmail);
                }

                $order->setCustomer($customer);
            })
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'app_order_update_customer';
    }
}
