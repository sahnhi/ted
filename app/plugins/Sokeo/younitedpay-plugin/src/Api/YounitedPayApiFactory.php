<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\Api;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentMethodRepository;

class YounitedPayApiFactory
{
    public const YOUNITED_PAY_CODE = 'younitedpay';

    private $paymentMethodRepository;

    public function __construct(PaymentMethodRepository $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function __invoke(): YounitedpayApiInterface
    {
        $config = $this->paymentMethodRepository->findOneByCode(self::YOUNITED_PAY_CODE)->getGatewayConfig()->getConfig();
        $config = new \ArrayObject($config);

        $younitedpayApi = new YounitedpayApi();
        $younitedpayApi->initialize($config);

        return $younitedpayApi;
    }
}
