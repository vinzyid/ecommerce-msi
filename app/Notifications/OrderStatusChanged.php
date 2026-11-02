<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
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
        $number = $this->order->order_number;

        $mail = (new MailMessage)->greeting("Halo, {$this->order->customer_name}!");

        match ($this->order->status) {
            'processing' => $mail->subject("Pesanan {$number} sedang diproses")
                ->line($this->order->payment_method === 'cod'
                    ? "Pesanan COD {$number} sudah dikonfirmasi dan sedang dikemas."
                    : "Pembayaran pesanan {$number} sudah kami terima. Pesanan sedang dikemas."),
            'shipped' => $mail->subject("Pesanan {$number} sedang dikirim")
                ->line("Pesanan {$number} sudah diserahkan ke {$this->order->courier}.")
                ->line("Nomor resi: {$this->order->tracking_number}"),
            'cancelled' => $mail->subject("Pesanan {$number} dibatalkan")
                ->line("Pesanan {$number} dibatalkan. Stok dan kuota voucher sudah dikembalikan."),
            default => $mail->subject("Status pesanan {$number} diperbarui")
                ->line("Status pesanan {$number} kini {$this->order->statusLabel()}."),
        };

        return $mail->action('Lihat pesanan', route('orders.show', $this->order));
    }
}
