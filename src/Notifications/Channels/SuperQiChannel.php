<?php

namespace App\Notifications;

use App\Services\AliPayService;
use Filament\Notifications\Notification;

class SuperQiChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, SuperQiNotification $notification): void
    {
        try {
            $response = (new AliPayService(
                config('services.alipay.gateway_url'),
                config('services.alipay.merchant_private_key'),
                config('services.alipay.alipay_public_key'),
                config('services.alipay.client_id'),
            ))->sendNotification($notifiable->super_qi_access_token, $notification->title, $notification->message);

            if ($this->requestFailed($response->json())) {
                report($response->body().' - name: '.$notifiable->name.' - phone: '.$notifiable->phone.' - client id: '.$notifiable->id);
                Notification::make()
                    ->title('فشل في ارسال الأشعار عن طريق سوبر كي الى '.$notifiable->name)
                    ->danger()
                    ->body($response->body())
                    ->sendToDatabase($notifiable);
            }
        } catch (\Exception $e) {
            report($e->getMessage().' - name: '.$notifiable->name.' - phone: '.$notifiable->phone.' - client id: '.$notifiable->id);
            Notification::make()
                ->title('فشل في ارسال الأشعار عن طريق سوبر كي الى '.$notifiable->name)
                ->danger()
                ->body($e->getMessage())
                ->sendToDatabase($notifiable);
        }
    }

    private function requestFailed(?array $response): bool
    {
        if ($response === null) {
            return true;
        }

        return isset($response['result']) && $response['result']['resultStatus'] === 'F';
    }
}
