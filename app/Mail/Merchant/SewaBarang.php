<?php

namespace App\Mail\Merchant;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SewaBarang extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $transaksi;
    public $subject;
    public function __construct(\App\Produk\Transaksi $transaksi, $subject)
    {
        $this->transaksi = $transaksi;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('booking@situsewa.com')
            ->subject($this->subject)
            ->view('emails.layout.booking_barang');
    }
}
