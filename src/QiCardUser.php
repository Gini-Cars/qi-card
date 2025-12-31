<?php

namespace Ht3aa\QiCard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class QiCardUser extends Model
{
    use HasApiTokens, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qi_card_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_info',
        'card_list',
        'qi_card_access_token',
        'qi_card_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_info' => 'array',
        'card_list' => 'array',
    ];
}
