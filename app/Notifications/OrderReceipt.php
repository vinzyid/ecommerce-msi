<?php

namespace App\Notifications;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Struk/invoice pesanan yang dikirim ke email pembeli.
 */
class OrderReceipt extends Notification
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
        $order = $this->order->loadMissing('items');

        $pdf = Pdf::loadView('emails.order-receipt', ['order' => $order, 'forPdf' => true])->setPaper('a4')
            ->setOption('isFontSubsettingEnabled', true);

        return (new MailMessage)
            ->subject("Struk pesanan {$order->order_number} — ".config('app.name'))
            ->view('emails.order-receipt', ['order' => $order])
            ->attachData($pdf->output(), "Struk-{$order->order_number}.pdf", ['mime' => 'application/pdf']);
    }
}
