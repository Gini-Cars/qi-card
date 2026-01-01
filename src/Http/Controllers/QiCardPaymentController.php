<?php

namespace Ht3aa\QiCard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QiCardPaymentController
{
    public function webhook(Request $request)
    {
        Log::info('qi card payment webhook received: '.json_encode($request->all()));
    }
}
