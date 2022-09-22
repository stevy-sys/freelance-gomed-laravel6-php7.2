<?php

use App\Models\General;
use Illuminate\Database\Seeder;

class GeneralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            "name" => "sayna",
            "mobile" => "97",
            "email"=> "mitiqeg@mailinator.com",
            "address"=> "Omnis incididunt omn",
            "city"=> "Quidem dolorem tempo",
            "state"=> "Totam unde optio ve",
            "zip"=> "Cillum cumque rerum",
            "country"=> "Quo reiciendis nihil",
            "min"=> 38,
            "free"=> 35,
            "tax"=> 22,
            "shipping"=> "fixed",
            "shippingPrice"=> 79,
            "status"=> 1,
            "extra_field"=> null
        ];
        General::create($data);
    }
}
