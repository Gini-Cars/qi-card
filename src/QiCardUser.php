<?php

namespace Ht3aa\QiCard;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;

class QiCardUser extends Authenticatable
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

    // User Info Accessors
    public function gender(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->user_info['gender'] ?? null,
        );
    }

    public function avatar(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->user_info['avatar'] ?? null,
        );
    }

    public function avatarTemporaryS3Url(): Attribute
    {
        return Attribute::make(
            get: fn() => Storage::disk('s3')->temporaryUrl($this->user_info['avatar'], now()->addMinutes(5)) ?? null,
        );
    }

    public function nationality(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->user_info['nationality'] ?? null,
        );
    }

    public function userName(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!isset($this->user_info['userName'])) {
                    return null;
                }

                return $this->user_info['userName']['fullName'];
            },
        );
    }

    public function userInfoUserNameInArabic(): Attribute
    {
        return Attribute::make(

            get: function () {
                if (!isset($this->user_info['userNameInArabic'])) {
                    return null;
                }

                return $this->user_info['userNameInArabic']['fullName'];
            },


        );
    }

    public function contactInfos(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->user_info['contactInfos'] ?? null,
        );
    }

    public function mobilePhoneNumber(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!isset($this->user_info['contactInfos'])) {
                    return null;
                }

                foreach ($this->user_info['contactInfos'] as $contactInfo) {
                    if ($contactInfo['contactType'] === 'MOBILE_PHONE') {
                        $phone = str_replace('-', '', $contactInfo['contactNo']);

                        if (! str_starts_with($phone, '+')) {
                            $phone = '+' . $phone;
                        }

                        return $phone;
                    }
                }

                return null;
            },
        );
    }

    public function email(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!isset($this->user_info['contactInfos'])) {
                    return null;
                }

                foreach ($this->user_info['contactInfos'] as $contactInfo) {
                    if ($contactInfo['contactType'] === 'EMAIL') {
                        return $contactInfo['contactNo'];
                    }
                }
                return null;
            },
        );
    }

    public function cardList(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->card_list ?? null,
        );
    }

    public function firstCard(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->card_list[0] ?? null,
        );
    }

    public function firstCardAccountNumber(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->card_list[0]['accountNumber'] ?? null,
        );
    }

    public function firstCardMaskedCardNo(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->card_list[0]['maskedCardNo'] ?? null,
        );
    }
}
