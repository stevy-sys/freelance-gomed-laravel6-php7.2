<?php

use App\Models\Cities;
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
        $city = Cities::first();
        if (isset($city) || $city == null) {
            $data = [
                "name" => "mada",
                "lat" => "12",
                "lng" => "43",
                "extra_field" => null,
                "status" => 1
            ];
            $city = Cities::create($data) ;
        }

        $faker = Faker\Factory::create();
        $city = Cities::first();
        $store = json_decode(FacadesFile::get('public/files/store.json'),true);
        $product = json_decode(FacadesFile::get('public/files/product.json'),true);
        $userStore = json_decode(FacadesFile::get('public/files/userStore.json'),true);
        
        for ($i=0; $i < 2 ; $i++) {
            //create user
            $userStore['first_name'] = $faker->name ;
            $userStore['email'] = $faker->unique()->safeEmail ;
            $userStore['last_name'] = $faker->name ;
            $userStore['password'] = Hash::make('azerazer');
            $user = User::create($userStore);


            //create store
            for ($i=0; $i < 2; $i++) { 
                $store['name'] = $faker->company ;
                $store['uid'] = $user->id ;
                $store['mobile'] = $faker->phoneNumber ;
                $store['address'] = $faker->streetAddress ;
                $store['cid'] = $city->id;
                $store = Stores::create($store);
    
                //create product for store
                for ($i=0; $i < 2; $i++) { 
                    $product['name'] = $faker->word ;
                    $product['store_id'] = $store->id ;
                    $productCreate = Products::create($product);
                }
            }
        }
    }
}
