<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RegisterAdmin extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $admin;

    public function __construct(\App\AdminVenue $admin)
    {
        $this->admin =  $admin;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('noreply@situsewa.com')
            ->subject('Registrasi Akun Admin Situsewa Berhasil')
            ->view('emails.auth.admin_register');
    }
}
