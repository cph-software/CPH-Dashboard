<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserCreatedNotification extends Notification
{
    use Queueable;

    protected $newUserName;
    protected $companyName;
    protected $creatorName;
    protected $actionUrl;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($newUserName, $companyName, $creatorName, $actionUrl)
    {
        $this->newUserName = $newUserName;
        $this->companyName = $companyName;
        $this->creatorName = $creatorName;
        $this->actionUrl = $actionUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'new_user_created',
            'module' => 'User Management',
            'user_name' => $this->creatorName,
            'message' => "Admin {$this->companyName} ({$this->creatorName}) telah menambahkan user baru: {$this->newUserName}.",
            'action_url' => $this->actionUrl,
            'status' => 'info', // blue
        ];
    }
}
