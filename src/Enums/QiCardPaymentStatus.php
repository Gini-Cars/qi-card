<?php

namespace Ht3aa\QiCard\Enums;

enum QiCardPaymentStatus: string
{
    /**
     * SUCCESS => نجح
     */
    case SUCCESS = 'SUCCESS';

    /**
     * FAIL => فشل
     */
    case FAIL = 'FAIL';

    /**
     * PROCESSING => قيد المعالجة
     */
    case PROCESSING = 'PROCESSING';

    /**
     * CANCELLED => ملغى
     */
    case CANCELLED = 'CANCELLED';
}
