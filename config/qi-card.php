<?php

// config for Ht3aa/QiCard
return [

    'user_info_scopes_enabled' => true,

    /**
     * If you want to store the card list in the database, you can enable this option.
     * The card list will be stored in the database and the original card list will be replaced with the database card list.
     * You should use CARD_LIST scope in the mini app to get the card list.
     */
    'card_list_scope_enabled' => false,

    /**
     * If you want to store the avatar url in the s3 storage, you can enable this option.
     * The avatar url will be stored in the s3 storage and the original url will be replaced with the s3 url.
     * You should use USER_AVATAR scope in the mini app to get the avatar url.
     */
    'store_avatar_url_in_s3_storage' => false,

    'update_user_data_every_login' => false,

    /**
     * This is the configuration for the Qi card api.
     * You can change the configuration by setting the values in the .env file.
     */
    'api' => [
        'base_url' => env('QI_CARD_API_BASE_URL'),
        'private_key' => env('QI_CARD_API_PRIVATE_KEY'),
        'public_key' => env('QI_CARD_API_PUBLIC_KEY'),
        'client_id' => env('QI_CARD_API_CLIENT_ID'),
    ],
];
