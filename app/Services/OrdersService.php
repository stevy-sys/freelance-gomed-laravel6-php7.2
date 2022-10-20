<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Stores;
use App\Mail\Ordonnance;
use App\Models\Countrie;
use App\Models\Products;
use App\Models\OrderUser;
use App\Mail\CommandeMail;
use App\Models\OrderStore;
use App\Jobs\RappelOrderStore;
use App\Models\DetailPaimentUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\DB;

class OrdersService {
    protected $productService ;

    public function __construct() {
        $this->productService = new ProductService;
    }
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
            $grandTotal = $grandTotal + $orderStore['total']  ;
        }
        
        $detailPaiment->update([
            'grand_total' => $grandTotal + $delivery
        ]);
    }

    private function createOrder($request,$user,$detailPaiment) {
        $product = Products::find($request->product_id);
        $orderuser = $detailPaiment->orderUser()->create([
            'product_id' => $product->id,
            'store_id' => $product->store_id,
            'quantity' => 1,
            'total' => $product->offer ? $product->original_price - (($product->offer->rates * $product->original_price)/100) : $product->original_price
        ]);
        $this->updateDetailPaiment($detailPaiment);
        return $orderuser ;
    }


    private function updateOrder($orderUser,$action,$detailPaiment) {
        if ($action == 'add') {
            $orderUser->quantity++ ;
            $orderUser->total = $orderUser->product->offer ? ($orderUser->product->original_price - (($orderUser->product->original_price * $orderUser->product->offer->rates)/100)) * $orderUser->quantity 
                                : 
                                $orderUser->product->original_price * $orderUser->quantity ;
            $orderUser->save();
        }else{
            if ($orderUser->quantity == 1) {
                // $orderUser->quantity = 0 ;
                $orderUser->delete();
            }else{
                $orderUser->quantity-- ;
                $orderUser->total = $orderUser->product->offer ? ($orderUser->product->original_price - (($orderUser->product->original_price * $orderUser->product->offer->rates)/100)) * $orderUser->quantity 
                                    : 
                                    $orderUser->product->original_price * $orderUser->quantity ;
                $orderUser->save();
            }
        }
        $this->updateDetailPaiment($detailPaiment);
    }

    private function sendMailOrder($type,$detailPaiment,$user,$userInRappel = null,$request=null){
        if ($type == 'user') {
            // user
            Mail::to($user->email)->send(new CommandeMail($user,$detailPaiment->id,$request)); 
        }else{
            // store
            $jobs = (new RappelOrderStore($user,$detailPaiment->id,$userInRappel))->delay(now()->addMinutes(1));
            $id = app(Dispatcher::class)->dispatch($jobs);
            $detailPaiment->update(['queue_id' => $id]);
        }
    }

    private function sendMailReponseOrder($user,$action)
    {
        
    }

    public function actionOrder($request)
    {
        $order = DetailPaimentUser::find($request->id);
        $user = User::find($order->user_owner);
        if ($request->status == 'accepted') {
            $order->update(['status' => 'valide']);
            // $this->sendMailReponseOrder($user,'accepted');
        }

        if ($request->status == 'refuse') {
            $order->update(['status' => 'refuse']);
            // $this->sendMailReponseOrder($user,'refuse');
        }

        $jobs = DB::table('jobs')->whereId($order->queue_id);
        if (isset($jobs)) {
            $jobs->delete();
        }

        

        return [
            'data'=>$order,
            'success' => true,
            'status' => 200,
        ];
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
        $detailPaiment = DetailPaimentUser::with('orderUser.product.store.countrie')->find($detailPaiment->id);

        if (isset($request) && $request->myCurrency) {
            $detailPaiment->orderUser = $detailPaiment->orderUser->map(function ($element) use($request){
                $element->product->priceLocale = $this->productService->convertCurrency(
                    $request->myCurrency,
                    $element->product->store->countrie->currency,
                    $element->product->offer ? ($element->product->original_price - ($element->product->original_price * $element->product->offer->rates)/100) : $element->product->original_price
                );
                return $element ;
            });

            $totalLocal = 0 ;
            $orders = $detailPaiment->orderUser;
            foreach ($orders as $or) {
                $totalLocal = $totalLocal + (
                    $or->product->offer ? ($or->product->priceLocale - (($or->product->priceLocale * $or->product->offer->rates)/100)) * $or->quantity : $or->product->priceLocale * $or->quantity) ;
            }
            $detailPaiment->totalLocal = $totalLocal ;
        }
        //

        $orderUser = OrderUser::where('detail_id',$detailPaiment->id)->where('product_id',$request->product_id)->first();


        $countrie= Countrie::where('code_pays',$request->code_country)->first();
        if (!isset($countrie)) {
            $countrie = Countrie::first();
        }
        $products = $this->productService->getProductInStoreViaCountrie($countrie);
        return [
            'data' => [
                'detailPaiment' => $detailPaiment,
                'orderUser' => isset($orderUser) ? $orderUser : 0,
                'count' => $detailPaiment->orderUser->count(),
                'topProduct' => $products
            ],
            'status' => 200
        ];
    }

    public function verifIfOutOfStock($request,$user)
    {
        $detail = $this->createDetailPaiment($user);
        $product = Products::find($request->product_id);
        $stock = $product->quantity->stock ;

        $orderUser = $detail->orderUser()->where('product_id',$request->product_id)->first();
        $stockOrder = $orderUser->quantity ;
        if ($stock <= $stockOrder) {
            return [
                'data' => [
                    'message' => 'out'
                ],
                'status' => 200
            ];
        }else{
            return [
                'data' => [
                    'message' => 'in'
                ],
                'status' => 200
            ];
        }
    }

    private function medicalPrescription($detailPaiment,$userStore)
    {
        $detail = DetailPaimentUser::whereHas('orderStore',function ($q) {
            $q->whereHas('product',function ($q) {
                $q->where('medical_prescription',1);
            });
        })->with('orderStore.product')->where('id',$detailPaiment->id)->first();
        
        $total = 0 ;
        if (isset($detail)) {
            foreach ($detail->orderStore as $orderStore) {
                if ($orderStore->product->medical_prescription == 1) {
                    $total += $orderStore->product->original_price ;
                }
            }
            Mail::to($userStore->email)->send(new Ordonnance($detail,$userStore,$total)); 
        }
    }

    /**
     * request = [store_id1,store_id2]
     */
    public function createOrderStore($request){
        $detailForUser = DetailPaimentUser::find($request->detail_id);
        $detailForUser->grand_total = $detailForUser->grand_total + $request->delivery['price'] ;
        $detailForUser->delivery_option = $request->delivery['option'];
        $detailForUser->type_receive = $request->delivery['type'];
        $detailForUser->payment_id = $request->payement_id;
        $detailForUser->payment_type = $request->type;
        $detailForUser->tva_value = $request->tva;
        $detailForUser->save();

        //copie detail paiment
        foreach ($request->allStore as $store_id) {
            $store = Stores::find($store_id);
            $userStore = User::find($store->uid);
            $detailForStore = DetailPaimentUser::create([
                'uid' => $store->uid,
                'type' => 'store',
                'user_owner' => Auth::id(),
                'status' => 'open',
                'delivery_option' => $request->delivery['option'],
                'type_receive' => $request->delivery['type'],
                'tva_value' => $request->tva,
                'payment_type' => $request->type,
                'payment_id' => $request->payement_id,
            ]);
            $orderUser = $detailForUser->orderUser()->where('store_id',$store_id)->get(['product_id','quantity','total','store_id'])->toArray();
            $detailForStore->orderStore()->createMany($orderUser);
            
            // update total detail paiment
            $this->updateDetailPaimentStore($detailForStore,$request->delivery['price']);

            // send mail for order
            $this->sendMailOrder('store',$detailForStore,$userStore,Auth::user(),null,null);

            //send medical prescription
            $this->medicalPrescription($detailForStore,$userStore);
        }

        // user
        $this->sendMailOrder('user',$detailForUser,Auth::user(),null,$request);
        $detailForUser->update(['paid_at' => Carbon::now()]);
        return [
            'data' => $detailForUser,
            'status' => 200
        ];
    }

    public function getAllOrderInMyStore($user)
    {
        $order['open'] = DetailPaimentUser::with('userOwner:id,first_name')->where('status','open')->where(['type'=>'store','uid' => $user->id])->get();
        $order['valide'] = DetailPaimentUser::with('userOwner:id,first_name')->where('status','valide')->where(['type'=>'store','uid' => $user->id])->get();
        $order['refuse'] = DetailPaimentUser::with('userOwner:id,first_name')->where('status','refuse')->where(['type'=>'store','uid' => $user->id])->get();
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

    public function getDetailPaimentById($request)
    {
        $detailPaiment = DetailPaimentUser::with('orderUser.product.store.countrie')->find($request->id);
        if (isset($request) && $request->myCurrency) {
            $detailPaiment->orderUser = $detailPaiment->orderUser->map(function ($element) use($request){
                $element->product->priceLocale = $this->productService->convertCurrency(
                    $request->myCurrency,
                    $element->product->store->countrie->currency,
                    $element->product->offer ? ($element->product->original_price - ($element->product->original_price * $element->product->offer->rates)/100) : $element->product->original_price
                );
                return $element ;
            });

        }
        $totalLocal = 0 ;
        $ord = $detailPaiment['orderUser'] ;
        foreach ($ord as $or) {
            $totalLocal += ($or->product->priceLocale*$or->quantity) ;
        }
        $detailPaiment->totalLocal = $totalLocal ;

        return [
            'data' => [
                'detail' => $detailPaiment,
                // 'order' => OrderUser::with('product.store.countrie')->where('detail_id',$detailPaiment->id)->get()
            ],
            'status' => 200
        ];
    }

    public function getMyDetailPaimentUser($user,$request=null)
    {
        $detailPaiment = DetailPaimentUser::where(['uid'=>$user->id,'type' => 'user'])->whereNull('paid_at')->first();
        if (!isset($detailPaiment)) {
            $detailPaiment =  DetailPaimentUser::create([
                'uid' => $user->id,
                'type' => 'user'
            ]);
        }
        $order = $detailPaiment->load('orderUser.product.store.countrie');
        if (isset($request) && $request->myCurrency) {
            $order->orderUser = $order->orderUser->map(function ($element) use($request){
                $element->product->priceLocale = $this->productService->convertCurrency(
                    $request->myCurrency,
                    $element->product->store->countrie->currency,
                    $element->product->offer ? ($element->product->original_price - ($element->product->original_price * $element->product->offer->rates)/100) : $element->product->original_price
                );
                return $element ;
            });
        }
       
        $totalLocal = 0 ;
        $ord = $order['orderUser'] ;
        foreach ($ord as $or) {
            $totalLocal += ($or->product->priceLocale*$or->quantity) ;
        }
        $detailPaiment->totalLocal = $totalLocal ;
        return [
            'data' => [
                'detail' => $order,
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
        ->orWhere('delivery_option','LIKE','%'.$request->search.'%') 
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
                return $item->status == 'open' ;
            });
        }
        if (isset($request->type) && $request->type == 'valide') {
            $data = $data->filter(function ($item) {
                return $item->status == 'valide' ;
            });
        }
        if (isset($request->type) && $request->type == 'refuse') {
            $data = $data->filter(function ($item) {
                return $item->status == 'refuse' ;
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
