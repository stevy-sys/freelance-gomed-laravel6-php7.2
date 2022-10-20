<?php

namespace App\Jobs;

use App\Models\OptionProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteOffer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $offer ;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($offer)
    {
        $this->offer = $offer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        OptionProduct::find($this->offer->id)->delete();
    }
}
