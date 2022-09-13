<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailCreateAccount extends Mailable
{
    use Queueable, SerializesModels;
    public $email ;
    public $username ;
    public $subject ;
    public $generalInfo ;
    public $otp ;
    /**
     * Create a new message instance.
     *
     * @return void
     */ 
    public function __construct($email,$username,$subject,$generalInfo,$otp)
    {
        $this->email = $email;
        $this->username = $username;
        $this->subject = $subject;
        $this->generalInfo = $generalInfo;
        $this->otp = $otp;
    }

    /**
     * Build the message. 
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env('MAIL_USERNAME'))
                    ->with([
                        'app_name' => $this->generalInfo,            
                        'otp' => $this->otp            
                    ])
                    ->subject($this->subject)
                    ->view('mails.register');
        // return $this->view('view.name');
    }
}
