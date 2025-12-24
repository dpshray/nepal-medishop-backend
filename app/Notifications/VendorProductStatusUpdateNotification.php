<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorProductStatusUpdateNotification extends Notification
{
    use Queueable;
    private $subject = null;
    /**
     * Create a new notification instance.
     */
    public function __construct(public $product_vendor)
    {
        $product_name = $this->product_vendor->product->name;
        $this->subject = "Your product $product_name has been approved by the admin and is now live for customers.";
        if ($this->product_vendor->is_approved) {
            $this->subject = "Your product $product_name has been rejected by the admin. Please review the details and resubmit.";
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        return (new MailMessage)->subject($this->subject)
            ->view('mail.admin.vendor-product-approval', [
                'product_vendor' => $this->product_vendor
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            "subject" => $this->subject,
            'product_vendor_id' => $this->product_vendor->id
        ];
    }
}
