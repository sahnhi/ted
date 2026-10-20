<?php

/*
 * This file was created by developers working at Sokeo
 * Do you need more information about us and what we do? Visit our https://sokeo.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://sokeo.io/career
*/

declare(strict_types=1);

namespace spec\Sokeo\SyliusYounitedpayPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Convert;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sokeo\SyliusYounitedpayPlugin\Action\ConvertPaymentAction;
use Sokeo\SyliusYounitedpayPlugin\ApiClient\YounitedpayApiClientInterface;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Currency\Model\Currency;

final class ConvertPaymentActionSpec extends ObjectBehavior
{
    public function let(PaymentDescriptionProviderInterface $paymentDescriptionProvider, ChannelContextInterface $channelContext): void
    {
        $eur = new Currency();
        $eur->setCode('EUR');
        $channel = new Channel();
        $channel->setBaseCurrency($eur);
        $channelContext->getChannel(Argument::any())->willReturn($channel);
        $this->beConstructedWith($paymentDescriptionProvider, $channelContext);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ConvertPaymentAction::class);
    }

    public function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    public function it_executes(
        Convert $request,
        PaymentInterface $payment,
        GatewayInterface $gateway,
        OrderInterface $order,
        CustomerInterface $customer,
        AddressInterface $address,
        YounitedpayApiClientInterface $YounitedpayApiClient,
        PaymentDescriptionProviderInterface $paymentDescriptionProvider
    ): void {
        $this->setGateway($gateway);
        $this->setApi($YounitedpayApiClient);

        $YounitedpayApiClient->getType()->willReturn(YounitedpayApiClientInterface::REDIRECT_ORDER_TYPE);
        $YounitedpayApiClient->getAllowMultiCurrency()->willReturn(false);
        $order->getId()->willReturn(21);
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');
        $order->getCustomer()->willReturn($customer);
        $order->getCurrencyCode()->willReturn('EUR');
        $order->getLocaleCode()->willReturn('US');
        $order->getCustomerIp()->willReturn('127.11.22.22');
        $payment->getOrder()->willReturn($order);
        $payment->getDetails()->willReturn([]);
        $payment->getId()->willReturn(17);
        $payment->getAmount()->willReturn(170);
        $payment->getCurrencyCode()->willReturn('EUR');
        $order->getBillingAddress()->willReturn($address);
        $order->getShippingAddress()->willReturn($address);
        $paymentDescriptionProvider->getPaymentDescription($payment)->willReturn('Payment Description');

        $request->setResult([
            'paymentData' => [
                'type' => 'redirect',
                'order_id' => '21-17-',
                'currency' => 'EUR',
                'amount' => 170,
                'description' => 'Payment Description',
                'customer' => [
                    'locale' => 'US',
                    'ip_address' => '127.11.22.22',
                    'first_name' => null,
                    'last_name' => null,
                    'address1' => null,
                    'zip_code' => null,
                    'city' => null,
                    'country' => null,
                    'phone' => null,
                    'email' => null,
                ],
            ],
        ])->shouldBeCalled();

        $this->execute($request);
    }

    public function it_supports_only_convert_request_payment_source_and_array_to(
        Convert $request,
        PaymentInterface $payment
    ): void {
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');
        $this->supports($request)->shouldReturn(true);
    }
}
