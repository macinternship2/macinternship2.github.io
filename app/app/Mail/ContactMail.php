<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $toEmail, $fromEmail, $subject, $data;

    /**
     * Create a new message instance.
     *
     * @param $toEmail
     * @param $fromEmail
     * @param $subject
     * @param $data
     * @internal param $to
     * @internal param $from
     * @internal param $body
     */
    public function __construct($fromEmail, $subject, $data, $toEmail = null)
    {
        $this->toEmail = $toEmail ? $toEmail : 'accesslocator@gmail.com';
        $this->fromEmail = $fromEmail;
        $this->subject = $subject;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->fromEmail)
            ->subject($this->subject)
            ->to($this->toEmail)
            ->view('mails.contact');
    }
}
