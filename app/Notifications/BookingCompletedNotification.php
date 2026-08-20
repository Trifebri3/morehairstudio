<?php

namespace App\Notifications;

use App\Domains\Booking\Models\Booking;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingCompletedNotification extends Notification implements ShouldQueue
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

        $message = "Halo {$this->booking->customer->name},\n\nTerima kasih telah melakukan perawatan di *MORE Hair Studio*!\n\n" .
                   "Booking Anda dengan kode *{$this->booking->booking_code}* telah dinyatakan selesai. " .
                   "Bagikan pengalaman Anda dan dapatkan voucher potongan untuk sesi berikutnya. Sampai jumpa kembali!";

        return [
            'to' => $to,
            'message' => $message,
            'template' => 'booking_completed',
            'parameters' => [
                'language' => 'id',
                'body' => [
                    $this->booking->customer->name,
                    $this->booking->booking_code,
                    $this->booking->outlet->name
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
