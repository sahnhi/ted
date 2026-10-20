<?php

namespace App\Form\Type\Payment;

use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\PaymentMethodInterface;
use App\Exception\PaymentMethodNotFound;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Sylius\Component\Core\Model\Payment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class RefundType extends AbstractType
{
    private const REFUND_CODE_ECHANGE = 'echange00';
    private const REFUND_CODE_ECHANGE_WAREHOUSE = 'REX00';

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', MoneyType::class, [
                'label' => 'sylius.form.refund.amount',
                'data' => $options['refund_amount'],
                'required' => true,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
                $form = $event->getForm();

                $refundCodeField = [
                    'type' => TextType::class,
                    'params' => [
                        'label' => 'sylius.form.refund.code',
                        'empty_data' => '',
                        'mapped' => false,
                        'required' => true,
                    ],
                ];

                $submitFieldDisabled = false;

                /** @var Payment $payment */
                $payment = $options['payment'];

                if ($payment) {
                    $paymentMethod = $payment->getMethod();
                    if (!$paymentMethod instanceof PaymentMethodInterface) {
                        throw new PaymentMethodNotFound();
                    }

                    $paymentDetails = $payment->getDetails();
                    $isExchange = str_contains((string) $options['order']->getNumber(), ReturnTypeServiceInterface::RETURN_PREFIXES[Reason::RETURN_TYPE_EXCHANGE]);
                    $isWarehouseExchange = str_contains((string) $options['order']->getNumber(), ReturnTypeServiceInterface::RETURN_PREFIXES[Reason::RETURN_TYPE_WAREHOUSE]);

                    if ($isExchange) {
                        $refundCodeField['params']['data'] = self::REFUND_CODE_ECHANGE;
                    }

                    if ($isWarehouseExchange) {
                        $refundCodeField['params']['data'] = self::REFUND_CODE_ECHANGE_WAREHOUSE;
                    }

                    if (false === $isExchange && (PaymentMethodInterface::ADYEN_CB === $paymentMethod->getCode() || PaymentMethodInterface::ADYEN_PAYPAL === $paymentMethod->getCode())) {
                        if (array_key_exists('extraData', $paymentDetails)) {
                            $extraData = json_decode((string) $paymentDetails['extraData'], true);

                            if (!empty($extraData['refundToken'])) {
                                $refundCodeField['type'] = HiddenType::class;
                                $refundCodeField['params']['required'] = false;
                            }
                        }
                    } elseif (false === $isExchange && PaymentMethodInterface::BE2BILL_NTIMES === $paymentMethod->getCode()) {
                        if (array_key_exists('EXTRADATA', $paymentDetails)) {
                            $extraData = json_decode((string) $paymentDetails['EXTRADATA'], true);

                            if (!empty($extraData['refund_token'])) {
                                $refundCodeField['type'] = HiddenType::class;
                                $refundCodeField['params']['required'] = false;
                            }
                        }

                        if (!empty($paymentDetails['SCHEDULEDATA'])) {
                            $nbrSchedulePayments = 0;
                            foreach ($paymentDetails['SCHEDULEDATA'] as $scheduleData) {
                                if (!empty($scheduleData['TRANSACTIONID'])) {
                                    ++$nbrSchedulePayments;
                                }
                            }

                            if ($nbrSchedulePayments < 3) {
                                // TODO : on désactive temporairement le fait de désactiver le bouton "Rembourser" pour le formulaire de remboursement d'une commande
                                $submitFieldDisabled = false;
                            }
                        }
                    }
                }

                $form->add('refundCode', $refundCodeField['type'], $refundCodeField['params']);
                $form->add('submit', SubmitType::class, [
                    'label' => 'app.ui.save',
                    'attr' => [
                        'class' => 'ui primary button save_refund',
                        'onclick' => 'return confirm("' . $this->translator->trans('sylius.form.refund.confirm') . '")',
                    ],
                    'disabled' => $submitFieldDisabled,
                ]);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
            'refund_amount' => 0,
            'payment' => null,
            'order' => null,
        ]);
    }
}
