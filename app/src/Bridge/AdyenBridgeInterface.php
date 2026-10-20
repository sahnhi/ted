<?php

namespace App\Bridge;

interface AdyenBridgeInterface
{
    public const ENVIRONMENT_DEVELOPMENT = \Adyen\Environment::TEST;
    public const ENVIRONMENT_PRODUCTION = \Adyen\Environment::LIVE;

    public const PAYMENT_METHOD_TYPE_CB = 'cb';
    public const PAYMENT_METHOD_TYPE_PAYPAL = 'paypal';
    public const PAYMENT_METHOD_TYPE_PAYBYLINK = 'paybylink';

    public const EVENT_CODE_AUTHORISATION = 'AUTHORISATION';
    public const EVENT_CODE_AUTHORISED = 'AUTHORISED';
    public const EVENT_CODE_REFUSED = 'REFUSED';
    public const EVENT_CODE_PENDING = 'PENDING';
    public const EVENT_CODE_CAPTURE = 'CAPTURE';
    public const EVENT_CODE_CANCELLED = 'CANCELLED';
    public const EVENT_CODE_CANCELLATION = 'CANCELLATION';
    public const EVENT_CODE_CANCEL_OR_REFUND = 'CANCEL_OR_REFUND';
    public const EVENT_CODE_ERROR = 'ERROR';
    public const EVENT_CODE_NOTIFICATION_OF_CHARGEBACK = 'NOTIFICATION_OR_CHARGEBACK';
    public const EVENT_CODE_CHARGEBACK = 'CHARGEBACK';
    public const EVENT_CODE_CHARGEBACK_REVERSED = 'CHARGEBACK_REVERSED';
    public const EVENT_CODE_REFUND_FAILED = 'REFUND_FAILED';
    public const EVENT_CODE_CAPTURE_FAILED = 'CAPTURE_FAILED';
    public const EVENT_CODE_EXPIRE = 'EXPIRE';
    public const EVENT_CODE_REFUND = 'REFUND';
    public const EVENT_CODE_REFUNDED_REVERSED = 'REFUNDED_REVERSED';

    public function getMerchantAccount(): string;

    public function getClientKey(): string;

    public function getEnvironment(): string;

    public function getMethodPayment(): string;

    public function getPaypalMerchantId(): string;

    public function verifySign(array $params): bool;

    public function merchantSig(array $params, bool $isNotify = false, string $hmacKey = 'hmacKey'): string;

    public function verifyNotification(array $params): bool;

    public function getApiUrl(): string;

    public function getApiKey(): string;
}
