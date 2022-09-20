<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommandeMail extends Mailable
{
    use Queueable, SerializesModels;
    public $data ;
    public $subject ;
    public $user ;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data,$subject,$user)
    {
        $this->data = $data['data'];
        $this->subject = $subject; 
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env('MAIL_USERNAME'))
                    ->subject($this->subject)
                    ->view('mails.orders');
    }
}
