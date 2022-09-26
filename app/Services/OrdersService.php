<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Stores;
use App\Models\Products;
use App\Models\OrderUser;
use App\Mail\CommandeMail;
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
        $detailPaiment = DetailPaimentUser::where(['uid',$user->id])->whereNull('paid_at')->first();
        if (!isset($detailPaiment)) {
            return DetailPaimentUser::create([
                'uid' => $user->id,
                'type' => 'user'
            ]);
        }

        return $detailPaiment;
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

    private function updateDetailPaimentStore($detailPaiment){
        $grandTotal = 0 ;
        
        foreach ($detailPaiment->orderStore as $orderStore) {
            $grandTotal += $orderStore['total'] ;
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

        return [
            'data' => [
                'detailPaiment' => DetailPaimentUser::where('uid',$user->id)->first(),
                'orderUser' => OrderUser::where('detail_id',$detailPaiment->id)->where('product_id',$request->product_id)->first(),
            ],
            'status' => 200
        ];
    }

    /**
     * request = [store_id1,store_id2]
     */
    public function createOrderStore($request){
        $detailForUser = DetailPaimentUser::find($request->detail_id);
        
        //copie detail paiment
        foreach ($request->allStore as $store_id) {
            $store = Stores::find($store_id);
            $userStore = User::find($store->uid);
            $detailForStore = DetailPaimentUser::create([
                'uid' => $store->uid,
                'type' => 'store',
                'user_owner' => Auth::id()
                // autreDetails
            ]);
            $orderUser = $detailForUser->orderUser()->where('store_id',$store_id)->get(['product_id','quantity','total','store_id'])->toArray();
            $detailForStore->orderStore()->createMany($orderUser);
            
            // update total detail paiment
            $this->updateDetailPaimentStore($detailForStore);

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

    public function getAllOrderInMyStore()
    {
        return DetailPaimentUser::with('userOwner:id,first_name')->where(['type'=>'store','uid' => $user->id])->get();
    }

    public function viewOneOrder($request)
    {
        return DetailPaimentUser::with(['userOwner:id,first_name','orderStore.product'])->where('id',$request->id)->get();
    }

    public function getMyDetailPaimentUser($request,$user)
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
                'detail' => $detailPaiment,
                'count' => $detailPaiment->orderUser->count(),
            ],
            'status' => 200
        ];
    }

    public function searchOrderInMyStore($request,$user)
    {
        // $store = Stores::where('uid',Auth::id())->first();
        $data = DetailPaimentUser::WhereHas('userOwner',function ($q) use($request){
            $q->where('first_name','LIKE','%'.$request->search.'%');
        })
        // ->orWhere('order_to','LIKE','%'.$request->search.'%') a etudier
        ->orWhere('type_receive','LIKE','%'.$request->search.'%')
        // ->orWhereDay('date_time',$request->search)
        // ->orWhereMonth('date_time',$request->search)
        // ->orWhereYear('date_time',$request->search)
        ->orWhere('id',$request->search)
        ->with('userOwner:id,first_name')->where('uid',$user->id)->get();

        // $data = $data->filter(function ($item) use ($store) {
        //     return $item->store_id == $store->id ; 
        // });

        if (isset($request->type) && $request->type == 'open') {
            $data = $data->filter(function ($item) {
                return $item->display_at == null ;
            });
        }
        if (isset($request->type) && $request->type == 'valide') {
            $data = $data->filter(function ($item) {
                return $item->display_at != null ;
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
