<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustomerForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $admin;
    public $reset;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(\App\Customer $admin, \App\CustomerResetPassword $reset)
    {
        $this->admin = $admin;
        $this->reset = $reset;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('situsewaindonesia@gmail.com')
            ->subject('Reset Password Situsewa')
            ->view('emails.auth.customer_forgot');
    }
}
