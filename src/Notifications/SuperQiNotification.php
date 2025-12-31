<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuperQiNotification extends Notification
{
    use Queueable;

    public string $message;

    public string $title;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message)
    {
        $this->message = $message;
        $this->title = $title;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [SuperQiChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toSuperQi(object $notifiable): SuperQiChannel
    {
        return new SuperQiChannel($this->title, $this->message);
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
