<?php

namespace App\Notifications;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class QiServiceChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, QiServiceNotification $notification): void
    {
        try {
            $response = Http::withHeaders(['token' => config('app.qi_service_notification.token')])
                ->post(config('app.qi_service_notification.base_url').'/notification/send', [
                    'mobileNumber' => $notifiable->phone,
                    'type' => 'NEWS',
                    'notification' => [
                        'title' => $notification->title,
                        'subTitle' => $notification->message,
                        'data' => [
                            'title' => $notification->title,
                            'content' => $notification->message,
                        ],
                    ],
                ]);
            if ($response->failed()) {
                report($response->body().' - name: '.$notifiable->name.' - phone: '.$notifiable->phone.' - client id: '.$notifiable->id);
                Notification::make()
                    ->title('فشل في ارسال الأشعار عن طريق تطبيق خدمات كي الى '.$notifiable->name)
                    ->danger()
                    ->body($response->body())
                    ->sendToDatabase($notifiable);
            }
        } catch (\Exception $e) {
            report($e->getMessage().' - name: '.$notifiable->name.' - phone: '.$notifiable->phone.' - client id: '.$notifiable->id);
            Notification::make()
                ->title('فشل في ارسال الأشعار عن طريق تطبيق خدمات كي الى '.$notifiable->name)
                ->danger()
                ->body($e->getMessage())
                ->sendToDatabase($notifiable);
        }
    }
}
