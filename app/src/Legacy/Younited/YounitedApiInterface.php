<?php

namespace App\Legacy\Younited;

interface YounitedApiInterface
{
    public const DEVELOPMENT = 'https://tediberservicesapi.pp-services.younited-credit.com';
    public const PRODUCTION = 'https://tediberservicesapi.services.younited-credit.com';

    public const LOGIN_URL = 'https://login.microsoftonline.com/younited-credit.fr/oauth2/token';

    public const GRANT_TYPE = 'client_credentials';

    public const PAYMENT_METHOD_CODE = 'younited';

    public const DECISION_RESULT_ACCEPTED = 1;
    public const DECISION_RESULT_REFUSED = 0;

    public const REASON_CODE_SIGNED = '0000';
    public const REASON_CODE_NO_OFFER = '0001';
    public const REASON_CODE_NOT_SIGNED = '0002';
    public const REASON_CODE_REFUSAL = '0003';
    public const REASON_CODE_CANCEL = '0004';
    public const REASON_CODE_BOC_CANCEL = '0005';

    public function setApiUrl($apiUrl);

    public function executeRequest();
}
