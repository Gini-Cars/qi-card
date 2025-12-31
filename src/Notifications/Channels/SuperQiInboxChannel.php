<?php

namespace Ht3aa\QiCard\Notifications\Channels;

use Ht3aa\QiCard\Facades\QiCard;
use Ht3aa\QiCard\Notifications\SuperQiInboxNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SuperQiInboxChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, SuperQiInboxNotification $notification): void
    {
        try {
            QiCard::sendSuperQiInboxNotification($notifiable->qi_card_access_token, $notification->title, $notification->message, $notification->url);
        } catch (\Exception $e) {
            Log::error('send super qi inbox notification failed: '.$e->getMessage());
        }
    }
}
