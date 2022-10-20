<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Cities;
use App\Models\Stores;
use App\Models\Countrie;
use App\Models\Products;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File as FacadesFile;

class DataSeedTemp extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countrys = [
            [
                'currency' => 'MGA',
                'code_pays' => 'MG',
                'pays' => 'MADAGASCAR',
                'curr_string' => 'MGA' 
            ],
            [
                'currency' => '€',
                'code_pays' => 'FR',
                'pays' => 'FRANCE',
                'curr_string' => 'EUR' 
            ],
            [
                'currency' => '$',
                'code_pays' => 'US',
                'pays' => 'USA',
                'curr_string' => 'USD' 
            ] 
        ];
        foreach ($countrys as $country) {
            $faker = Faker\Factory::create();
            # code...
                $country = Countrie::create([
                    'currency' => $country['currency'],
                    'code_pays' => $country['code_pays'],
                    'pays' => $country['pays'],
                    'curr_string' => $country['curr_string'],
                ]);
            
                
                // $country_code = $faker->countryCode ;
                // $isExiste = Countrie::where('code_pays',$country_code)->first();
                // $country = null;
                // if ($isExiste != null) {
                //     $country = $isExiste ;
                // }
                // else{
                //     $country = Countrie::create([
                //         'currency' => $faker->currencyCode,
                //         'code_pays' => $country_code,
                //         'pays' => $faker->locale,
                //     ]);
                // }
    
                // $city = Cities::first();
                // if (isset($city) || $city == null) {
                    $data = [
                        "name" => $faker->city,
                        "lat" => "12",
                        "lng" => "43",
                        "extra_field" => null,
                        "status" => 1
                    ];
                    $city = Cities::create($data);
                // }
    
                
                for ($j=0; $j < 5 ; $j++) {
                    $store = json_decode(FacadesFile::get('public/files/store.json'),true);
                    $product = json_decode(FacadesFile::get('public/files/product.json'),true);
                    $userStore = json_decode(FacadesFile::get('public/files/userStore.json'),true);
                    //create user
                    $userStore['first_name'] = $faker->name ;
                    $userStore['email'] = $faker->unique()->safeEmail;
                    $userStore['last_name'] = $faker->name ;
                    $userStore['password'] = Hash::make('azerazer');
                    $user = User::create($userStore);
    
                    //create store
                    $store['name'] = $faker->company ;
                    $store['uid'] = $user->id;
                    $store['mobile'] = $faker->phoneNumber ;
                    $store['address'] = $faker->streetAddress ;
                    $store['cid'] = $city->id;
                    $store['countrie_id'] = $country->id;
                    $store = Stores::create($store);
    
    
                    
            
                    //create product for store
                    for ($k=0; $k < rand(10,20) ; $k++) { 
                        $product['name'] = $faker->word ;
                        $product['store_id'] = $store->id ;
                        $product['original_price'] = rand(100,500);
                        $product['sell_price'] = rand(100,500);
                        $rand = rand(0,10);
                        if ($rand % 2 == 0) {
                            $product['medical_prescription'] = true ;
                        }else{
                            $product['medical_prescription'] = false ;
                        }
                        $productCreate = Products::create($product);
                        $rand = rand(0,10);
                        $productCreate->quantity()->create([
                            'stock' => rand(10,50),
                            'in_stock' => true,
                        ]);
                        if ($rand % 2 == 0) {
                            $productCreate->offer()->create([
                                'rates' => rand(1,25),
                                'exp_offer' => Carbon::now()->subMinutes(rand(55, 100)),
                                'start_offer' => Carbon::now()->subMinutes(rand(1, 55))
                            ]);
                        }
                    }
                }
        }
    }
}
