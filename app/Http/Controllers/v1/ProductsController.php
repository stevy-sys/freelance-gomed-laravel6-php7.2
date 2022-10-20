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
use App\Models\Cities;
use App\Models\Stores;
use App\Models\Banners;
use App\Models\Category;
use App\Models\Products;
use App\Models\Settings;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProductsController extends Controller
{
    public $service ;
    
    public function __construct(Type $var = null) {
        $this->service = new ProductService();
    }

    public function getProductInStoreViaCountrie(Request $request){
       return $this->service->getProductInStoreViaCountrie($request);
    }
    public function save(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'store_id' => 'required',
                'cover' => 'required',
                'name' => 'required',
                // 'images' => 'required',
                'original_price' => 'required',
                'sell_price' => 'required',
                'offer' => 'required',
                'kind' => 'required',
                'cate_id' => 'required',
                'sub_cate_id' => 'required',
                'in_home' => 'required',
                'is_single' => 'required',
                'have_gram' => 'required',
                'gram' => 'required',
                'have_kg' => 'required',
                'kg' => 'required',
                'have_pcs' => 'required',
                'pcs' => 'required',
                'have_liter' => 'required',
                'liter' => 'required',
                'have_ml' => 'required',
                'ml' => 'required',
                'type_of' => 'required',
                'in_offer' => 'required',
                'in_stoke' => 'required',
                'rating' => 'required',
                'total_rating' => 'required',
                // 'variations' => 'required',
                'size' => 'required',
                'medical_prescription' => 'required'
            ]);
            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Validation Error.', $validator->errors(),
                    'status' => 500
                ];
                return response()->json($response, 404);
            }
    
            $response = $this->service->createProduct($request,Auth::user());
            return response()->json($response, $response['status']);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => $th->getMessage(),
                'status' => 500
            ];
            return response()->json($response, 500);
        }
        
    }

    public function getById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }

        $data = Products::with(['quantity','offer'])->find($request->id);


        if (is_null($data)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }
        $related = Products::where(['status' => 1, 'store_id' => $data->store_id, 'sub_cate_id' => $data->sub_cate_id])->get();
        $storeInfo = Stores::select('id', 'uid', 'name', 'status', 'zipcode', 'cid')->where('uid', $data->store_id)->first();
        $response = [
            'data' => $data,
            'related' => $related,
            'soldby' => $storeInfo,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $data = $request->all() ;
        $data['store_id'] = Auth::user()->store->id ;
        if (isset($request->tva)) {
            $data['tva_id'] = $request->tva ;
        }

        //update product
        $data = Products::find($request->id)->update($data);

        //update offer
        $product = Products::find($request->id);
        if (isset($request->offer)) {
            $product->offer()->update([
                'rates' => $request->offer,
                'exp_offer' => $request->exp_offer,
                'start_offer' => $request->start_offer,
            ]);
        }

        //update quantity
        $product = Products::find($request->id);
        if ($request->quantity) {
            $product->quantity()->update([
                'stock' => $request->quantity
            ]);
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
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function updateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $data = Products::find($request->id)->update($request->only('status'));

        if (is_null($data)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function updateOffers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $data = Products::find($request->id)->update($request->only('in_offer'));

        if (is_null($data)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function updateHome(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $data = Products::find($request->id)->update($request->only('in_home'));

        if (is_null($data)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $data = Products::find($request->id);
        if ($data) {
            $data->delete();
            $response = [
                'data' => $data,
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

    public function getAll()
    {
        $data = Products::all();
        if (is_null($data)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }

        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function searchStoreWithGeoLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        if (2 == 1) {
            $values = 3959; // miles
            $distanceType = 'miles';
        } else {
            $values = 6371; // km
            $distanceType = 'km';
        }
        $settings = DB::table('settings')->select('search_radius')->first();
        \DB::enableQueryLog();
        $stores = Stores::select(DB::raw('store.id as id,store.uid as uid,store.name as name,store.mobile as mobile,store.lat as lat,store.lng as lng,
        store.verified as verified,store.address as address,store.descriptions as descriptions,store.images as images,store.cover as cover,store.open_time as open_time,
        store.close_time as close_time,store.isClosed as isClosed,store.certificate_url as certificate_url,store.certificate_type as certificate_type,store.rating as rating,
        store.total_rating as total_rating,store.cid as cid,store.zipcode as zipcode,store.extra_field as extra_field,store.status as status, ( ' . $values . ' * acos( cos( radians(' . $request->lat . ') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(' . $request->lng . ') ) + sin( radians(' . $request->lat . ') ) * sin( radians( lat ) ) ) ) AS distance'))
            ->having('distance', '<', $settings->search_radius)
            ->orderBy('distance')
            ->where('store.status', 1)
            ->get();

        $response = [
            'data' => $stores,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function searchWithGeoLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        if (2 == 1) {
            $values = 3959; // miles
            $distanceType = 'miles';
        } else {
            $values = 6371; // km
            $distanceType = 'km';
        }
        $today = Carbon::now();
        $settings = DB::table('settings')->select('search_radius')->first();
        \DB::enableQueryLog();
        $stores = Stores::select(DB::raw('store.id as id,store.uid as uid,store.name as name,store.mobile as mobile,store.lat as lat,store.lng as lng,
        store.verified as verified,store.address as address,store.descriptions as descriptions,store.images as images,store.cover as cover,store.open_time as open_time,
        store.close_time as close_time,store.isClosed as isClosed,store.certificate_url as certificate_url,store.certificate_type as certificate_type,store.rating as rating,
        store.total_rating as total_rating,store.cid as cid,store.zipcode as zipcode,store.extra_field as extra_field,store.status as status, ( ' . $values . ' * acos( cos( radians(' . $request->lat . ') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(' . $request->lng . ') ) + sin( radians(' . $request->lat . ') ) * sin( radians( lat ) ) ) ) AS distance'))
            ->having('distance', '<', $settings->search_radius)
            ->orderBy('distance')
            ->where('store.status', 1)
            ->get();
        if (count($stores)) {
            $storeIds = $stores->pluck('uid')->toArray();
            $cityId = $stores[0]->cid;
            $banners = Banners::where(['status' => 1, 'city_id' => $cityId])->whereDate('from', '<=', $today)->whereDate('to', '>=', $today)->get();
            $category = Category::where('status', 1)->get();
            $homeProducts = Products::where(['status' => 1, 'in_home' => 1])->WhereIn('store_id', $storeIds)->orderBy('rating', 'desc')->limit(15)->get();
            $inOffers = Products::where('status', 1)->where('discount', '>', 0)->WhereIn('store_id', $storeIds)->orderBy('discount', 'desc')->limit(15)->get();
            $topProducts = Products::where('status', 1)->orWhere('in_home', 1)->WhereIn('store_id', $storeIds)->orderBy('rating', 'desc')->limit(15)->get();
            $city = Cities::where('id', $cityId)->first();
            foreach ($category as $loop) {
                $loop->subCates = SubCategory::where(['status' => 1, 'cate_id' => $loop->id])->get();
            }

            $data = [
                'stores' => $stores,
                'banners' => $banners,
                'category' => $category,
                'topProducts' => $topProducts,
                'inOffers' => $inOffers,
                'storeIds' => $storeIds,
                'homeProducts' => $homeProducts,
                'cityInfo' => $city,
            ];
            $response = [
                'data' => $data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }
        $response = [
            'data' => $stores,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getWithSubCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'storeIds' => 'required',
            'sub' => 'required',
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $ids = explode(',', $request->storeIds);
        $products = Products::where(['status' => 1, 'cate_id' => $request->id, 'sub_cate_id' => $request->sub])->WhereIn('store_id', $ids)->orderBy('name', 'asc')->limit($request->limit)->get();
        $response = [
            'data' => $products,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getWithSubCategoryId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'storeIds' => 'required',
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $ids = explode(',', $request->storeIds);
        $products = Products::where(['status' => 1, 'sub_cate_id' => $request->id])->WhereIn('store_id', $ids)->orderBy('name', 'asc')->limit($request->limit)->get();
        $response = [
            'data' => $products,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function searchQuery(Request $request)
    {
        $str = "";
        if ($request->has('param') && $request->has('stores')) {
            $str = $request->param;
            $stores = $request->stores;
        }
        $ids = explode(',', $stores);

        $products = Products::select('id', 'name', 'store_id', 'cover')->where('status', 1)->where('name', 'like', '%' . $str . '%')->WhereIn('store_id', $ids)->orderBy('name', 'asc')->limit(5)->get();
        $response = [
            'data' => $products,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getByStoreId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $data = Products::where(['status' => 1, 'store_id' => $request->id])->orderBy('name', 'asc')->limit($request->limit)->get();
        $storeInfo = Stores::select('id', 'uid', 'name', 'status', 'zipcode', 'cid')->where('uid', $request->id)->first();
        $response = [
            'data' => $data,
            'storeInfo' => $storeInfo,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getByStoreIdStoreAll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $storeInfo = Stores::select('id', 'uid', 'name', 'status', 'zipcode', 'cid')->where('uid', $request->id)->first();
        $data = Products::where(['store_id' => $storeInfo->id])->orderBy('name', 'asc')->limit($request->limit)->get();
        $response = [
            'data' => $data,
            'storeInfo' => $storeInfo,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getByIdgetByIdStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }

        $data = Products::with(['offer','tva'])->find($request->id); 


        if (is_null($data)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }
        $category = Category::where('id', $data->cate_id)->first();
        $subCategory = SubCategory::where('id', $data->sub_cate_id)->first();
        $response = [
            'data' => $data,
            'category' => $category,
            'subCategory' => $subCategory,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getTopRated(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'storeIds' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $ids = explode(',', $request->storeIds);
        $homeProducts = Products::where(['status' => 1, 'in_home' => 1])->WhereIn('store_id', $ids)->orderBy('rating', 'desc')->limit(15)->get();
        $topProducts = Products::where('status', 1)->orWhere('in_home', 1)->WhereIn('store_id', $ids)->orderBy('rating', 'desc')->limit(15)->get();
        $response = [
            'topProducts' => $topProducts,
            'homeProducts' => $homeProducts,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function searchStoreWithZipCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'zipcode' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $stores = Stores::where(['status' => 1, 'zipcode' => $request->zipcode])->get();
        $response = [
            'data' => $stores,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function searchWithZipCode(Request $request)
    {

        $zipcode = $request->zipcode;
        if ($zipcode == null || !$zipcode || !isset($zipcode)) {
            $settings = Settings::first();
            $zipcode = $settings->default_delivery_zip;
        }
        $today = Carbon::now();
        $stores = Stores::where(['status' => 1, 'zipcode' => $zipcode])->get();
        if (count($stores)) {
            $storeIds = $stores->pluck('uid')->toArray();
            $cityId = $stores[0]->cid;
            $banners = Banners::where(['status' => 1, 'city_id' => $cityId])->whereDate('from', '<=', $today)->whereDate('to', '>=', $today)->get();
            $category = Category::where('status', 1)->get();
            $homeProducts = Products::where(['status' => 1, 'in_home' => 1])->WhereIn('store_id', $storeIds)->orderBy('rating', 'desc')->limit(15)->get();
            $inOffers = Products::where('status', 1)->where('discount', '>', 0)->WhereIn('store_id', $storeIds)->orderBy('discount', 'desc')->limit(15)->get();
            $topProducts = Products::where('status', 1)->orWhere('in_home', 1)->WhereIn('store_id', $storeIds)->orderBy('rating', 'desc')->limit(15)->get();
            $city = Cities::where('id', $cityId)->first();
            foreach ($category as $loop) {
                $loop->subCates = SubCategory::where(['status' => 1, 'cate_id' => $loop->id])->get();
            }

            $data = [
                'stores' => $stores,
                'banners' => $banners,
                'category' => $category,
                'topProducts' => $topProducts,
                'homeProducts' => $homeProducts,
                'inOffers' => $inOffers,
                'storeIds' => $storeIds,
                'cityInfo' => $city,
            ];
            $response = [
                'data' => $data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }
        $response = [
            'data' => null,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function searchStoreWithCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500 
            ];
            return response()->json($response, 404);
        }
        // $stores = Stores::where(['status' => 1, 'cid' => $request->id])->get();
        $stores = Stores::whereHas('countrie',function ($q)use($request){
            $q->where('code_pays',$request->countrie_code);
        })->where(['status' => 1])->get();
        $response = [
            'data' => $stores,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function searchWithCity(Request $request)
    {
        $today = Carbon::now();
        $cid = $request->id;
        if ($cid == null || !$cid || !isset($cid)) {
            $settings = Settings::first();
            $cid = $settings->default_city_id;
        }
        $stores = Stores::where(['status' => 1])->get();
        if (count($stores)) {
            $storeIds = $stores->pluck('uid')->toArray();
            $banners = Banners::where(['status' => 1, 'city_id' => $cid])->whereDate('from', '<=', $today)->whereDate('to', '>=', $today)->get();
            $category = Category::where('status', 1)->get();
            $homeProducts = Products::with('tva')->where(['status' => 1, 'in_home' => 1])->WhereIn('store_id', $storeIds)->orderBy('rating', 'desc')->limit(15)->get();
            $topProducts = Products::with('tva')->where('status', 1)->orWhere('in_home', 1)->WhereIn('store_id', $storeIds)->orderBy('rating', 'desc')->limit(15)->get();
            $inOffers = Products::with('tva')->where('status', 1)->where('discount', '>', 0)->WhereIn('store_id', $storeIds)->orderBy('discount', 'desc')->limit(15)->get();
            $city = Cities::where('id', $cid)->first();
            foreach ($category as $loop) {
                $loop->subCates = SubCategory::where(['status' => 1, 'cate_id' => $loop->id])->get();
            }

            $data = [
                'stores' => $stores,
                'banners' => $banners,
                'category' => $category,
                'topProducts' => $topProducts,
                'homeProducts' => $homeProducts,
                'inOffers' => $inOffers,
                'storeIds' => $storeIds,
                'cityInfo' => $city,
            ];
            $response = [
                'data' => $data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }
        $response = [
            'data' => null,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getProductsWithCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'catID' => 'required',
            'subId' => 'required',
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $today = Carbon::now();
        $cid = $request->id;
        if ($cid == null || !$cid || !isset($cid)) {
            $settings = Settings::first();
            $cid = $settings->default_city_id;
        }
        $stores = Stores::where(['status' => 1, 'cid' => $cid])->get();
        if (count($stores)) {
            $storeIds = $stores->pluck('uid')->toArray();
            $banners = Banners::where(['status' => 1, 'city_id' => $cid])->whereDate('from', '<=', $today)->whereDate('to', '>=', $today)->get();
            $products = Products::where(['status' => 1, 'cate_id' => $request->catID, 'sub_cate_id' => $request->subId])->WhereIn('store_id', $storeIds)->orderBy('rating', 'desc')->limit($request->limit)->get();

            $data = [
                'banners' => $banners,
                'products' => $products,
            ];
            $response = [
                'data' => $data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }
        $response = [
            'data' => null,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getProductsWithZipCodes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'catID' => 'required',
            'subId' => 'required',
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $zipcode = $request->zipcode;
        if ($zipcode == null || !$zipcode || !isset($zipcode)) {
            $settings = Settings::first();
            $zipcode = $settings->default_delivery_zip;
        }
        $today = Carbon::now();
        $stores = Stores::where(['status' => 1, 'zipcode' => $zipcode])->get();
        if (count($stores)) {
            $storeIds = $stores->pluck('uid')->toArray();
            $cityId = $stores[0]->cid;
            $banners = Banners::where(['status' => 1, 'city_id' => $cityId])->whereDate('from', '<=', $today)->whereDate('to', '>=', $today)->get();
            $products = Products::where(['status' => 1, 'cate_id' => $request->catID, 'sub_cate_id' => $request->subId])->WhereIn('store_id', $storeIds)->orderBy('rating', 'desc')->limit($request->limit)->get();

            $data = [
                'banners' => $banners,
                'products' => $products,
            ];
            $response = [
                'data' => $data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }
        $response = [
            'data' => null,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getProductsWithLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
            'catID' => 'required',
            'subId' => 'required',
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        if (2 == 1) {
            $values = 3959; // miles
            $distanceType = 'miles';
        } else {
            $values = 6371; // km
            $distanceType = 'km';
        }
        $today = Carbon::now();
        $settings = DB::table('settings')->select('search_radius')->first();
        \DB::enableQueryLog();
        $stores = Stores::select(DB::raw('store.id as id,store.uid as uid,store.name as name,store.mobile as mobile,store.lat as lat,store.lng as lng,
        store.verified as verified,store.address as address,store.descriptions as descriptions,store.images as images,store.cover as cover,store.open_time as open_time,
        store.close_time as close_time,store.isClosed as isClosed,store.certificate_url as certificate_url,store.certificate_type as certificate_type,store.rating as rating,
        store.total_rating as total_rating,store.cid as cid,store.zipcode as zipcode,store.extra_field as extra_field,store.status as status, ( ' . $values . ' * acos( cos( radians(' . $request->lat . ') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(' . $request->lng . ') ) + sin( radians(' . $request->lat . ') ) * sin( radians( lat ) ) ) ) AS distance'))
            ->having('distance', '<', $settings->search_radius)
            ->orderBy('distance')
            ->where('store.status', 1)
            ->get();
        if (count($stores)) {
            $storeIds = $stores->pluck('uid')->toArray();
            $cityId = $stores[0]->cid;
            $banners = Banners::where(['status' => 1, 'city_id' => $cityId])->whereDate('from', '<=', $today)->whereDate('to', '>=', $today)->get();
            $products = Products::where(['status' => 1, 'cate_id' => $request->catID, 'sub_cate_id' => $request->subId])->WhereIn('store_id', $storeIds)->orderBy('rating', 'desc')->limit($request->limit)->get();

            $data = [
                'banners' => $banners,
                'products' => $products,
            ];
            $response = [
                'data' => $data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }
        $response = [
            'data' => $stores,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getTopRateProductsWithCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }

        $products = Products::whereHas('store',function ($q)use($request) {
            $q->whereHas('countrie',function ($query) use($request){
                $query->where('code_pays',$request->countrie_code);
            });
        })->get();

        $data = [
            'products' => $products,
        ];

        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);



        // $today = Carbon::now();
        // $cid = $request->id;
        // if ($cid == null || !$cid || !isset($cid)) {
        //     $settings = Settings::first();
        //     $cid = $settings->default_city_id;
        // }
        // $stores = Stores::where(['status' => 1, 'cid' => $cid])->get();
        // if (count($stores)) {
        //     $storeIds = $stores->pluck('uid')->toArray();

        //     $topProducts = Products::where('status', 1)->orWhere('in_home', 1)->WhereIn('store_id', $storeIds)->orderBy('rating', 'desc')->limit($request->limit)->get();
        //     $data = [
        //         'products' => $topProducts,
        //     ];
        //     $response = [
        //         'data' => $data,
        //         'success' => true,
        //         'status' => 200,
        //     ];
        //     return response()->json($response, 200);
        // }
        // $response = [
        //     'data' => null,
        //     'success' => true,
        //     'status' => 200,
        // ];
        return response()->json($response, 200);
    }

    public function getTopRateProductsWithLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        if (2 == 1) {
            $values = 3959; // miles
            $distanceType = 'miles';
        } else {
            $values = 6371; // km
            $distanceType = 'km';
        }
        $today = Carbon::now();
        $settings = DB::table('settings')->select('search_radius')->first();
        \DB::enableQueryLog();
        $stores = Stores::select(DB::raw('store.id as id,store.uid as uid,store.name as name,store.mobile as mobile,store.lat as lat,store.lng as lng,
        store.verified as verified,store.address as address,store.descriptions as descriptions,store.images as images,store.cover as cover,store.open_time as open_time,
        store.close_time as close_time,store.isClosed as isClosed,store.certificate_url as certificate_url,store.certificate_type as certificate_type,store.rating as rating,
        store.total_rating as total_rating,store.cid as cid,store.zipcode as zipcode,store.extra_field as extra_field,store.status as status, ( ' . $values . ' * acos( cos( radians(' . $request->lat . ') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(' . $request->lng . ') ) + sin( radians(' . $request->lat . ') ) * sin( radians( lat ) ) ) ) AS distance'))
            ->having('distance', '<', $settings->search_radius)
            ->orderBy('distance')
            ->where('store.status', 1)
            ->get();
        if (count($stores)) {
            $storeIds = $stores->pluck('uid')->toArray();
            $topProducts = Products::where('status', 1)->orWhere('in_home', 1)->WhereIn('store_id', $storeIds)->orderBy('rating', 'desc')->limit($request->limit)->get();
            $data = [
                'products' => $topProducts,
            ];
            $response = [
                'data' => $data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }
        $response = [
            'data' => $stores,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getTopRateProductsWithZipcodes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $zipcode = $request->zipcode;
        if ($zipcode == null || !$zipcode || !isset($zipcode)) {
            $settings = Settings::first();
            $zipcode = $settings->default_delivery_zip;
        }
        $today = Carbon::now();
        $stores = Stores::where(['status' => 1, 'zipcode' => $zipcode])->get();
        if (count($stores)) {
            $storeIds = $stores->pluck('uid')->toArray();
            $topProducts = Products::where('status', 1)->orWhere('in_home', 1)->WhereIn('store_id', $storeIds)->orderBy('rating', 'desc')->limit($request->limit)->get();
            $data = [
                'products' => $topProducts,
            ];
            $response = [
                'data' => $data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }
        $response = [
            'data' => null,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getOffersProductsWithCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $today = Carbon::now();
        $cid = $request->id;
        if ($cid == null || !$cid || !isset($cid)) {
            $settings = Settings::first();
            $cid = $settings->default_city_id;
        }
        $stores = Stores::where(['status' => 1, 'cid' => $cid])->get();
        if (count($stores)) {
            $storeIds = $stores->pluck('uid')->toArray();
            $inOffers = Products::where('status', 1)->where('discount', '>', 0)->WhereIn('store_id', $storeIds)->orderBy('discount', 'desc')->limit($request->limit)->get();
            $data = [
                'products' => $inOffers,
            ];
            $response = [
                'data' => $data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }
        $response = [
            'data' => null,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getOffersProductsWithLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        if (2 == 1) {
            $values = 3959; // miles
            $distanceType = 'miles';
        } else {
            $values = 6371; // km
            $distanceType = 'km';
        }
        $today = Carbon::now();
        $settings = DB::table('settings')->select('search_radius')->first();
        \DB::enableQueryLog();
        $stores = Stores::select(DB::raw('store.id as id,store.uid as uid,store.name as name,store.mobile as mobile,store.lat as lat,store.lng as lng,
        store.verified as verified,store.address as address,store.descriptions as descriptions,store.images as images,store.cover as cover,store.open_time as open_time,
        store.close_time as close_time,store.isClosed as isClosed,store.certificate_url as certificate_url,store.certificate_type as certificate_type,store.rating as rating,
        store.total_rating as total_rating,store.cid as cid,store.zipcode as zipcode,store.extra_field as extra_field,store.status as status, ( ' . $values . ' * acos( cos( radians(' . $request->lat . ') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(' . $request->lng . ') ) + sin( radians(' . $request->lat . ') ) * sin( radians( lat ) ) ) ) AS distance'))
            ->having('distance', '<', $settings->search_radius)
            ->orderBy('distance')
            ->where('store.status', 1)
            ->get();
        if (count($stores)) {
            $storeIds = $stores->pluck('uid')->toArray();
            $inOffers = Products::where('status', 1)->where('discount', '>', 0)->WhereIn('store_id', $storeIds)->orderBy('discount', 'desc')->limit($request->limit)->get();
            $data = [
                'products' => $inOffers,
            ];
            $response = [
                'data' => $data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }
        $response = [
            'data' => $stores,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getOffersProductsWithZipcodes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $zipcode = $request->zipcode;
        if ($zipcode == null || !$zipcode || !isset($zipcode)) {
            $settings = Settings::first();
            $zipcode = $settings->default_delivery_zip;
        }
        $today = Carbon::now();
        $stores = Stores::where(['status' => 1, 'zipcode' => $zipcode])->get();
        if (count($stores)) {
            $storeIds = $stores->pluck('uid')->toArray();
            $inOffers = Products::where('status', 1)->where('discount', '>', 0)->WhereIn('store_id', $storeIds)->orderBy('discount', 'desc')->limit($request->limit)->get();
            $data = [
                'products' => $inOffers,
            ];
            $response = [
                'data' => $data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }
        $response = [
            'data' => null,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function importData(Request $request)
    {
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

                    if (count($header) == count($row)) {
                        $row = array_combine($header, $row);
                        $insertInfo =  array(
                            'id' => $row['id'],
                            'store_id' => $row['store_id'],
                            'cover' => $row['cover'],
                            'name' => $row['name'],
                            'images' => $row['images'],
                            'original_price' => $row['original_price'],
                            'sell_price' => $row['sell_price'],
                            'discount' => $row['discount'],
                            'kind' => $row['kind'],
                            'cate_id' => $row['cate_id'],
                            'sub_cate_id' => $row['sub_cate_id'],
                            'in_home' => $row['in_home'],
                            'is_single' => $row['is_single'],
                            'have_gram' => $row['have_gram'],
                            'gram' => $row['gram'],
                            'have_kg' => $row['have_kg'],
                            'kg' => $row['kg'],
                            'have_pcs' => $row['have_pcs'],
                            'pcs' => $row['pcs'],
                            'have_liter' => $row['have_liter'],
                            'liter' => $row['liter'],
                            'have_ml' => $row['have_ml'],
                            'ml' => $row['ml'],
                            'descriptions' => $row['descriptions'],
                            'key_features' => $row['key_features'],
                            'disclaimer' => $row['disclaimer'],
                            'exp_date' => $row['exp_date'],
                            'type_of' => $row['type_of'],
                            'in_stoke' => $row['in_stoke'],
                            'rating' => $row['rating'],
                            'total_rating' => $row['total_rating'],
                            'status' => $row['status'],
                            'in_offer' => $row['in_offer'],
                            'variations' => $row['variations'],
                            'size' => $row['size'],
                        );
                        $checkLead  =  Products::where("id", "=", $row["id"])->first();
                        if (!is_null($checkLead)) {
                            DB::table('products')->where("id", "=", $row["id"])->update($insertInfo);
                        } else {
                            DB::table('products')->insert($insertInfo);
                        }
                    }
                }
            }
        }
        $response = [
            'data' => 'Done',
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }
}