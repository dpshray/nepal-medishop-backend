<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserOrderNotification extends Notification
{
    use Queueable;
    private $subject = null;
    /**
     * Create a new notification instance.
     */
    public function __construct(public $order)
    {
        $order_code = $order->order_code;
        $customer_name = $order->customer_name;
        $order_price = $order->price;
        $this->subject = "A new order of order code $order_code has been placed by $customer_name with a total amount of $order_price.";
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
    public function toMail($notifiable)
    {
        return (new MailMessage)->subject($this->subject)
            ->view('mail.admin.order-placed', ['order' => $this->order]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subject' => $this->subject,
            'order_id' => $this->order->id
        ];
    }

    protected function productList(): array
    {
        return $this->order->orderItems->map(function ($OI) {
            return "{$OI->item->name} (Qty: {$OI->quantity}) - Rs. {$OI->price}";
        })->toArray();
    }
}
