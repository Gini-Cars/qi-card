<?php

namespace Ht3aa\QiCard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class QiCardPayment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qi_card_payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_request_id',
        'payment_id',
        'amount',
        'order_description',
        'qi_card_user_id',
        'status',
        'product_type',
        'product_id',
        'redirect_url',
    ];

    public function product(): MorphTo
    {
        return $this->morphTo();
    }
}
