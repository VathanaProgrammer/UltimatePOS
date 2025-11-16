<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class NewOnlineOrderNotification extends Notification
{
    use Queueable;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    // Send only to database (system notification)
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'msg' => $this->data['msg'], // message to show
            'link' => $this->data['link'] ?? '#', // link if clicked
            'icon_class' => $this->data['icon_class'] ?? 'fa fa-shopping-cart', // optional icon
            'created_at' => now(),
        ];
    }
}