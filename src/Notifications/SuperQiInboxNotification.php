<?php

namespace Ht3aa\QiCard\Notifications;

use Ht3aa\QiCard\Notifications\Channels\SuperQiInboxChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuperQiInboxNotification extends Notification
{
    use Queueable;

    public string $message;

    public string $title;

    public ?string $url = null;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, ?string $url = null)
    {
        $this->message = $message;
        $this->title = $title;
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [SuperQiInboxChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toSuperQi(object $notifiable): SuperQiInboxChannel
    {
        return new SuperQiInboxChannel($this->title, $this->message, $this->url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
