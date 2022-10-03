<?php

use App\Models\Cities;
use App\Models\Countrie;
use App\Models\Products;
use App\Models\Stores;
use App\Models\User;
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
        for ($i=0; $i < 25 ; $i++) {
            $faker = Faker\Factory::create();
            $country_code = $faker->countryCode ;
            $isExiste = Countrie::where('code_pays',$country_code)->first();
            $country = null;
            if ($isExiste != null) {
                $country = $isExiste ;
            }
            else{
                $country = Countrie::create([
                    'currency' => $faker->currencyCode,
                    'code_pays' => $country_code,
                    'pays' => $faker->locale,
                ]);
            }

            // $city = Cities::first();
            // if (isset($city) || $city == null) {
                $data = [
                    "name" => $faker->city,
                    "lat" => "12",
                    "lng" => "43",
                    "extra_field" => null,
                    "status" => 1
                ];
                $city = Cities::create($data) ;
            // }

            
            for ($j=0; $j < 20 ; $j++) {
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
                for ($k=0; $k < rand(2,4) ; $k++) { 
                    $product['name'] = $faker->word ;
                    $product['store_id'] = $store->id ;
                    $product['original_price'] = rand(100,500);
                    $product['sell_price'] = rand(100,500) ;
                    $productCreate = Products::create($product);
                }
            }
        }
        
        // $city = Cities::first();
        // $store = json_decode(FacadesFile::get('public/files/store.json'),true);
        // $product = json_decode(FacadesFile::get('public/files/product.json'),true);
        // $userStore = json_decode(FacadesFile::get('public/files/userStore.json'),true);
        
        // for ($i=0; $i < 2 ; $i++) {
        //     //create user
        //     $userStore['first_name'] = $faker->name ;
        //     $userStore['email'] = $faker->unique()->safeEmail ;
        //     $userStore['last_name'] = $faker->name ;
        //     $userStore['password'] = Hash::make('azerazer');
        //     $user = User::create($userStore);


        //     //create store
        //     for ($i=0; $i < 2; $i++) { 
        //         $store['name'] = $faker->company ;
        //         $store['uid'] = $user->id ;
        //         $store['mobile'] = $faker->phoneNumber ;
        //         $store['address'] = $faker->streetAddress ;
        //         $store['cid'] = $city->id;
        //         $store = Stores::create($store);
    
        //         //create product for store
        //         for ($i=0; $i < 2; $i++) { 
        //             $product['name'] = $faker->word ;
        //             $product['store_id'] = $store->id ;
        //             $productCreate = Products::create($product);
        //         }
        //     }
        // }
    }
}
