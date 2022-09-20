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
use Illuminate\Support\Facades\Hash;
use Validator;
use Auth;
use DB;
class DriversController extends Controller
{
    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'country_code' => 'required',
            'mobile' => 'required',
            'cover' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'gender' => 'required',
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
                $driverInfo = Drivers::create([
                    'email' => $request->email,
                    'first_name'=>$request->first_name,
                    'last_name'=>$request->last_name,
                    'status'=>1,
                    'mobile'=>$request->mobile,
                    'country_code'=>$request->country_code,
                    'password' => Hash::make($request->password),
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

        $data = Drivers::find($request->id);

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
        $data = Drivers::find($request->id)->update($request->all());

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
        $data = Drivers::find($request->id);
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
        $data = Drivers::all();
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

    public function geyByCity(Request $request){
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
        $data = Drivers::where(['city'=>$request->id,'status'=>1])->get();
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
                            'date' => $row['date'],
                            'fcm_token' => $row['fcm_token'],
                            'others' => $row['others'],
                            'address' => $row['address'],
                            'current' => $row['current'],
                            'city' => $row['city'],
                            'status' => $row['status'],
                        );

                        $checkLead  =  Drivers::where("email", "=", $row["email"])->first();

                        if (!is_null($checkLead)) {
                            DB::table('drivers')->where("email", "=", $row["email"])->update($insertInfo);
                        }

                        else {
                            DB::table('drivers')->insert($insertInfo);
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

    public function logout(Request $request){
        Auth()->logout();

        // Return message
        return response()
            ->json(['message' => 'Successfully logged out']);
    }
}
