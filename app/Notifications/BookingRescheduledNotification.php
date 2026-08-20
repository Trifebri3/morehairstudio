<?php

namespace App\Notifications;

use App\Domains\Booking\Models\Booking;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingRescheduledNotification extends Notification implements ShouldQueue
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

        $serviceName = $item ? $item->service->name : 'Grooming Service';

        $message = "Jadwal booking Anda berhasil diubah.\n\n" .
                   "• *Booking ID*: {$this->booking->booking_code}\n" .
                   "• *Outlet*: {$outletName}\n" .
                   "• *Jadwal Baru*: {$formattedDate} pukul {$startTime} WIB\n\n" .
                   "Sampai jumpa di outlet!";

        return [
            'to' => $to,
            'message' => $message,
            'template' => 'booking_rescheduled',
            'parameters' => [
                'language' => 'id',
                'body' => [
                    $this->booking->booking_code,
                    $serviceName,
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
