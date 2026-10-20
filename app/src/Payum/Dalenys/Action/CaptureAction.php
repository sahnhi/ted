<?php

namespace App\Payum\Dalenys\Action;

use Payum\Be2Bill\Action\CaptureAction as BaseCaptureAction;
use Payum\Be2Bill\Api;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;

class CaptureAction extends BaseCaptureAction
{
    public function __construct()
    {
        parent::__construct();
    }

    public function execute($request)
    {
        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (isset($httpRequest->request['token'])) {
            $model = ArrayObject::ensureArrayObject($request->getModel());
            $model['CARDFULLNAME'] = $httpRequest->request['credit_card_holder'];
            $model['HFTOKEN'] = $httpRequest->request['token'];

            $params = $model->toUnsafeArray();
            unset($params['SCHEDULEDATA']);

            $result = (array) $this->api->payment($params);

            if (!empty($result['TRANSACTIONID'])) {
                $scheduleData = $model->getArrayCopy()['SCHEDULEDATA'];
                $scheduleData[0]['TRANSACTIONID'] = $result['TRANSACTIONID'];
                $scheduleData[0]['MESSAGE'] = $result['MESSAGE'];
                $scheduleData[0]['EXECCODE'] = $result['EXECCODE'];

                $result['SCHEDULEDATA'] = $scheduleData;
            }

            $model->replace($result);
        }

        if (!isset($httpRequest->request['EXECCODE'])) {
            $this->handlePayment($request);
        }
    }

    private function handlePayment(Capture $request)
    {
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (Api::EXECCODE_3DSECURE_IDENTIFICATION_REQUIRED === $model['EXECCODE']) {
            if (isset($model['3DSECUREHTML'])) {
                throw new HttpResponse(base64_decode((string) $model['3DSECUREHTML']));
            }

            throw new HttpResponse(base64_decode((string) $model['REDIRECTHTML']));
        }

        parent::execute($request);
    }
}
