<?php

namespace App\Notifications;

use App\Domains\Booking\Models\Booking;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingCheckInReminderNotification extends Notification implements ShouldQueue
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
        $startTime = $item ? substr($item->start_time, 0, 5) : '';

        $message = "Booking Anda dijadwalkan pukul {$startTime} WIB.\n" .
                   "Mohon melakukan check-in ketika sudah tiba di outlet.";

        return [
            'to' => $to,
            'message' => $message,
            'template' => 'booking_check_in_reminder',
            'parameters' => [
                'language' => 'id',
                'body' => [
                    $this->booking->customer->name,
                    $startTime,
                    $this->booking->outlet->name,
                    $this->booking->booking_code
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
