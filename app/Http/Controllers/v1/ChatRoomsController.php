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
use App\Models\ChatRooms;
use Validator;
use DB;
class ChatRoomsController extends Controller
{
    public function createChatRooms(Request $request){
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'participants' => 'required',
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

        $data = ChatRooms::create($request->all());
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

    public function getChatRooms(Request $request){
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'participants' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }

        \DB::enableQueryLog();

        $matchThese = ['uid' => $request->uid, 'participants' => $request->participants];
        $matchTheseToo = ['uid' => $request->participants, 'participants' => $request->uid];
        $data = ChatRooms::where($matchThese)->first();
        $data2 = ChatRooms::where($matchTheseToo)->first();

        $query = \DB::getQueryLog();
        if (is_null($data) && is_null($data2)) {
            $response = [
                'data' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }

        $response = [
            'data'=>$data,
            'data2'=>$data2,
            'success' => true,
            'status' => 200,
            'query' => $query
        ];
        return response()->json($response, 200);
    }


    public function getChatListBUid(Request $request){
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

        $data = DB::table('chat_room')
        ->select('a.first_name as sender_first_name','a.id as uid','b.first_name as receiver_name','b.id as participants','a.last_name as sender_last_name','a.cover as sender_cover','b.last_name as receiver_last_name','b.cover as receiver_cover','a.type as sender_type','b.type as receiver_type')
        ->join('users as a', 'chat_room.uid', '=', 'a.id')
        ->join('users as b', 'chat_room.participants', '=', 'b.id')
        ->where('chat_room.uid',$request->id)
        ->orWhere('chat_room.participants',$request->id)
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
                            'participants' => $row['participants'],
                            'status' => $row['status'],
                        );
                        $checkLead  =  ChatRooms::where("id", "=", $row["id"])->first();
                        if (!is_null($checkLead)) {
                            DB::table('chat_room')->where("id", "=", $row["id"])->update($insertInfo);
                        }
                        else {
                            DB::table('chat_room')->insert($insertInfo);
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
