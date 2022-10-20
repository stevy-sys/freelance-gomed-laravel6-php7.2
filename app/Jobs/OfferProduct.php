<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OfferProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $productOffer;
    public $start_offer;
    public $exp_offer;
    public $offer;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($productOffer,$request)
    {
        $request = $request->all();
        $this->start_offer = $request['start_offer'];
        $this->exp_offer = $request['exp_offer'];
        $this->offer = $request['offer'];
        $this->productOffer = $productOffer;
       
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $offer = $this->productOffer->offer()->create([
            'rates' => $this->offer,
            'exp_offer' => $this->exp_offer,
            'start_offer' => $this->start_offer
        ]);
        
        DeleteOffer::dispatch($offer)->delay(Carbon::parse($offer->exp_offer));
    }
}
