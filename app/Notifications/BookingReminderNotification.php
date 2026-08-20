<?php

namespace App\Notifications;

use App\Domains\Booking\Models\Booking;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->onConnection(config('whatsapp.queue.connection', 'database'));
        $this->onQueue(config('whatsapp.queue.queue', 'whatsapp'));
    }

    public function via($notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable): array
    {
        $to = $this->resolveRecipient($notifiable);
        $item = $this->booking->items->first();
        $formattedDate = $this->booking->booking_date->format('d F Y');
        $startTime = $item ? substr($item->start_time, 0, 5) : '';
        $outletName = $this->booking->outlet->name;

        $message = "Halo {$this->booking->customer->name},\n\nIni adalah pengingat untuk jadwal perawatan Anda di *MORE Hair Studio*:\n\n" .
                   "• *Booking ID*: {$this->booking->booking_code}\n" .
                   "• *Outlet*: {$outletName}\n" .
                   "• *Tanggal*: {$formattedDate}\n" .
                   "• *Jam*: {$startTime} WIB\n\n" .
                   "Kami tunggu kedatangan Anda!";

        return [
            'to' => $to,
            'message' => $message,
            'template' => 'booking_reminder',
            'parameters' => [
                'language' => 'id',
                'body' => [
                    $this->booking->customer->name,
                    $this->booking->booking_code,
                    $formattedDate,
                    $startTime,
                    $outletName
                ]
            ],
            'booking_id' => $this->booking->id,
            'customer_id' => $this->booking->customer_id,
        ];
    }

    protected function resolveRecipient($notifiable): ?string
    {
        if (is_string($notifiable)) {
            return $notifiable;
        }
        if ($notifiable instanceof \App\Domains\Customer\Models\Customer) {
            return $notifiable->phone;
        }
        if ($notifiable instanceof Booking) {
            return $notifiable->customer->phone;
        }
        return null;
    }
}
