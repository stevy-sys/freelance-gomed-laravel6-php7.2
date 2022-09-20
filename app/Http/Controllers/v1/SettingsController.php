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
use App\Models\Settings;
use App\Models\Category;
use App\Models\Languages;
use App\Models\General;
use App\Models\SubCategory;
use App\Models\Cities;
use App\Models\User;
use App\Models\Flush;
use Validator;
use DB;
class SettingsController extends Controller
{
    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'currencySymbol' => 'required',
            'currencySide' => 'required',
            'currencyCode' => 'required',
            'appDirection' => 'required',
            'logo' => 'required',
            'sms_name' => 'required',
            'sms_creds' => 'required',
            'country_modal' => 'required',
            'web_category' => 'required',
            'default_country_code' => 'required',
            'default_city_id' => 'required',
            'default_delivery_zip' => 'required',
            'app_color' => 'required',
            'fcm_token' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.', $validator->errors(),
                'status'=> 500
            ];
            return response()->json($response, 404);
        }

        $data = Settings::create($request->all());
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

    public function getSettingsForOwner(Request $request){
        try {
            $data = DB::table('settings')->first();
            if($data && $data->web_category && $data->web_category !=null){
                $ids = explode(',',$data->web_category);
                $cats = Category::WhereIn('id',$ids)->get();
                $data->web_cates_data = $cats;
            }
            $citites = Cities::all();
            $response = [
                'data'=>$data,
                'citites'=>$citites,
                'categories'=>Category::all(),
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(),200);
        }
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

        $data = Settings::find($request->id);

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
        $data = Settings::find($request->id)->update($request->all());

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
        $data = Settings::find($request->id);
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
        $data = Settings::all();
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

    public function getDefault(Request $request){
        $settings = Settings::first();
        $general = General::first();
        $citites = Cities::where('status',1)->get();
        $matchThese = ['is_default'=>1,'status'=>1];
        $language = Languages::where($matchThese)->first();
        $support = User::select('id','first_name','last_name')->where('type','admin')->first();
        $appUpdates = Flush::where('key','app_updates')->first();
        $data = [
            'settings'=>$settings,
            'language'=>$language,
            'general'=>$general,
            'we_served'=>$citites,
            'support'=>$support,
            'appUpdates'=>$appUpdates
        ];

        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getDefaultWeb(Request $request){
        try {
            $settings = Settings::first();
            $general = General::first();
            $citites = Cities::where('status',1)->get();
            $matchThese = ['is_default'=>1,'status'=>1];
            $language = Languages::where($matchThese)->first();
            $allLanguages = Languages::select('id','name','cover')->where('status',1)->get();
            $support = User::select('id','first_name','last_name')->where('type','admin')->first();
            $cats = array();
            if($settings && $settings->web_category && $settings->web_category !=null){
                $ids = explode(',',$settings->web_category);
                $cats = Category::WhereIn('id',$ids)->get();
                foreach($cats as $loop){
                    $loop->subCates = SubCategory::where(['status'=>1,'cate_id'=>$loop->id])->get();
                }
            }

            $data = [
                'settings'=>$settings,
                'language'=>$language,
                'general'=>$general,
                'we_served'=>$citites,
                'support'=>$support,
                'allLanguages'=>$allLanguages,
                'categories'=>$cats,
            ];

            $response = [
                'data'=>$data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(),200);
        }

    }
    public function getByLanguageId(Request $request){
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
        $settings = Settings::first();
        $citites = Cities::where('status',1)->get();
        $matchThese = ['is_default'=>1,'status'=>1];
        $general = General::first();
        $language = Languages::find($request->id);
        $appUpdates = Flush::where('key','app_updates')->first();
        $support = User::select('id','first_name','last_name')->where('type','admin')->first();
        $data = [
            'settings'=>$settings,
            'language'=>$language,
            'general' => $general,
            'we_served'=>$citites,
            'support'=>$support,
            'appUpdates'=>$appUpdates
        ];

        $response = [
            'data'=>$data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getByLanguageIdWeb(Request $request){
        try {
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
            $settings = Settings::first();
            $citites = Cities::where('status',1)->get();
            $matchThese = ['is_default'=>1,'status'=>1];
            $general = General::first();
            $language = Languages::find($request->id);
            $support = User::select('id','first_name','last_name')->where('type','admin')->first();
            $allLanguages = Languages::select('id','name','cover')->where('status',1)->get();
            $cats = array();
            if($settings && $settings->web_category && $settings->web_category !=null){
                $ids = explode(',',$settings->web_category);
                $cats = Category::WhereIn('id',$ids)->get();
                foreach($cats as $loop){
                    $loop->subCates = SubCategory::where(['status'=>1,'cate_id'=>$loop->id])->get();
                }
            }
            $data = [
                'settings'=>$settings,
                'language'=>$language,
                'general' => $general,
                'we_served'=>$citites,
                'support'=>$support,
                'allLanguages'=>$allLanguages,
                'categories'=>$cats,
            ];

            $response = [
                'data'=>$data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(),200);
        }

    }
}
