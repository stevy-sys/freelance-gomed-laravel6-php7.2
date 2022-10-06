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
use App\Models\Stores;
use App\Models\User;
use Validator;
use DB;
class StoresController extends Controller
{
    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'name' => 'required',
            'mobile' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'address' => 'required',
            'descriptions' => 'required',
            'cover' => 'required',
            'commission' => 'required',
            'open_time' => 'required',
            'close_time' => 'required',
            // 'cid' => 'required',
            'zipcode' => 'required',
            'status'=>'required',
            'countrie_id' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }

        $data = Stores::create($request->all());
        if (is_null($data)) {
            $response = [
            'data'=>$data,
            'message' => 'error',
            'status' => 500,
        ];
        return response()->json($response, 200);
        }
        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
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

        $data = Stores::find($request->id);

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

    public function updateStatus(Request $request){
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
        $data = Stores::find($request->id)->update($request->only('status'));

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
        $data = Stores::find($request->id)->update($request->all());

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
        $data = Stores::find($request->id);
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
        $data = Stores::all();
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

    public function getStoresData(Request $request){
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
        $ids = explode(',',$request->id);
        $data = Stores::WhereIn('id',$ids)->get();

        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getStores(Request $request){
        $data = DB::table('store')
                ->select('store.id as id','store.uid as uid','store.name as name','store.mobile as mobile','store.lat as lat','store.lng as lng','store.verified as verified',
                'store.address as address','store.descriptions as descriptions','store.images as images','store.cover as cover','store.commission as commission','store.open_time as open_time',
                'store.close_time as close_time','store.isClosed as isClosed','store.certificate_url as certificate_url','store.certificate_type as certificate_type','store.rating as rating',
                'store.total_rating as total_rating','store.cid as cid','store.zipcode as zipcode','store.extra_field as extra_field','store.status as status','users.first_name as first_name',
                'users.last_name as last_name','cities.name as city_name')
                ->join('cities','store.cid','cities.id')
                ->join('users','store.uid','users.id')
                ->get();
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
                            'uid' => $row['uid'],
                            'name' => $row['name'],
                            'mobile' => $row['mobile'],
                            'lat' => $row['lat'],
                            'lng' => $row['lng'],
                            'verified' => $row['verified'],
                            'address' => $row['address'],
                            'descriptions' => $row['descriptions'],
                            'images' => $row['images'],
                            'cover' => $row['cover'],
                            'status' => $row['status'],
                            'commission' => $row['commission'],
                            'open_time' => $row['open_time'],
                            'close_time' => $row['close_time'],
                            'isClosed' => $row['isClosed'],
                            'certificate_url' => $row['certificate_url'],
                            'certificate_type' => $row['certificate_type'],
                            'rating' => $row['rating'],
                            'total_rating' => $row['total_rating'],
                            'cid' => $row['cid'],
                        );
                        $checkLead  =  Stores::where("id", "=", $row["id"])->first();
                        if (!is_null($checkLead)) {
                            DB::table('store')->where("id", "=", $row["id"])->update($insertInfo);
                        }
                        else {
                            DB::table('store')->insert($insertInfo);
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
}