<?php

namespace App\Mail\Venue;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BookingLapangan extends Mailable
{
    use Queueable, SerializesModels;

    public $bookingHeader;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(\App\Venue\BookingHeader $bookingHeader)
    {
        $this->bookingHeader = $bookingHeader;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('booking@situsewa.com')
            ->subject('Booking Lapangan Situsewa Berhasil')
            ->view('emails.layout.booking_lapangan');
    }
}
