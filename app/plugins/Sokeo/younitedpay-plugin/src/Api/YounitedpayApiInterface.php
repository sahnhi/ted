<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\Api;

interface YounitedpayApiInterface
{
    public const BEARER_API_BASE_URL = 'https://login.microsoftonline.com';
    public const BEARER_API_URI_TEST = '/c9536195-ef3b-4703-9c13-924db8e24486/oauth2/v2.0/token';
    public const BEARER_API_URI_PROD = '/5fe44fa6-b50a-42d9-a006-199bedeb5bb9/oauth2/v2.0/token';
    public const BEARER_API_METHOD = 'POST';
    public const YOUNITED_PAY_API_URL_TEST = 'https://api.sandbox-younited-pay.com';
    public const YOUNITED_PAY_API_URL_PROD = 'https://api.younited-pay.com';
    public const GRANT_TYPE = 'client_credentials';
    public const SCOPE = 'api://younited-pay/.default';

    public const REASON_CODE_SIGNED = '0000';
    public const REASON_CODE_NO_OFFER = '0001';
    public const REASON_CODE_NOT_SIGNED = '0002';
    public const REASON_CODE_REFUSAL = '0003';
    public const REASON_CODE_CANCEL = '0004';
    public const REASON_CODE_BOC_CANCEL = '0005';

    public function initialize(\ArrayObject $config): void;

    public function executeRequest($baseUri, $method, $routeUrl, $options);
}
