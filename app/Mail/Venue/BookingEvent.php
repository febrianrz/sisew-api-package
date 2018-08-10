<?php

namespace App\Mail\Venue;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BookingEvent extends Mailable
{
    use Queueable, SerializesModels;

    
    public $eventPeserta;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(\App\Venue\EventPeserta $eventPeserta)
    {
        $this->eventPeserta = $eventPeserta;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('booking@situsewa.com')
            ->subject('Booking Event Situsewa Berhasil')
            ->view('emails.layout.booking_event');
    }
}
