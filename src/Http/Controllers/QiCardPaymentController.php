<?php

namespace Ht3aa\QiCard\Http\Controllers;

use Ht3aa\QiCard\Models\QiCardPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class QiCardPaymentController
{
    // {
    //     "paymentResult": {
    //         "resultCode": "SUCCESS",
    //         "resultMessage": "Success.",
    //         "resultStatus": "S"
    //     },
    //     "paymentId": "20260101111212800100166820603167424",
    //     "paymentRequestId": "23dcbc4f-d71c-4f70-b211-f089c00e8f70",
    //     "extendInfo": "{\"sourcePlatform\":\"MINI_APP\"}",
    //     "paymentTime": "2026-01-01T15:29:44+03:00",
    //     "paymentAmount": {
    //         "currency": "IQD",
    //         "value": "1000"
    //     },
    //     "paymentCreateTime": "2026-01-01T15:27:53+03:00"
    // }
    public function webhook(Request $request)
    {
        $payment = QiCardPayment::where('payment_request_id', $request->get('paymentRequestId'))->first();
        if (! $payment) {
            Log::error('qi card payment not found: '.$request->paymentRequestId);
            throw new UnprocessableEntityHttpException('Payment not found');
        }

        // send request to the custom webhook url
        if (config('qi-card.api.payment_custom_webhook_url')) {
            Http::post(config('qi-card.api.payment_custom_webhook_url'), $request->all());
        }

        $payment->update([
            'status' => $request->get('paymentResult')['resultCode'],
        ]);
    }
}
