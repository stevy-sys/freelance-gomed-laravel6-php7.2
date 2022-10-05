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

use DB;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Orders;
use App\Models\Stores;
use App\Models\Drivers;
use App\Models\General;
use App\Models\Products;
use App\Mail\CommandeMail;
use App\Models\Complaints;
use Illuminate\Http\Request;
use App\Jobs\RappelOrderStore;
use App\Services\OrdersService;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Models\DetailPaimentUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Bus\Dispatcher;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class OrdersController extends Controller
{
    public $service ;
    
    public function __construct() {
        $this->service = new OrdersService();
    }

    public function makeOrder(Request $request){
        try {
            $response = $this->service->makeOrder($request,Auth::user());
            return response()->json($response,$response['status']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()],500);
        }
    }

    public function verifStockOrder(Request $request){
        try {
            $response = $this->service->verifIfOutOfStock($request,Auth::user());
            return response()->json($response,$response['status']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()],500);
        }
    }

    public function getOrderDetailUser(Request $request)
    {
        try {
            $response = $this->service->getOrderDetailUser($request);
            return response()->json($response,$response['status']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()],500);
        }
    }

    public function allOrderCompletedUser()
    {
        try {
            $response = $this->service->allOrderCompletedUser(Auth::user());
            return response()->json($response,$response['status']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()],500);
        }
    }

    public function getMyDetailPaimentUser(){
        try {
            $response = $this->service->getMyDetailPaimentUser(Auth::user());
            return response()->json($response,$response['status']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()],500);
        }
    }

    public function createOrderStore(Request $request){
        try {
            $response = $this->service->createOrderStore($request);
            return response()->json($response,$response['status']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()],500);
        }
    }

    public function searchOrderInMyStore(Request $request){
        try {
            $response = $this->service->searchOrderInMyStore($request,Auth::user());
            return response()->json($response,$response['status']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()],500);
        }
    }

    public function getAllORderInMMyStorev2() {
        try {
            $response = $this->service->getAllOrderInMyStore(Auth::user());
            return response()->json($response,$response['status']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()],500);
        }
    }

    public function viewDetailPaiment(Request $request) {
        try {
            $response = $this->service->viewDetailPaiment($request);
            return response()->json($response,$response['status']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()],500);
        }
    }

    // ////////////////////////////////////////////////////////

    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'store_id' => 'required',
            'date_time' => 'required',
            'paid_method' => 'required',
            'order_to' => 'required',
            'orders' => 'required',
            // 'notes' => 'required',
            // 'total' => 'required',
            // 'tax' => 'required',
            // 'grand_total' => 'required',
            // 'discount' => 'required',
            // 'delivery_charge' => 'required',
            // 'extra' => 'required',
            // 'pay_key' => 'required',
            // 'status' => 'required',
            // 'payStatus' => 'required',
            // 'type_receive' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }

        $allStore = explode(",",$request->store_id);
        $allProduct = collect(json_decode((string)$request->orders,true));
        $orderRetour = [] ;
        if (count($allStore) > 1) {
            foreach ($allStore as $store_id) {
                $products = $allProduct->where('store_id',$store_id)->all();
                $data = $request->all();
                $data['store_id'] = $store_id;
                $data['total'] = 0 ;
                $data['orders'] = json_encode(array_values($products));
                foreach ($products as $product) {
                    $childTotal = $product['original_price'] * $product['quantity'];
                    $data['total'] += $childTotal ;
                }
                $data['duty_free'] = isset($request->tax) ? ($data['total']) * (($request->tax/100)+ 1) : 0 ;
                $data['grand_total'] = $data['total'] + $request->delivery_charge ;
                $order = Orders::create($data);
                $order->orders = json_decode(json_encode($order->orders));
                $orderRetour[] = $order ;
                $store = Stores::find($store_id);
                $userStore = User::find($store->uid);
                $jobs = (new RappelOrderStore($userStore,$order))->delay(now()->addMinutes(1));
                $id = app(Dispatcher::class)->dispatch($jobs);
                $order->update(['queue_id' => $id]);
    
                // $order->orders = json_encode($order->orders);
            }
        }else{
            $data = $request->all() ;
            $data['duty_free'] = $request->tax != 0 ? ($data['total']) * (($request->tax/100)+ 1) : 0 ;
            $order = Orders::create($data);
            $order->orders = json_decode(json_encode($order->orders));
            $orderRetour[] = $order ;
            $store = Stores::find($request->store_id);
            $userStore = User::find($store->uid);
            $jobs = (new RappelOrderStore($userStore,$order))->delay(now()->addMinutes(1));
            $id = app(Dispatcher::class)->dispatch($jobs);
            $order->update(['queue_id' => $id]);
        }
        // $orderCreate = [] ;
        // foreach ($allStore as $store_id) {
        //     foreach ($allProduct as $product) {
        //         if ($product->store_id == $store_id) {
        //             $data = $request->all();
        //             $data['store_id'] = $store_id ;
        //         }
        //     }
        //     $store = Store::find($product->store_id);

        // }

        // $order = Orders::create($request->all());
        

        // if (is_null($order)) {
        //     $response = [
        //         'data'=>$order,
        //         'message' => 'error',
        //         'status' => 500,
        //     ];
        //     return response()->json($response, 200);
        // }
        if($request && $request->wallet_used == 1){
            $redeemer = User::where('id',$request->uid)->first();
            $redeemer->withdraw($request->wallet_price);
        }



        // $order->orders = json_decode($order->orders);
        // $store = Stores::find($request->store_id);
        // $userStore = User::find($store->uid);
        // $jobs = (new RappelOrderStore($userStore,$order))->delay(now()->addMinutes(3));
        // $id = app(Dispatcher::class)->dispatch($jobs);
        // $order->update(['queue_id' => $id]);

        // $order->orders = json_encode($order->orders);
        $response = [
            'data'=> $orderRetour,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

   
    public function getAllOrderInMyStore(Request $request){
        $all = Orders::whereHas('store',function ($q){
            $q->where('uid',Auth::id());
        })->with('user:id,first_name')->get(['id','uid','orders','date_time','grand_total','order_to','created_at','display_at','type_receive']);
        $open = Orders::whereHas('store',function ($q){
            $q->where('uid',Auth::id());
        })->with('user:id,first_name')->whereNull('display_at')->get(['id','uid','orders','date_time','grand_total','order_to','created_at','display_at','type_receive']);
        $valide = Orders::whereHas('store',function ($q){
            $q->where('uid',Auth::id());
        })->with('user:id,first_name')->whereNotNull('display_at')->get(['id','uid','orders','date_time','grand_total','order_to','created_at','display_at','type_receive']);
        $data['all'] = $all;
        $data['open'] = $open;
        $data['valide'] = $valide;
        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function actionOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }

        $order = DetailPaimentUser::find($request->id);
        if ($request->status == 'accepted') {
            $order->update(['status' => 'valide']);
        }

        if ($request->status == 'refuse') {
            $order->update(['status' => 'refuse']);
        }

        $jobs = DB::table('jobs')->whereId($order->queue_id);
        if (isset($jobs)) {
            $jobs->delete();
        }
        
        $response = [
            'data'=>$order,
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

        $data = Orders::find($request->id);

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

    public function getByIdFromStore(Request $request){
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

        $order = Orders::with('user')->find($request->id);

        if (is_null($order)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }

        $response = [
            'data'=>$order,
            'user'=>User::find($order->uid),
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getByIdFromDriver(Request $request){
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

        $data = Orders::find($request->id);

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
            'user'=>User::find($data->uid),
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function updateStatusStore(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'notes' => 'required',
            'status' => 'required',
            'order_status' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }
        $data = Orders::find($request->id)->update($request->all());

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
        $data = Orders::find($request->id)->update($request->all());

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
        $data = Orders::find($request->id);
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

    public function getByUid(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }
        $data = Orders::where('uid',$request->id)->limit($request->limit)->orderBy('id','desc')->get();
        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);

    }

    public function searchWithId(Request $request){
        $str = "";
        $uid = "";
        if ($request->has('id') && $request->has('uid')) {
            $str = $request->id;
            $uid = $request->uid;
        }

        $products = Orders::where('id', 'like', '%'.$str.'%')->where('uid',$uid)->orderBy('id','desc')->limit(10)->get();
        $response = [
            'data'=>$products,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getByStoreForApps(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }

        $data = Orders::whereRaw('FIND_IN_SET("'.$request->id.'",store_id)')->limit($request->limit)->orderBy('id','desc')->get();

        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    // public function getOrderInMyStore(){
    //     $response = Orders::whereHas('store',function ($query){
    //         $query->whereHas('user',function ($q){
    //             $q->where('id',Auth::id());
    //         });
    //     })->with('user:id,first_name')->get(['id','uid','date_time','grand_total','order_to']);
    //     return response()->json($response, 200);
    // }

    public function getByDriverIdForApp(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }

        $data = Orders::whereRaw('FIND_IN_SET("'.$request->id.'",driver_id)')->limit($request->limit)->orderBy('id','desc')->get();

        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }
    public function getByOrderId(Request $request){
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
        $data = Orders::where('id',$request->id)->first();
        $driverInfo = null;
        $storeInfo = null;
        if($data && $data->driver_id != null){
            $ids = explode(',',$data->driver_id);
            $driverInfo = Drivers::WhereIn('id',$ids)->get();
        }

        if($data && $data->store_id != null){
            $ids = explode(',',$data->store_id);
            $storeInfo = Stores::WhereIn('uid',$ids)->get();
            foreach($storeInfo as $loop){
                $info = User::select('email')->where('id',$loop->uid)->first();
                $loop->email = $info->email;
            }
        }

        $response = [
            'data'=>$data,
            'driverInfo'=>$driverInfo,
            'storeInfo'=>$storeInfo,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getAll(){
        $data = DB::table('orders')
                ->select('orders.*','users.first_name as first_name','users.last_name as last_name')
                ->join('users','orders.uid','users.id')
                ->orderBy('orders.id','desc')
                ->get();
        foreach($data as $loop){
            $ids = explode(',',$loop->store_id);
            $store = Stores::select('name')->WhereIn('id',$ids)->get();
            $loop->storeInfo = $store;
        }
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
                            'uid' => $row['uid'],
                            'store_id' => $row['store_id'],
                            'date_time' => $row['date_time'],
                            'paid_method' => $row['paid_method'],
                            'order_to' => $row['order_to'],
                            'orders' => $row['orders'],
                            'notes' => $row['notes'],
                            'address' => $row['address'],
                            'driver_id' => $row['driver_id'],
                            'assignee' => $row['assignee'],
                            'total' => $row['total'],
                            'tax' => $row['tax'],
                            'grand_total' => $row['grand_total'],
                            'delivery_charge' => $row['delivery_charge'],
                            'coupon_code' => $row['coupon_code'],
                            'discount' => $row['discount'],
                            'extra' => $row['extra'],
                            'pay_key' => $row['pay_key'],
                            'status' => $row['status'],
                        );
                        $checkLead  =  Orders::where("id", "=", $row["id"])->first();
                        if (!is_null($checkLead)) {
                            DB::table('orders')->where("id", "=", $row["id"])->update($insertInfo);
                        }
                        else {
                            DB::table('orders')->insert($insertInfo);
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

    public function sendMailForOrders(Request $request){
        $validator = Validator::make($request->all(), [
            'id'     => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }

        // var_dump($request->id);
        $data = DB::table('orders')
        ->select('orders.*','users.first_name as user_first_name','users.last_name as user_last_name','users.cover as user_cover','users.fcm_token as user_fcm_token','users.mobile as user_mobile','users.email as user_email')
        ->join('users', 'orders.uid', '=', 'users.id')
        ->where('orders.id',$request->id)
        ->first();

        $general = General::first();
        $addres ='';
        if(isset($data->order_to) && $data->order_to  == 'home'){
            $compressed = json_decode($data->address);
            if (isset($compressed)) {
                $addres = $compressed->house .' '.$compressed->landmark .' '.$compressed->address .' '.$compressed->pincode;
            }
        }
        $data->orders = json_decode($data->orders);
        $response = [
            'data'=>$data,
            'email'=>$general->email,
            'delivery'=>$addres
        ];

        $mail = $data->user_email;
        $username = $data->user_first_name;
        $subject = 'Order Status';
        Mail::to($mail)->send(new CommandeMail($response,$subject,Auth::user())); 

        // $response = Mail::send('mails/orders', $response , function($message) use($mail,$username,$subject,$general){
        //     $message->to($mail, $username)->subject($subject);
        //     $message->from(env('MAIL_USERNAME'),$general->name);
        // });
       
        $response = [
            'success' => $response,
            'message' => 'success',
            'status' => 200
        ];
        return $response;
    }

    public function printInvoice(Request $request){
        $validator = Validator::make($request->all(), [
            'id'     => 'required',
            'token'  => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }

        try {
            $data = DB::table('orders')
            ->select('orders.*','users.first_name as user_first_name','users.last_name as user_last_name','users.cover as user_cover','users.fcm_token as user_fcm_token','users.mobile as user_mobile','users.email as user_email')
            ->join('users', 'orders.uid', '=', 'users.id')
            ->where('orders.id',$request->id)
            ->first();
            $general = General::first();
            $addres ='';
            if($data->order_to =='home'){
                $compressed = json_decode($data->address);
                $addres = $compressed->house .' '.$compressed->landmark .' '.$compressed->address .' '.$compressed->pincode;
            }
            $data->orders = json_decode($data->orders);
            $response = [
                'data'=>$data,
                'email'=>$general->email,
                'delivery'=>$addres
            ];

            $mail = $data->user_email;
            $username = $data->user_first_name;
            return view('printinvoice',$response);
        } catch (TokenExpiredException $e) {

            return response()->json(['error' => 'Session Expired.', 'status_code' => 401], 401);

        } catch (TokenInvalidException $e) {

            return response()->json(['error' => 'Token invalid.', 'status_code' => 401], 401);

        } catch (JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 401);

        }
    }

    public function printStoreInvoice(Request $request){
        $validator = Validator::make($request->all(), [
            'id'     => 'required',
            'token'  => 'required',
            'storeId'  => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }

        try {
            $data = DB::table('orders')
            ->select('orders.*','users.first_name as user_first_name','users.last_name as user_last_name','users.cover as user_cover','users.fcm_token as user_fcm_token','users.mobile as user_mobile','users.email as user_email')
            ->join('users', 'orders.uid', '=', 'users.id')
            ->where('orders.id',$request->id)
            ->first();
            $general = General::first();
            $addres ='';
            if($data->order_to =='home'){
                $compressed = json_decode($data->address);
                $addres = $compressed->house .' '.$compressed->landmark .' '.$compressed->address .' '.$compressed->pincode;
            }
            $data->orders = json_decode($data->orders);
            $orderArray = array();
            foreach($data->orders as $loop){
                if($loop->store_id == $request->storeId){
                    array_push($orderArray,$loop);
                }
            }
            $ids = explode(',',$data->store_id);
            $totalStore = count($ids);

            $dummyTotal = 0;
            foreach($orderArray as $element){
                $dummyPrice = 0;
                if ($element->variations && $element->variations !== '' && gettype($element->variations) == 'string') {
                    $element->variations = json_decode($element->variations);

                    if (!$element->variant || $element->variant == null) {
                        $element->variant = 0;
                    }
                }

                if ($element && $element->discount == 0) {
                    if ($element->size == 1) {
                        if ($element->variations[0]->items[$element->variant]->discount && $element->variations[0]->items[$element->variant]->discount !== 0) {
                            $dummyPrice = $dummyPrice + ((float)($element->variations[0]->items[$element->variant]->discount) * $element->quantiy);
                        } else {
                            $dummyPrice = $dummyPrice + ((float)($element->variations[0]->items[$element->variant]->price) * $element->quantiy);
                        }
                    } else {
                            $dummyPrice = $dummyPrice + ((float)($element->original_price) * $element->quantiy);
                    }
                } else {
                    if ($element->size == 1) {
                        if ($element->variations[0]->items[$element->variant]->discount && $element->variations[0]->items[$element->variant]->discount !== 0) {
                            $dummyPrice = $dummyPrice + ((float)($element->variations[0]->items[$element->variant]->discount) * $element->quantiy);
                        } else {
                            $dummyPrice = $dummyPrice + ((float)($element->variations[0]->items[$element->variant]->price) * $element->quantiy);
                        }
                    } else {
                        $dummyPrice = $dummyPrice + ((float)($element->sell_price) * $element->quantiy);
                    }
                }
                $dummyTotal = $dummyTotal + $dummyPrice;
            }
            $orderDiscount = 0;
            $orderWalletDiscount = 0;
            $orderDeliveryCharge = 0;
            $orderTaxCharge = 0;
            $grandTotal = 0;
            if($data->discount > 0){
                $orderDiscount = (float)($data->discount / $totalStore);
            }
            if ($data->wallet_used == 1) {
                $orderWalletDiscount = (float)($data->wallet_price / $totalStore);
            }

            $storeName = Stores::select('name')->where('uid',$request->storeId)->first();

            try {
                $storeExtra = array();
                $extra = json_decode($data->extra, $associative=true, $depth=512, JSON_THROW_ON_ERROR);
                foreach($extra as $loop){
                    if($loop['store_id'] == $request->storeId){
                        array_push($storeExtra,$loop);
                    }
                }
                // $storeExtra = $storeExtra[0];
                if ($extra && count($storeExtra) && $data->order_to == 'home') {
                    $storeData = $storeExtra[0];
                    if ($storeData['shipping'] == 'km') {
                        $deliveryCharge = (float)($storeData['distance']) * (float)($storeData['shippingPrice']);
                        $orderDeliveryCharge = (float)$deliveryCharge;
                        $orderTaxCharge = (float)($storeData['tax']);
                    } else {
                        $orderDeliveryCharge = ((float)($storeData['shippingPrice']) / $totalStore);
                        $orderTaxCharge = (float)($storeData['tax']);
                    }
                } else {
                    $storeData = $storeExtra[0];
                    $orderTaxCharge = (float)($storeData['tax']);
                }
            } catch (Exception $e) {
            }
            $total = (float)($dummyTotal) + (float)($orderTaxCharge) + (float)($orderDeliveryCharge);
            $discount = (float)($orderDiscount) + (float)($orderWalletDiscount);
            $grandTotal = $total - $discount;
            $grandTotal = $grandTotal > 0 ? $grandTotal : 0;
            $response = [
                'data'=>$data,
                'email'=>$general->email,
                'delivery'=>$addres,
                'orderArray'=>$orderArray,
                'total'=>$dummyTotal,
                'orderDiscount'=>$orderDiscount,
                'orderWalletDiscount'=>$orderWalletDiscount,
                'orderDeliveryCharge'=>$orderDeliveryCharge,
                'orderTaxCharge'=>$orderTaxCharge,
                'grandTotal'=>$grandTotal,
                'storeName' => $storeName
            ];

            $mail = $data->user_email;
            $username = $data->user_first_name;
            return view('printstoreinvoice',$response);
        } catch (TokenExpiredException $e) {

            return response()->json(['error' => 'Session Expired.', 'status_code' => 401], 401);

        } catch (TokenInvalidException $e) {

            return response()->json(['error' => 'Token invalid.', 'status_code' => 401], 401);

        } catch (JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 401);

        }
    }

    public function getStoreStatsDataWithDates(Request $request){
        $validator = Validator::make($request->all(), [
            'id'     => 'required',
            'from'     => 'required',
            'to'     => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }
        $from = date($request->from);
        $to = date($request->to);
        $data = Orders::whereRaw('FIND_IN_SET("'.$request->id.'",store_id)')->whereBetween('date_time',[$from, $to])->orderBy('id','desc')->get();
        $commission = DB::table('store')->select('commission')->where('uid',$request->id)->first();
        $response = [
            'data'=>$data,
            'commission'=>$commission,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getAdminDashboard(Request $request){
        $now = Carbon::now();



        $todatData = Orders::select(\DB::raw("COUNT(*) as count"), \DB::raw("DATE_FORMAT(date_time,'%h:%m') as day_name"), \DB::raw("DATE_FORMAT(date_time,'%h:%m') as day"))
        ->whereDate('date_time',Carbon::today())
        ->groupBy('day_name','day')
        ->orderBy('day')
        ->get();

        $weekData = Orders::select(\DB::raw("COUNT(*) as count"), \DB::raw("DATE(date_time) as day_name"), \DB::raw("DATE(date_time) as day"))
            ->whereBetween('date_time', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->groupBy('day_name','day')
            ->orderBy('day')
            ->get();

        $monthData = Orders::select(\DB::raw("COUNT(*) as count"), \DB::raw("DATE(date_time) as day_name"), \DB::raw("DATE(date_time) as day"))
            ->whereMonth('date_time', Carbon::now()->month)
            ->groupBy('day_name','day')
            ->orderBy('day')
            ->get();
        $monthResponse = [];
        $weekResponse =[];
        $todayResponse = [];

        foreach($todatData as $row) {
            $todayResponse['label'][] = $row->day_name;
            $todayResponse['data'][] = (int) $row->count;
        }
        foreach($weekData as $row) {
            $weekResponse['label'][] = $row->day_name;
            $weekResponse['data'][] = (int) $row->count;
        }

        foreach($monthData as $row) {
            $monthResponse['label'][] = $row->day_name;
            $monthResponse['data'][] = (int) $row->count;
        }

        $todayDate  = $now->format('d F');

        $weekStartDate = $now->startOfWeek()->format('d');
        $weekEndDate = $now->endOfWeek()->format('d F');

        $monthStartDate = $now->startOfMonth()->format('d');
        $monthEndDate = $now->endOfMonth()->format('d F');

        $recentOrders = DB::table('orders')
                ->select('orders.*','users.first_name as first_name','users.last_name as last_name')
                ->join('users','orders.uid','users.id')
                ->limit(10)
                ->orderBy('orders.id','desc')
                ->get();
        foreach($recentOrders as $loop){
            $ids = explode(',',$loop->store_id);
            $store = Stores::select('name')->WhereIn('id',$ids)->get();
            $loop->storeInfo = $store;
        }

        $complaints = Complaints::whereMonth('created_at', Carbon::now()->month)->get();

        foreach($complaints as $loop){
            $user = User::select('email','first_name','last_name','cover')->where('id',$loop->uid)->first();
            $loop->userInfo = $user;
            if($loop && $loop->store_id && $loop->store_id !=null){
                $store = Stores::select('name','cover')->where('uid',$loop->store_id)->first();
                $storeUser = User::select('email','cover')->where('id',$loop->store_id)->first();
                $loop->storeInfo = $store;
                $loop->storeUiserInfo = $storeUser;
            }

            if($loop && $loop->driver_id && $loop->driver_id !=null){
                $driver = Drivers::select('email','first_name','last_name','cover')->where('id',$loop->driver_id)->first();
                $loop->driverInfo = $driver;
            }
            if($loop && $loop->product_id && $loop->product_id !=null){
                $product = Products::select('name','cover')->where('id',$loop->product_id)->first();
                $loop->productInfo = $product;
            }

        }
        $data = [
            'today' => $todayResponse,
            'week' => $weekResponse,
            'month' => $monthResponse,
            'todayLabel' => $todayDate,
            'weekLabel' => $weekStartDate . '-'. $weekEndDate,
            'monthLabel' => $monthStartDate.'-'.$monthEndDate,
            'complaints'=>$complaints,
            'users' =>User::where('type','user')->count(),
            'stores'=>User::where('type','store')->count(),
            'orders'=>Orders::count(),
            'recentOrders'=>$recentOrders,
            'recentUsers' =>User::where('type','user')->limit(10)->orderBy('id','desc')->get(),
            'products'=>Products::count()
        ];

        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getStoreStatsData(Request $request){
        $validator = Validator::make($request->all(), [
            'id'     => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }
        $now = Carbon::now();

        $todayData = Orders::whereRaw('FIND_IN_SET("'.$request->id.'",store_id)')->whereDate('date_time',Carbon::today())->get();
        $weekData = Orders::whereRaw('FIND_IN_SET("'.$request->id.'",store_id)')->whereBetween('date_time', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->get();
        $monthData = Orders::whereRaw('FIND_IN_SET("'.$request->id.'",store_id)')->whereMonth('date_time', Carbon::now()->month)->get();
        $complaints = Complaints::where(['store_id'=>$request->id,'status'=>0])->whereMonth('created_at', Carbon::now()->month)->get();
        $todayDate  = $now->format('d F');
        $todayResponse = [
            'data'=>$todayData,
            'label'=>$todayDate
        ];

        $weekStartDate = $now->startOfWeek()->format('d');
        $weekEndDate = $now->endOfWeek()->format('d F');

        $weekResponse = [
            'label' => $weekStartDate . '-'. $weekEndDate,
            'data' => $weekData
        ];

        $monthStartDate = $now->startOfMonth()->format('d');
        $monthEndDate = $now->endOfMonth()->format('d F');

        $monthResponse =  [
            'label'=>$monthStartDate.'-'.$monthEndDate,
            'data'=>$monthData,
        ];
        $data = [
            'today' => $todayResponse,
            'week' => $weekResponse,
            'month' => $monthResponse,
            'complaints'=>$complaints
        ];

        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getByIdAdmin(Request $request){
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

        $data = Orders::find($request->id);

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
            'user'=>User::find($data->uid),
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }
}
