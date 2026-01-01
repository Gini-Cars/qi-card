<?php

namespace Ht3aa\QiCard\Http\Controllers;

use Ht3aa\QiCard\Facades\QiCard;
use Ht3aa\QiCard\QiCardUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Illuminate\Support\Facades\Log;

class QiCardPaymentController
{
    public function webhook(Request $request)
    {
        Log::info('qi card payment webhook received: ' . json_encode($request->all()));
    }
}
