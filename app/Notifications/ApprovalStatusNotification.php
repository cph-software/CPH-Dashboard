<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalStatusNotification extends Notification
{
    use Queueable;

    protected $module;
    protected $status; // 'Approved' or 'Rejected'
    protected $approverName;
    protected $actionUrl;
    protected $rejectReason;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($module, $status, $approverName, $actionUrl, $rejectReason = null)
    {
        $this->module = $module;
        $this->status = $status;
        $this->approverName = $approverName;
        $this->actionUrl = $actionUrl;
        $this->rejectReason = $rejectReason;
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
        $statusLower = strtolower($this->status);
        $message = "Data {$this->module} Anda telah di-{$statusLower} oleh {$this->approverName}.";
        
        $details = [];
        if ($statusLower === 'rejected' && $this->rejectReason) {
            $details['reject_reason'] = $this->rejectReason;
        }

        return [
            'type' => 'approval_status',
            'module' => $this->module,
            'user_name' => $this->approverName,
            'message' => $message,
            'action_url' => $this->actionUrl,
            'status' => $statusLower, // 'approved' (green) or 'rejected' (red)
            'details' => $details,
        ];
    }
}
