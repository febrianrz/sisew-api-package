<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SuccessResetPasswordCustomer extends Mailable
{
    use Queueable, SerializesModels;
    public $admin;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(\App\Customer $admin)
    {
        $this->admin = $admin;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('noreply@situsewa.com')
            ->subject('Reset Password Akun Situsewa Berhasil')
            ->view('emails.auth.customer_success_reset');
    }
}
