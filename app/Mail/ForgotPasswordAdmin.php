<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForgotPasswordAdmin extends Mailable
{
    use Queueable, SerializesModels;


    public $admin;
    public $reset;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(\App\AdminVenue $admin, \App\UserResetPassword $reset)
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
        return $this->from('noreply@situsewa.com')
            ->subject('Reset Password Situsewa')
            ->view('emails.auth.admin_forgot');
    }
}
