<?php

namespace App\Form\Type;

use App\Business\Command\Quotation;
use App\Doctrine\Entity\Seller;
use App\Doctrine\Entity\Store;
use Doctrine\ORM\EntityRepository;
use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Sylius\Bundle\AddressingBundle\Form\Type\CountryCodeChoiceType;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class QuotationType extends AbstractType
{
    protected ChannelInterface $channel;

    public function __construct(ChannelContextInterface $context)
    {
        $this->channel = $context->getChannel();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
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
            ->add('email', EmailType::class, [
                'label' => 'sylius.ui.email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sylius.contact.email.not_blank',
                    ]),
                    new Email([
                        'message' => 'sylius.contact.email.invalid',
                    ]),
                ],
            ])
            ->add('address', AddressType::class, [
                'label' => 'sylius.ui.address',
                'required' => false,
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'sylius.ui.phone_number',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('firstName', TextType::class, [
                'label' => 'sylius.ui.first_name',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'sylius.ui.last_name',
                'required' => false,
                'empty_data' => '',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $form = $event->getForm();
            $form->get('address')
                ->remove('phoneNumber')
                ->remove('firstName')
                ->remove('lastName')
                ->remove('company')
                ->remove('details')
                ->add('street', TextType::class, [
                    'label' => 'sylius.ui.street',
                    'required' => false,
                    'empty_data' => '',
                ])
                ->add('countryCode', CountryCodeChoiceType::class, [
                    'label' => 'sylius.form.address.country',
                    'enabled' => true,
                    'required' => false,
                ])
                ->add('city', TextType::class, [
                    'label' => 'sylius.ui.city',
                    'required' => false,
                    'empty_data' => '',
                ])
                ->add('postcode', TextType::class, [
                    'label' => 'sylius.ui.postcode',
                    'required' => false,
                    'empty_data' => '',
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Quotation::class]);
    }
}
