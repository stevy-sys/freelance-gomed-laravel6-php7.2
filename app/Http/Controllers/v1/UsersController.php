<?php
/*
  Authors : Sayna (Rahul Jograna)
  Website : https://sayna.io/
  App Name : Grocery Delivery App
  This App Template Source code is licensed as per the
  terms found in the Website https://sayna.io/license
  Copyright and Good Faith Purchasers © 2021-present Sayna.
*/
namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Drivers;
use App\Models\User;
use App\Models\Products;
use App\Models\Stores;
use Illuminate\Support\Facades\Mail;
use Validator;
use DB;
class UsersController extends Controller
{
    public function getById(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }

        $data = User::find($request->id);

        if (is_null($data)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }

        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }
        $data = User::find($request->id)->update($request->all());

        if (is_null($data)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }
        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function delete(Request $request){
     $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }
        $data = User::find($request->id);
        if ($data) {
            $data->delete();
            $response = [
                'data'=>$data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }
        $response = [
            'success' => false,
            'message' => 'Data not found.',
            'status' => 404
        ];
        return response()->json($response, 404);
    }

    public function getAll(){
        $data = User::where(['type'=>'user'])->get();
        if (is_null($data)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }

        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function admins(){
        $data = User::where(['type'=>'admin'])->get();
        if (is_null($data)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }

        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function importData(Request $request){
        $request->validate([
            "csv_file" => "required",
        ]);
        $file = $request->file("csv_file");
        $csvData = file_get_contents($file);
        $rows = array_map("str_getcsv", explode("\n", $csvData));
        $header = array_shift($rows);
        foreach ($rows as $row) {
            if (isset($row[0])) {
                if ($row[0] != "") {

                    if(count($header) == count($row)){
                        $row = array_combine($header, $row);
                        $insertInfo =  array(
                            'id' => $row['id'],
                            'first_name' => $row['first_name'],
                            'last_name' => $row['last_name'],
                            'email' => $row['email'],
                            'password' => $row['password'],
                            'country_code' => $row['country_code'],
                            'mobile' => $row['mobile'],
                            'cover' => $row['cover'],
                            'lat' => $row['lat'],
                            'lng' => $row['lng'],
                            'gender' => $row['gender'],
                            'verified' => $row['verified'],
                            'type' => $row['type'],
                            'date' => $row['date'],
                            'fcm_token' => $row['fcm_token'],
                            'others' => $row['others'],
                            'stripe_key' => $row['stripe_key'],
                            'status' => $row['status'],
                        );

                        $checkLead  =  User::where("email", "=", $row["email"])->first();

                        if (!is_null($checkLead)) {
                            DB::table('users')->where("email", "=", $row["email"])->update($insertInfo);
                        }

                        else {
                            DB::table('users')->insert($insertInfo);
                        }
                    }
                }
            }
        }
        $response = [
            'data'=>'Done',
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function sendMailToUsers(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'subjects' => 'required',
                'content' => 'required',
            ]);
            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Validation Error.', $validator->errors(),
                    'status'=> 500
                ];
                return response()->json($response, 404);
            }
            $users = User::select('email','first_name','last_name')->where('type','user')->get();
            $general  = DB::table('general')->select('name','email')->first();
            foreach($users as $user){
                Mail::send([], [], function ($message) use ($request,$user,$general) {
                    $message->to($user->email)
                      ->from($general->email, $general->name)
                      ->subject($request->subjects)
                      ->setBody($request->content, 'text/html');
                  });
            }

            $response = [
                'success' => true,
                'message' => 'success',
                'status' => 200
            ];
            return $response;

        } catch (Exception $e) {
            return response()->json($e->getMessage(),200);
        }

    }

    public function sendMailToStores(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'subjects' => 'required',
                'content' => 'required',
            ]);
            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Validation Error.', $validator->errors(),
                    'status'=> 500
                ];
                return response()->json($response, 404);
            }
            $users = User::select('email','first_name','last_name')->where('type','store')->get();
            $general  = DB::table('general')->select('name','email')->first();
            foreach($users as $user){
                Mail::send([], [], function ($message) use ($request,$user,$general) {
                    $message->to($user->email)
                      ->from($general->email, $general->name)
                      ->subject($request->subjects)
                      ->setBody($request->content, 'text/html');
                  });
            }

            $response = [
                'success' => true,
                'message' => 'success',
                'status' => 200
            ];
            return $response;

        } catch (Exception $e) {
            return response()->json($e->getMessage(),200);
        }

    }

    public function sendMailToAll(Request $request){
        // Drivers
        try {
            $validator = Validator::make($request->all(), [
                'subjects' => 'required',
                'content' => 'required',
            ]);
            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Validation Error.', $validator->errors(),
                    'status'=> 500
                ];
                return response()->json($response, 404);
            }
            $users = User::select('email','first_name','last_name')->get();
            $drivers =  Drivers::select('email','first_name','last_name')->get();
            $general  = DB::table('general')->select('name','email')->first();
            foreach($users as $user){
                Mail::send([], [], function ($message) use ($request,$user,$general) {
                    $message->to($user->email)
                      ->from($general->email, $general->name)
                      ->subject($request->subjects)
                      ->setBody($request->content, 'text/html');
                  });
            }

            foreach($drivers as $user){
                Mail::send([], [], function ($message) use ($request,$user,$general) {
                    $message->to($user->email)
                      ->from($general->email, $general->name)
                      ->subject($request->subjects)
                      ->setBody($request->content, 'text/html');
                  });
            }

            $response = [
                'success' => true,
                'message' => 'success',
                'status' => 200
            ];
            return $response;

        } catch (Exception $e) {
            return response()->json($e->getMessage(),200);
        }
    }

    public function sendMailToDrivers(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'subjects' => 'required',
                'content' => 'required',
            ]);
            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Validation Error.', $validator->errors(),
                    'status'=> 500
                ];
                return response()->json($response, 404);
            }
            $drivers =  Drivers::select('email','first_name','last_name')->get();
            $general  = DB::table('general')->select('name','email')->first();

            foreach($drivers as $user){
                Mail::send([], [], function ($message) use ($request,$user,$general) {
                    $message->to($user->email)
                      ->from($general->email, $general->name)
                      ->subject($request->subjects)
                      ->setBody($request->content, 'text/html');
                  });
            }

            $response = [
                'success' => true,
                'message' => 'success',
                'status' => 200
            ];
            return $response;

        } catch (Exception $e) {
            return response()->json($e->getMessage(),200);
        }
    }

    public function getInfo(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }

        $user = DB::table('users')->select('first_name','last_name','cover','email','country_code','mobile')->where('id',$request->id)->first();
        $address = DB::table('address')->where('uid',$request->id)->get();
        $orders = DB::table('orders')->where('uid',$request->id)->get();
        $rating = DB::table('rating')->where('uid',$request->id)->get();
        foreach($rating as $loop){
            if($loop && $loop->did && $loop->did !=0){
                $loop->driverInfo = Drivers::where('id',$loop->did)->select('first_name','last_name','cover','email','country_code','mobile')->first();
            }

            if($loop && $loop->pid && $loop->pid !=0){
                $loop->productInfo = Products::where('id',$loop->pid)->first();
            }

            if($loop && $loop->sid && $loop->sid !=0){
                $loop->storeInfo = Stores::where('uid',$loop->sid)->first();
            }
        }
        $data = [
            'user'=>$user,
            'address'=>$address,
            'orders'=>$orders,
            'rating'=>$rating
        ];
        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }
}
