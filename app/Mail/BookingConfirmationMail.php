<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking->loadMissing(['service', 'customer']);
    }

    public function build(): self
    {
        return $this->subject('Reserva recebida - '.$this->booking->service->name)
            ->view('emails.booking-confirmation', [
                'booking' => $this->booking,
            ]);
    }
}
