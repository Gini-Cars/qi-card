<?php

namespace Ht3aa\QiCard;

use Carbon\Carbon;
use Exception;
use Ht3aa\QiCard\Models\QiCardPayment;
use Ht3aa\QiCard\Models\QiCardUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class QiCard
{
    private PendingRequest $qiCardHttpRequest;

    private string $clientId;

    private string $privateKey;

    public function __construct()
    {
        $this->clientId = $this->getClientId();
        $this->privateKey = $this->getPrivateKey();
        $this->qiCardHttpRequest = Http::baseUrl(config('qi-card.api.base_url'));
    }

    public function createUser(string $authCode): QiCardUser
    {

        $url = '/v1/authorizations/applyToken';
        $params = [
            'grantType' => 'AUTHORIZATION_CODE',
            'authCode' => $authCode,
        ];

        $headers = $this->buildHeaders('POST', $url, $params);
        $response = $this->qiCardHttpRequest->replaceHeaders($headers)->post($url, $params)->json();

        if ($this->requestFailed($response)) {
            Log::error('response failed: '.json_encode($response));
            throw new UnprocessableEntityHttpException('qi card response failed try again later');
        }

        if (config('qi-card.user_info_scopes_enabled')) {
            $userInfo = $this->fetchUserInfo($response['accessToken']);
        }

        if (config('qi-card.card_list_scope_enabled')) {
            $cardList = $this->fetchAccountNumbers($response['accessToken']);
        }

        if (config('qi-card.store_avatar_url_in_s3_storage')) {
            $userInfo['avatar'] = $this->updateAvatarUrlToS3($userInfo['avatar']);
        }

        $user = QiCardUser::where('wallet_id', $userInfo['userId'])->first();

        if (! $user) {
            return QiCardUser::create([
                'wallet_id' => $userInfo['userId'],
                'qi_card_access_token' => $response['accessToken'],
                'user_info' => $userInfo,
                'card_list' => $cardList,
            ]);
        }

        if (config('qi-card.update_user_data_every_login')) {
            $user->update([
                'qi_card_access_token' => $response['accessToken'],
                'user_info' => $userInfo,
                'card_list' => $cardList,
            ]);
        }

        return $user;
    }

    private function fetchAccountNumbers(string $token): array
    {
        $url = '/v1/users/inquiryUserCardList';
        $params = [
            'accessToken' => $token,
        ];
        $headers = $this->buildHeaders('POST', $url, $params);

        $response = $this->qiCardHttpRequest->replaceHeaders($headers)->post($url, $params)->json();

        if (! isset($response['cardList']) || ! is_array($response['cardList']) || $this->requestFailed($response)) {
            Log::error('fetch account numbers failed: '.json_encode($response));
            throw new UnprocessableEntityHttpException('Request failed try again later');
        }

        return $response['cardList'];
    }

    private function fetchUserInfo(string $token): array
    {
        $url = '/v1/users/inquiryUserInfo';
        $params = [
            'accessToken' => $token,
        ];

        $headers = $this->buildHeaders('POST', $url, $params);
        $response = $this->qiCardHttpRequest->replaceHeaders($headers)->post($url, $params)->json();

        if ($this->requestFailed($response)) {
            Log::error('fetch user info failed: '.json_encode($response));
            throw new UnprocessableEntityHttpException('Request failed try again later');
        }

        return $response['userInfo'];
    }

    public function createPayment(string $amount, Model $product, string $orderDescription, QiCardUser $qiCardUser): QiCardPayment
    {
        $url = '/v1/payments/pay';
        $params = [
            'paymentExpiryTime' => now()->addHours(2)->toIso8601String(),
            'productCode' => config('qi-card.api.payment_product_code'),
            'paymentRequestId' => Str::uuid()->toString(),
            'paymentAmount' => [
                'currency' => 'IQD',
                'value' => $amount * 1000,
            ],
            'order' => [
                'orderDescription' => $orderDescription,
                'buyer' => [
                    'referenceBuyerId' => $qiCardUser->wallet_id,
                ],
            ],
            'paymentExpiryTime' => now()->addHours(2)->toIso8601String(),
            'paymentNotifyUrl' => config('qi-card.api.payment_webhook_url') ?? route('qi-card.payment.webhook'),
        ];

        $headers = $this->buildHeaders('POST', '/v1/payments/pay', $params);

        $response = $this->qiCardHttpRequest->replaceHeaders($headers)->post($url, $params)->json();

        if ($this->requestFailed($response)) {
            Log::error('initiate payment failed: '.json_encode($response));
            throw new UnprocessableEntityHttpException('Request failed try again later');
        }

        return QiCardPayment::create([
            'payment_request_id' => $params['paymentRequestId'],
            'payment_id' => $response['paymentId'],
            'redirect_url' => $response['redirectActionForm']['redirectUrl'],
            'amount' => $amount,
            'order_description' => $orderDescription,
            'qi_card_user_id' => $qiCardUser->id,
            'product_type' => $product::class,
            'product_id' => $product->id,
        ]);
    }

    public function sendSuperQiInboxNotification($accessToken, $title, $content, $url = '')
    {
        $url = '/v1/messages/sendInbox';

        $params = [
            'accessToken' => $accessToken,
            'requestId' => Str::uuid()->toString(),
            'templateCode' => 'MINI_APP_COMMON_INBOX',
            'templates' => [
                [
                    'templateParameters' => [
                        'Title' => $title,
                        'Content' => $content,
                        'Url' => $url,
                    ],
                ],
            ],
        ];

        $headers = $this->buildHeaders('POST', $url, $params);

        $response = $this->qiCardHttpRequest->replaceHeaders($headers)->post($url, $params)->json();

        if ($this->requestFailed($response)) {
            Log::error('send super qi notification failed: '.json_encode($response));
            throw new UnprocessableEntityHttpException('Request failed try again later');
        }

        return $response;
    }

    private function updateAvatarUrlToS3(string $avatarUrl): string
    {
        if (! $avatarUrl) {
            throw new Exception('Avatar url is not found in the user info of qi card information. please make sure you have added the USER_AVATAR scope in the mini app and enable the store_avatar_url_in_s3_storage option in the config file.');
        }

        $fullPath = 'qi-card-user-avatars/'.Str::uuid()->toString().'.jpeg';

        Storage::disk('s3')->put($fullPath, file_get_contents($avatarUrl));

        return $fullPath;
    }

    private function requestFailed(?array $response): bool
    {
        return $response === null || (isset($response['result']) && $response['result']['resultStatus'] === 'F');
    }

    private function getClientId()
    {
        $clientId = config('qi-card.api.client_id');

        if (! $clientId) {
            throw new Exception('QI_CARD_API_CLIENT_ID environment variable is not set with the proper value. Please ask the Qi card team to provide you with the correct client id.');
        }

        return $clientId;
    }

    private function getPrivateKey()
    {
        $privateKey = config('qi-card.api.private_key');

        if (! $privateKey) {
            throw new Exception('QI_CARD_API_PRIVATE_KEY environment variable is not set with the proper value. Please ask the Qi card team to provide you with the correct private key.');
        }

        return $privateKey;
    }

    private function buildHeaders(string $method, string $path, array $params): array
    {
        $currentTimestamp = Carbon::now()->toIso8601String();
        $signature = $this->generateSignature($method, $path, $currentTimestamp, json_encode($params, JSON_THROW_ON_ERROR));

        return [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Client-Id' => $this->clientId,
            'Request-Time' => $currentTimestamp,
            'Signature' => 'algorithm=RSA256, keyVersion=1, signature='.($signature),
            'Accept' => 'application/json',
        ];
    }

    private function generateSignature(string $httpMethod, string $path, string $reqTime, string $content): string
    {
        $signContent = $httpMethod.' '.$path."\n".$this->clientId.'.'.$reqTime.'.'.$content;

        openssl_sign($signContent, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }
}
