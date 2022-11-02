<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\DetailPaimentUser;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActionOrder extends Mailable
{
    use Queueable, SerializesModels;
   
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
      
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env('MAIL_USERNAME'))
                    ->subject('Commande')
                    ->view('mails.rejectOrder');
    }
}
