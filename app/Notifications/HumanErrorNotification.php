<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HumanErrorNotification extends Notification
{
    use Queueable;

    public $message;
    public $userId;
    public $userName;
    public $module;
    public $details;
    public $tyreCompanyId;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $userId, $userName, $module, $details = [], $tyreCompanyId = null)
    {
        $this->message = $message;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->module = $module;
        $this->details = $details;
        $this->tyreCompanyId = $tyreCompanyId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'module' => $this->module,
            'details' => $this->details,
            'tyre_company_id' => $this->tyreCompanyId,
        ];
    }
}
