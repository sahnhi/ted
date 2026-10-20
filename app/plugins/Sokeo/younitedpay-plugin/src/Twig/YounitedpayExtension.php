<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\Twig;

use Sokeo\SyliusYounitedpayPlugin\Api\YounitedpayApi;
use Sokeo\SyliusYounitedpayPlugin\Api\YounitedpayBestPrice;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentMethodRepository;
use Sylius\Component\Core\Model\OrderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class YounitedpayExtension extends AbstractExtension
{
    /** @var PaymentMethodRepository */
    private $paymentMethodRepository;

    public function __construct(PaymentMethodRepository $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('configMinAmount', [$this, 'configMinAmount']),
            new TwigFunction('configMaxAmount', [$this, 'configMaxAmount']),
            new TwigFunction('getMaturities', [$this, 'getMaturities']),
        ];
    }

    public function configMinAmount()
    {
        // Get younited plugin credentials
        $younitedpayConfig = $this->paymentMethodRepository->findOneByCode('younitedpay')->getGatewayConfig()->getConfig();
        $younitedpayApi = $this->getYounitedpayApi($younitedpayConfig);
        $configMinAmount = $younitedpayApi->getMinAmount();

        return $configMinAmount;
    }

    public function configMaxAmount()
    {
        // Get younited plugin credentials
        $younitedpayConfig = $this->paymentMethodRepository->findOneByCode('younitedpay')->getGatewayConfig()->getConfig();
        $younitedpayApi = $this->getYounitedpayApi($younitedpayConfig);
        $configMaxAmount = $younitedpayApi->getMaxAmount();

        return $configMaxAmount;
    }

    public function getMaturities(OrderInterface $order)
    {
        $filteredOffers = [];
        // Get last payment from $order Object
        $payment = $order->getLastPayment();

        // Get younited plugin credentials
        $younitedpayConfig = $this->paymentMethodRepository->findOneByCode('younitedpay')->getGatewayConfig()->getConfig();

        $younitedpayApi = $this->getYounitedpayApi($younitedpayConfig);
        $configMaturities = $younitedpayApi->getMaturities();

        $younitedpayBestPrice = new YounitedpayBestPrice($younitedpayApi);
        $resMaturities = $younitedpayBestPrice->getBestPrice($payment->getAmount());

        if (!empty($resMaturities['offers'])) {
            // Filterig maturities accordingly to gateway config
            $filteredOffers = array_filter($resMaturities['offers'], function ($v) use ($configMaturities) {
                if (!empty($v['maturityInMonths']) && !empty($configMaturities)) {
                    return in_array($v['maturityInMonths'], $configMaturities);
                }
            });
        }

        return $filteredOffers;
    }

    private function getYounitedpayApi(array $config)
    {
        $config = new \ArrayObject($config);
        $younitedpayApi = new YounitedpayApi();
        $younitedpayApi->initialize($config);

        return $younitedpayApi;
    }
}
