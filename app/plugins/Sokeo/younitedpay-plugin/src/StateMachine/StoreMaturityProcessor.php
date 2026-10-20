<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\StateMachine;

use Payum\Core\Reply\HttpRedirect;
use Sokeo\SyliusYounitedpayPlugin\Api\YounitedpayApi;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final class StoreMaturityProcessor
{
    public const YOUNITEDPAY_PAYMENT_METHOD_NAME = 'younitedpay';
    public const YOUNITEDPAY_DEFAULT_MATURITY = 12;

    private $requestStack;
    private $flashBag;

    public function __construct(RequestStack $requestStack, FlashBagInterface $flashBag)
    {
        $this->requestStack = $requestStack;
        $this->flashBag = $flashBag;
    }

    public function setDetails(OrderInterface $order): void
    {
        // Get last payment from $order Object
        $payment = $order->getLastPayment();
        /** @var PaymentInterface $payment */
        if (null === $payment) {
            return;
        }

        $details = $payment->getDetails();
        $gatewayConfig = $payment->getMethod()->getGatewayConfig();

        if (self::YOUNITEDPAY_PAYMENT_METHOD_NAME === $gatewayConfig->getGatewayName()) {
            $younitedpayApi = $this->getYounitedpayApi($gatewayConfig->getConfig());
            $chosenMaturity = null !== $this->requestStack->getCurrentRequest() ?
                ($this->requestStack->getCurrentRequest()->request->get('maturity') ?? self::YOUNITEDPAY_DEFAULT_MATURITY) :
                self::YOUNITEDPAY_DEFAULT_MATURITY;

            if (!$chosenMaturity or !is_numeric($chosenMaturity) or !in_array($chosenMaturity, $younitedpayApi->getMaturities())) {
                $this->flashBag->add('error', 'Maturité incorrecte');
                throw new HttpRedirect($this->requestStack->getCurrentRequest()->server->get('HTTP_REFERER'));
            }

            // Add/Update maturity into lastPayment details field
            $details = array_merge(
                $details,
                ['younitedpay' => ['maturity' => (int) $chosenMaturity]]
            );
        } elseif (isset($details['younitedpay'])) {
            unset($details['younitedpay']);
        }

        $payment->setDetails($details);
    }

    private function getYounitedpayApi(array $config)
    {
        $config = new \ArrayObject($config);
        $younitedpayApi = new YounitedpayApi();
        $younitedpayApi->initialize($config);

        return $younitedpayApi;
    }
}
