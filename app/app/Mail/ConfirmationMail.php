<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $toEmail;

    public $fromEmail;

    public $userName;

    public $confirmationLink;

    /**
     * Create a new message instance.
     *
     * @param $userName
     * @param $toEmail
     * @param $confirmationLink
     * @param null $fromEmail
     * @internal param $from
     * @internal param $to
     */
    public function __construct($userName, $toEmail, $confirmationLink, $fromEmail = null)
    {
        $this->userName = $userName;
        $this->toEmail = $toEmail;
        $this->fromEmail = $fromEmail ? $fromEmail : 'noreply@accesslocator.com';
        $this->confirmationLink = $confirmationLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->fromEmail)
            ->to($this->toEmail)
            ->subject('Confirm your AccessLocator account')
            ->view('mails.confirmation');
    }
}
