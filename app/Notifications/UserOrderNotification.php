<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserOrderNotification extends Notification
{
    use Queueable;
    /**
     * Create a new notification instance.
     */
    public function __construct(public $order)
    {
        //
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
        return (new MailMessage)->subject('New Order Received - Order #' . $this->order->order_code)
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
            'subject' => 'New Order Received – Order #' . $this->order->order_code,
            'heading' => 'A new order has been placed on the system. Below are the order details:',
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
