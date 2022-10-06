<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\DetailPaimentUser;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommandeMail extends Mailable
{
    use Queueable, SerializesModels;
    public $detailPaiment ;

    public $user ;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user,$detailPaiment)
    {
        $this->user = $user;
        $detail = DetailPaimentUser::find($detailPaiment);
        $this->detailPaiment = $detail->load('orderUser.product');
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
                    ->view('mails.orders');
    }
}
