<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\DetailPaimentUser;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RappelOrderStore extends Mailable
{
    use Queueable, SerializesModels;

    public $detailPaiment;
    public $userInRappel;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($detailPaiment,$userInRappel)
    {
        $this->userInRappel = $userInRappel;
        $detail = DetailPaimentUser::find($detailPaiment);
        $this->detailPaiment = $detail->load('orderStore.product');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env('MAIL_USERNAME'))
                    ->subject('Rappel de commande')
                    ->view('mails.rappel-order');
    }
}
