<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEmail extends Notification
{
    use Queueable;

    public $booking;

    public $ticket;

    public function __construct($booking, $ticket = null)
    {
        $this->booking = $booking;
        $this->ticket = $ticket;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $bookingDate = $this->booking->booking_date->format('d M Y');

        $mail = (new MailMessage)
            ->subject('Konfirmasi Tiket Reservasi - More Hair Studio')
            ->greeting('Halo '.$this->booking->customer->name.',')
            ->line('Pemesanan Anda telah berhasil dikonfirmasi. Berikut rincian reservasi Anda:')
            ->line('Kode Booking: '.$this->booking->booking_code)
            ->line('Outlet: '.$this->booking->outlet->name)
            ->line('Tanggal: '.$bookingDate);

        if ($this->ticket && ! empty($this->ticket->pdf_path)) {
            $mail->line('Detail tiket digital resmi Anda telah kami lampirkan dalam berkas PDF di email ini.')
                ->attach(public_path($this->ticket->pdf_path), [
                    'as' => 'Ticket-'.$this->booking->booking_code.'.pdf',
                    'mime' => 'application/pdf',
                ]);
        }

        $mail->line('Ini adalah email otomatis. Mohon tidak membalas email ini.');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
