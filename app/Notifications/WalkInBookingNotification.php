<?php

namespace App\Notifications;

use App\Domains\Booking\Models\Booking;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WalkInBookingNotification extends Notification implements ShouldQueue
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
        $serviceName = $item ? $item->service->name : 'Grooming Service';

        $message = "Halo {$this->booking->customer->name},\n\nRegistrasi Walk-In Anda di *MORE Hair Studio* berhasil!\n\n" .
                   "• *Booking ID*: {$this->booking->booking_code}\n" .
                   "• *Layanan*: {$serviceName}\n" .
                   "• *Antrean Anda sedang diproses.* Silakan menunggu panggilan di lounge. Terima kasih!";

        return [
            'to' => $to,
            'message' => $message,
            'template' => 'walk_in_booking',
            'parameters' => [
                'language' => 'id',
                'body' => [
                    $this->booking->customer->name,
                    $this->booking->booking_code,
                    $serviceName
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
