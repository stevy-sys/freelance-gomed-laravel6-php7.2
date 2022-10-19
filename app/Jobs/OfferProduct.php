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
    public $request;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($productOffer,$request)
    {
        $this->request = $request;
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
            'rates' => $this->request->offer,
            'exp_offer' => $this->request->exp_offer,
            'start_offer' => $this->request->start_offer
        ]);
        DeleteOffer::dispatch($offer)->delay(Carbon::parse($this->request->exp_offer));
    }
}
