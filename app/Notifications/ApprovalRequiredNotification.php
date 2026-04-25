<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalRequiredNotification extends Notification
{
    use Queueable;

    protected $module;
    protected $submitterName;
    protected $actionUrl;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($module, $submitterName, $actionUrl)
    {
        $this->module = $module;
        $this->submitterName = $submitterName;
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
            'type' => 'approval_required',
            'module' => $this->module,
            'user_name' => $this->submitterName,
            'message' => "Data {$this->module} dari {$this->submitterName} membutuhkan persetujuan Anda.",
            'action_url' => $this->actionUrl,
            'status' => 'pending', // used for UI styling (yellow/orange)
        ];
    }
}
