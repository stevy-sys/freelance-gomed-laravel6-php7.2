<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Stores;
use App\Models\Products;
use App\Models\OrderUser;
use App\Mail\CommandeMail;
use App\Models\OrderStore;
use App\Jobs\RappelOrderStore;
use App\Models\DetailPaimentUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Bus\Dispatcher;

class OrdersService {
    
    private function verifOrder($request,$user,$detailPaiment)
    {
        return OrderUser::where('detail_id',$detailPaiment->id)->where('product_id',$request->product_id)->first();
    }

    private function createDetailPaiment($user)
    {
        $detailPaiment = DetailPaimentUser::where('uid',$user->id)->whereNull('paid_at')->first();
        if (!isset($detailPaiment)) {
            return DetailPaimentUser::create([
                'uid' => $user->id,
                'type' => 'user'
            ]);
        }

        return $detailPaiment;
    }

    public function getOrderDetailUser($request)
    {
        $detailPaiment = DetailPaimentUser::whereHas('orderStore',function ($query) use($request) {
          
        })->with('orderStore.product')->where(['type'=>'store','user_owner' => Auth::id()])->get();
        // $orderStore = OrderStore::where('detail_id',$detailPaiment->id)->get();

        return [
            'data' => [
                'detailPaiment' => $detailPaiment,
                // 'orderUser' => $orderStore,
            ],
            'status' => 200
        ];
    }

    private function updateDetailPaiment($detailPaiment){
        $grandTotal =  0 ;

        foreach ($detailPaiment->orderUser as $orderUser) {
            $grandTotal += $orderUser->total ;
        }

        $detailPaiment->update([
            'grand_total' => $grandTotal
        ]);
    }

    private function updateDetailPaimentStore($detailPaiment,$delivery=null){
        $grandTotal = 0 ;
        
        foreach ($detailPaiment->orderStore as $orderStore) {
            $grandTotal = $grandTotal + $orderStore['total'] + $delivery ;
        }
        
        $detailPaiment->update([
            'grand_total' => $grandTotal
        ]);
    }

    private function createOrder($request,$user,$detailPaiment) {
        $product = Products::find($request->product_id);
        $orderuser = $detailPaiment->orderUser()->create([
            'product_id' => $product->id,
            'store_id' => $product->store_id,
            'quantity' => 1,
            'total' => $product->original_price
        ]);
        $this->updateDetailPaiment($detailPaiment);
        return $orderuser ;
    }


    private function updateOrder($orderUser,$action,$detailPaiment) {
        if ($action == 'add') {
            $orderUser->quantity++ ;
            $orderUser->total = $orderUser->product->original_price * $orderUser->quantity ;
            $orderUser->save();
        }else{
            if ($orderUser->quantity == 1) {
                // $orderUser->quantity = 0 ;
                $orderUser->delete();
            }else{
                $orderUser->quantity-- ;
                $orderUser->total = $orderUser->product->original_price * $orderUser->quantity;
                $orderUser->save();
            }
        }
        $this->updateDetailPaiment($detailPaiment);
    }

    private function sendMailOrder($type,$detailPaiment,$user,$userInRappel = null){
        if ($type == 'user') {
            // user
            Mail::to($user->email)->send(new CommandeMail($user,$detailPaiment->id)); 
        }else{
            // store
            $jobs = (new RappelOrderStore($user,$detailPaiment->id,$userInRappel))->delay(now()->addMinutes(1));
            $id = app(Dispatcher::class)->dispatch($jobs);
            $detailPaiment->update(['queue_id' => $id]);
        }
    }

    public function makeOrder($request,$user) {
        $detailPaiment = $this->createDetailPaiment($user);
        $orderUser = $this->verifOrder($request,$user,$detailPaiment);
        if (!isset($orderUser)) {
            $this->createOrder($request,$user,$detailPaiment);
        } 
        else {
            $this->updateOrder($orderUser,$request->action,$detailPaiment);
        }
        $detailPaiment = DetailPaimentUser::with('orderUser.product')->find($detailPaiment->id);
        $orderUser = OrderUser::where('detail_id',$detailPaiment->id)->where('product_id',$request->product_id)->first();
        return [
            'data' => [
                'detailPaiment' => $detailPaiment,
                'orderUser' => isset($orderUser) ? $orderUser : 0,
                'count' => $detailPaiment->orderUser->count()
            ],
            'status' => 200
        ];
    }

    /**
     * request = [store_id1,store_id2]
     */
    public function createOrderStore($request){
        $detailForUser = DetailPaimentUser::find($request->detail_id);
        $detailForUser->grand_total = $detailForUser->grand_total + $request->delivery['price'] ;
        $detailForUser->delivery_option = $request->delivery['option'];
        $detailForUser->type_receive = $request->delivery['type'];
        $detailForUser->save();
        //copie detail paiment
        foreach ($request->allStore as $store_id) {
            $store = Stores::find($store_id);
            $userStore = User::find($store->uid);
            $detailForStore = DetailPaimentUser::create([
                'uid' => $store->uid,
                'type' => 'store',
                'user_owner' => Auth::id(),
                'delivery_option' => $request->delivery['option'],
                'type_receive' => $request->delivery['type'],
            ]);
            $orderUser = $detailForUser->orderUser()->where('store_id',$store_id)->get(['product_id','quantity','total','store_id'])->toArray();
            $detailForStore->orderStore()->createMany($orderUser);
            
            // update total detail paiment
            $this->updateDetailPaimentStore($detailForStore,$request->delivery['price']);

            // send mail
            $this->sendMailOrder('store',$detailForStore,$userStore,Auth::user());
        }

        // user
        $this->sendMailOrder('user',$detailForUser,Auth::user());
        $detailForUser->update(['paid_at' => Carbon::now()]);
        return [
            'data' => $detailForUser,
            'status' => 200
        ];
    }

    public function getAllOrderInMyStore($user)
    {
        $order['open'] = DetailPaimentUser::with('userOwner:id,first_name')->where('status',0)->where(['type'=>'store','uid' => $user->id])->get();
        $order['valide'] = DetailPaimentUser::with('userOwner:id,first_name')->where('status',1)->where(['type'=>'store','uid' => $user->id])->get();
        $order['all'] = DetailPaimentUser::with('userOwner:id,first_name')->where(['type'=>'store','uid' => $user->id])->get();
        return [
            'data' => $order,
            'status' => 200
        ];
    }

    public function viewDetailPaiment($request)
    {
        $order = DetailPaimentUser::with(['orderStore.product','userOwner'])->find($request->id);
        return [
            'data' => $order,
            'status' => 200
        ];
    }

    public function viewOneOrder($request)
    {
        $order = DetailPaimentUser::with(['userOwner:id,first_name','orderStore.product'])->where('id',$request->id)->first();
        return [
            'data' => $order,
            'status' => 200
        ]; 
    }

    public function allOrderCompletedUser($user)
    {
        $order = DetailPaimentUser::with('orderUser.product')->where(['uid'=>$user->id,'type' => 'user'])->whereNotNull('paid_at')->get();
        return [
            'data' => $order,
            'status' => 200
        ]; 
    }

    public function getMyDetailPaimentUser($user)
    {
        $detailPaiment = DetailPaimentUser::where(['uid'=>$user->id,'type' => 'user'])->whereNull('paid_at')->first();
        if (!isset($detailPaiment)) {
            $detailPaiment =  DetailPaimentUser::create([
                'uid' => $user->id,
                'type' => 'user'
            ]);
        }
        return [
            'data' => [
                'detail' => $detailPaiment->load('orderUser.product.store.countrie'),
                'count' => $detailPaiment->orderUser->count(),
            ],
            'status' => 200
        ];
    }

    public function searchOrderInMyStore($request,$user)
    {
        // $store = Stores::where('uid',$user->id)->first();
        $data = DetailPaimentUser::WhereHas('userOwner',function ($q) use($request){
            $q->where('first_name','LIKE','%'.$request->search.'%');
        })
        // ->orWhere('order_to','LIKE','%'.$request->search.'%') a etudier
        ->orWhere('type_receive','LIKE','%'.$request->search.'%')
        ->orWhereDay('created_at',$request->search)
        ->orWhereMonth('created_at',$request->search)
        ->orWhereYear('created_at',$request->search)
        ->orWhere('id',$request->search)
        ->with('userOwner:id,first_name')->where(['type' => 'store'])->get();

        $data = $data->filter(function ($item) use ($user) {
            return $item->uid == $user->id ; 
        });

        if (isset($request->type) && $request->type == 'open') {
            $data = $data->filter(function ($item) {
                return $item->status == 0 ;
            });
        }
        if (isset($request->type) && $request->type == 'valide') {
            $data = $data->filter(function ($item) {
                return $item->status == 1 ;
            });
        }
        $dataTemp = [] ;
        foreach ($data as $data) {
            $dataTemp[] = $data ;
        }
        $response = [
            'data'=> $dataTemp,
            'success' => true,
            'status' => 200,
        ];
        return $response;
    }
}
