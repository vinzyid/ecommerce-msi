<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlaced extends Notification
{
    public function __construct(private readonly Order $order)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Pesanan {$this->order->order_number} berhasil dibuat")
            ->greeting("Halo, {$this->order->customer_name}!")
            ->line("Pesanan {$this->order->order_number} sudah kami terima.")
            ->line('Total: Rp'.number_format((float) $this->order->total, 0, ',', '.'))
            ->line('Metode pembayaran: '.$this->order->paymentLabel())
            ->action('Lihat pesanan', route('orders.show', $this->order));
    }
}
