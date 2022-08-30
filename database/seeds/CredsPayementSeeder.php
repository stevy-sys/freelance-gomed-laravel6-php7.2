<?php

use App\Models\Payments;
use Illuminate\Database\Seeder;

class CredsPayementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payments = Payments::all();
        foreach ($payments as $payment) {
            if ($payment->name == 'Stripe') {
                $creds = json_encode(['secret' => env('STRIPE_SECRET')]);
                $payment->update(compact('creds'));
            }
            if ($payment->name == 'PayPal') {
                $creds = json_encode(['client_id' => env('PAYPAL_CLIENT_ID')]);
                $payment->update(compact('creds'));
            }

            if ($payment->name == 'RazorPay') {
                $creds = json_encode(['key' => env('RAZORPAY_KEY')]);
                $payment->update(compact('creds')); 
            }
        }
    }
}
