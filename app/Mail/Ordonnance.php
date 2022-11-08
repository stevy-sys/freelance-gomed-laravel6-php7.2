<?php

namespace App\Mail;

use App\Models\DetailPaimentUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Ordonnance extends Mailable
{
    use Queueable, SerializesModels;

    public $detailPaiment ;
    public $user ;
    public $currency ;
    public $total ;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($detail,$user,$total)
    {
        $this->detailPaiment = $detail;
        $this->user = $user;
        $this->currency = $user->store->countrie->currency;
        $this->total = $total;

       
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->view('mails.ordonnance');
        foreach ($this->detailPaiment['orderStore'] as $value) {
            if ($value->mediable) {
                $email->attach(explode("public",public_path())[0].'/storage/ordonnance/'.$value->mediable->file);
            }
        }
        return $email;
    }
}
