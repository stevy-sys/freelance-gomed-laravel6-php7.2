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
use App\Models\DriverRequest;
use App\Models\Drivers;
use App\Models\Otp;
use App\Models\General;
use App\Models\Settings;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Validator;
use DB;
class DriverRequestController extends Controller
{
    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'first_name'=>'required',
            'last_name'=>'required',
            'mobile'=>'required',
            'cover'=>'required',
            'country_code'=>'required',
            'password' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'address' => 'required',
            'cid' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }

        $emailValidation = Drivers::where('email',$request->email)->first();
        if (is_null($emailValidation) || !$emailValidation) {
            $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];
            $data = Drivers::where($matchThese)->first();
            if (is_null($data) || !$data) {
                $driverInfo = DriverRequest::create([
                    'email' => $request->email,
                    'first_name'=>$request->first_name,
                    'last_name'=>$request->last_name,
                    'status'=>0,
                    'mobile'=>$request->mobile,
                    'country_code'=>$request->country_code,
                    'password' => $request->password,
                    'cover' => $request->cover,
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                    'address' => $request->address,
                    'city' => $request->cid,
                    'gender' => $request->gender,
                    'extra_field' => $request->extra_field,
                ]);

                return response()->json(['data'=>$driverInfo,'status'=>200], 200);
            }
            $response = [
                'success' => false,
                'message' => 'Mobile is already registered.',
                'status' => 500
            ];
            return response()->json($response, 500);
        }
        $response = [
            'success' => false,
            'message' => 'Email is already taken',
            'status' => 500
        ];
        return response()->json($response, 500);
    }

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

        $data = DriverRequest::find($request->id);

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
        $data = DriverRequest::find($request->id)->update($request->all());

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
        $data = DriverRequest::find($request->id);
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
        $data = DriverRequest::all();
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

    public function getNewRequest(){
        $data = DB::table('driver_request')
                ->select('driver_request.id as id','driver_request.address as address','driver_request.cover as cover','driver_request.city as city','driver_request.gender as gender',
                'driver_request.first_name as first_name','driver_request.last_name as last_name','driver_request.mobile as mobile','driver_request.country_code as country_code','cities.name as city_name')
                ->join('cities','driver_request.city','cities.id')
                ->where('driver_request.status',0)
                ->get();
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

    public function checkEmail(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'subject' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }
        $data = Drivers::where('email',$request->email)->first();

        if (is_null($data)) {
            $settings = Settings::take(1)->first();
            $generalInfo = General::take(1)->first();
            $mail = $request->email;
            $username = $request->email;
            $subject = $request->subject;
            $otp = random_int(100000, 999999);
            $savedOTP = Otp::create([
                'otp'=>$otp,
                'email'=>$request->email,
                'status'=>0,
            ]);
            $mailTo = Mail::send('mails/register',
                [
                    'app_name'      =>$generalInfo->name,
                    'otp'          => $otp
                ]
                , function($message) use($mail,$username,$subject,$generalInfo){
                $message->to($mail, $username)
                ->subject($subject);
                $message->from(env('MAIL_USERNAME'),$generalInfo->name);
            });

            $response = [
                'data'=>true,
                'mail'=>$mailTo,
                'otp_id'=>$savedOTP->id,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }

        $response = [
            'data' => false,
            'message' => 'email is already registered',
            'status' => 500
        ];
        return response()->json($response, 200);
    }

    public function rejectRequest(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'subject' => 'required',
            'status' => 'required',
            'message' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }
        $data = DriverRequest::find($request->id)->update($request->only('status'));
        $info = DriverRequest::find($request->id);
        $settings = Settings::take(1)->first();
        $generalInfo = General::take(1)->first();
        $mail = $info->email;
        $username = $info->email;
        $subject = $request->subject;

        $mailTo = Mail::send('mails/reject',
            [
                'app_name'      =>$generalInfo->name,
                'message_info'       =>$request->message,
            ]
            , function($message) use($mail,$username,$subject,$generalInfo){
            $message->to($mail, $username)
            ->subject($subject);
            $message->from(env('MAIL_USERNAME'),$generalInfo->name);
        });

        $response = [
            'data'=>true,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);

    }

    public function acceptRequest(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'subject' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }
        $data = DriverRequest::find($request->id)->update($request->only('status'));
        $info = DriverRequest::find($request->id);
        $emailValidation = Drivers::where('email',$info->email)->first();
        if (is_null($emailValidation) || !$emailValidation) {

            $matchThese = ['country_code' => $info->country_code, 'mobile' => $info->mobile];
            $data = Drivers::where($matchThese)->first();
            if (is_null($data) || !$data) {

                $data = Drivers::create([
                    'email' => $info->email,
                    'first_name'=>$info->first_name,
                    'last_name'=>$info->last_name,
                    'status'=>1,
                    'mobile'=>$info->mobile,
                    'country_code'=>$info->country_code,
                    'password' => $info->password,
                    'cover' => $info->cover,
                    'lat' => $info->lat,
                    'lng' => $info->lng,
                    'address' => $info->address,
                    'city' => $info->city,
                    'gender' => $info->gender,
                    'extra_field' => $info->extra_field,
                ]);
                $settings = Settings::take(1)->first();
                $generalInfo = General::take(1)->first();
                $mail = $info->email;
                $username = $info->email;
                $subject = $request->subject;

                $mailTo = Mail::send('mails/accepted',
                    [
                        'app_name'      =>$generalInfo->name,
                    ]
                    , function($message) use($mail,$username,$subject,$generalInfo){
                    $message->to($mail, $username)
                    ->subject($subject);
                    $message->from(env('MAIL_USERNAME'),$generalInfo->name);
                });

                $response = [
                    'data'=>true,
                    'success' => true,
                    'status' => 200,
                ];
                return response()->json($response, 200);

            }


            $response = [
                'success' => false,
                'message' => 'Mobile is already registered.',
                'status' => 500
            ];
            return response()->json($response, 500);
        }

    }

    public function thankyouReply(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'subject' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }
        $data = Drivers::where('email',$request->email)->first();

        if (is_null($data)) {
            $settings = Settings::take(1)->first();
            $generalInfo = General::take(1)->first();
            $mail = $request->email;
            $username = $request->email;
            $subject = $request->subject;

            $mailTo = Mail::send('mails/request',
                [
                    'app_name'      =>$generalInfo->name,
                ]
                , function($message) use($mail,$username,$subject,$generalInfo){
                $message->to($mail, $username)
                ->subject($subject);
                $message->from(env('MAIL_USERNAME'),$generalInfo->name);
            });

            $response = [
                'data'=>true,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }

        $response = [
            'data' => false,
            'message' => 'email is already registered',
            'status' => 500
        ];
        return response()->json($response, 200);
    }
}
