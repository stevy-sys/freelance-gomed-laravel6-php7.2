<?php

namespace App\Jobs;

use App\Models\Orders;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\RappelOrderStore as MailRappel;

class RappelOrderStore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $user;
    public $detailPaiment;
    public $userInRappel;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user,$detailPaiment,$userInRappel)
    {
        $this->user = $user ;
        $this->userInRappel = $userInRappel ;
        $this->detailPaiment = $detailPaiment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->user->email)->send(new MailRappel($this->detailPaiment,$this->userInRappel));       
    }
}
