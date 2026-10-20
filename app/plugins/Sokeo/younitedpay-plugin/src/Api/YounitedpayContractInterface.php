<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\Api;

use Payum\Core\Request\GetHttpRequest;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface YounitedpayContractInterface
{
    public const CONTRACT_REFERENCE_STRING_TO_REPLACE = '{contractReference}';
    public const LOAD_CONTRACT_URI = '/api/1.0/Contract/{contractReference}';
    public const CANCEL_CONTRACT_URI = '/api/1.0/Contract/{contractReference}';
    public const CONFIRM_CONTRACT_URI = '/api/1.0/Contract/{contractReference}/confirm';
    public const ACTIVATE_CONTRACT_URI = '/api/1.0/Contract/{contractReference}/activate';
    public const WITHDRAW_CONTRACT_URI = '/api/1.0/Contract/{contractReference}/withdraw';
    public const INITIALIZE_CONTRACT_URI = '/api/1.0/Contract';
    public const LOAD_CONTRACT_METHOD = 'GET';
    public const CANCEL_CONTRACT_METHOD = 'DELETE';
    public const CONFIRM_CONTRACT_METHOD = 'PATCH';
    public const ACTIVATE_CONTRACT_METHOD = 'PATCH';
    public const WITHDRAW_CONTRACT_METHOD = 'PATCH';
    public const INITIALIZE_CONTRACT_METHOD = 'POST';
    public const GRANTED_WEBHOOK_URL = 'younitedpay-webhook/granted';
    public const CANCELLED_WEBHOOK_URL = 'younitedpay-webhook/cancelled';
    public const WITHDRAWN_WEBHOOK_URL = 'younitedpay-webhook/withdrawn';
    public const LOAD_LABEL = 'load';
    public const CANCEL_LABEL = 'cancel';
    public const CONFIRM_LABEL = 'confirm';
    public const ACTIVATE_LABEL = 'activate';
    public const WITHDRAW_LABEL = 'withdraw';
    public const INITIALIZE_LABEL = 'initialize';

    public function initializeContract(
        OrderInterface $order,
        PaymentInterface $payment,
        int $maturity,
        UrlGeneratorInterface $router,
        GetHttpRequest $request
    );

    public function confirmContract(string $contractReference);

    public function activateContract(string $contractReference);

    public function withdrawContract(string $contractReference, $amount);

    public function cancelContract(string $contractReference);

    public function loadContract(string $contractReference);
}
