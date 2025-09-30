<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking->loadMissing(['service', 'customer']);
    }

    public function build(): self
    {
        return $this->subject('Atualizacao da reserva - '.$this->booking->service->name)
            ->view('emails.booking-status-updated', [
                'booking' => $this->booking,
            ]);
    }
}
