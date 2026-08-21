<?php

namespace App\Notifications;

use App\Domains\Booking\Models\Booking;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
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
        $formattedDate = $this->booking->booking_date->format('d F Y');
        $startTime = $item ? substr($item->start_time, 0, 5) : '';
        $endTime = $item ? substr($item->end_time, 0, 5) : '';
        $outletName = $this->booking->outlet->name;
        $stylistName = $this->booking->stylist->name;

        $message = "Booking berhasil dikonfirmasi.\n" .
                   "*More Hair Studio*\n\n" .
                   "• *Outlet*: {$outletName}\n" .
                   "• *Tanggal*: {$formattedDate}\n" .
                   "• *Jam*: {$startTime} - {$endTime} WIB\n" .
                   "• *Stylist*: {$stylistName}\n" .
                   "• *Layanan*: {$serviceName}\n" .
                   "• *Booking ID*: {$this->booking->booking_code}\n" .
                   "• *Tiket Digital & QR Code*: " . route('booking.ticket', ['code' => $this->booking->booking_code]);

        return [
            'to' => $to,
            'message' => $message,
            'template' => 'booking_confirmation',
            'parameters' => [
                'language' => 'id',
                'body' => [
                    $outletName,
                    $formattedDate,
                    $startTime,
                    $endTime,
                    $stylistName,
                    $serviceName,
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
