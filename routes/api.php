<?php
/*
  Authors : Sayna (Rahul Jograna)
  Website : https://sayna.io/
  App Name : Grocery Delivery App
  This App Template Source code is licensed as per the
  terms found in the Website https://sayna.io/license
  Copyright and Good Faith Purchasers © 2021-present Sayna.
*/

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\OtpController;
use App\Http\Controllers\v1\TvaController;
use App\Http\Controllers\v1\FlushController;
use App\Http\Controllers\v1\PagesController;
use App\Http\Controllers\v1\PopupController;
use App\Http\Controllers\v1\UsersController;
use App\Http\Controllers\v1\CitiesController;
use App\Http\Controllers\v1\ManageController;
use App\Http\Controllers\v1\OffersController;
use App\Http\Controllers\v1\OrdersController;
use App\Http\Controllers\v1\StoresController;
use App\Http\Controllers\v1\AddressController;
use App\Http\Controllers\v1\BannersController;
use App\Http\Controllers\v1\DriversController;
use App\Http\Controllers\v1\GeneralController;
use App\Http\Controllers\v1\RatingsController;
use App\Http\Controllers\v1\CategoryController;
use App\Http\Controllers\v1\ContactsController;
use App\Http\Controllers\v1\PaymentsController;
use App\Http\Controllers\v1\PaytmPayController;
use App\Http\Controllers\v1\ProductsController;
use App\Http\Controllers\v1\ReferralController;
use App\Http\Controllers\v1\SettingsController;
use App\Http\Controllers\v1\Auth\AuthController;
use App\Http\Controllers\v1\ChatRoomsController;
use App\Http\Controllers\v1\FavouriteController;
use App\Http\Controllers\v1\LanguagesController;
use App\Http\Controllers\v1\SubscriberController;
use App\Http\Controllers\v1\Auth\LogoutController;
use App\Http\Controllers\v1\ComplaintsController;
use App\Http\Controllers\v1\SubCategoryController;
use App\Http\Controllers\v1\ChatMessagesController;
use App\Http\Controllers\v1\StoreRequestController;

use App\Http\Controllers\v1\Auth\RegisterController;
use App\Http\Controllers\v1\DriverRequestController;
use App\Http\Controllers\v1\ReferralCodesController;
use App\Http\Controllers\v1\Profile\ProfileController;
use App\Models\Products;
use App\Models\Stores;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/', function () {
    return [
        'app' => 'Gomed Delivery API by Sayna',
        'version' => '1.0.0',
    ];
});

Route::prefix('/v1')->group(function () { 

    Route::group(['namespace' => 'Auth'], function () {

        Route::post('auth/login', [AuthController::class, 'login']); 
        Route::post('auth/loginDrivers', [AuthController::class, 'loginDrivers']);
        Route::post('auth/adminLogin', [AuthController::class, 'adminLogin']);
        Route::post('auth/create_account', [RegisterController::class, 'register']);
        Route::post('auth/create_admin_account', [RegisterController::class, 'create_admin_account']);
        Route::post('auth/loginWithPhonePassword', [AuthController::class, 'loginWithPhonePassword']);
        Route::post('auth/loginWithPhonePasswordDrivers', [AuthController::class, 'loginWithPhonePasswordDrivers']);
        Route::get('auth/firebaseauth', [AuthController::class, 'firebaseauth']);
        Route::post('auth/verifyPhoneForFirebaseRegistrations', [AuthController::class, 'verifyPhoneForFirebaseRegistrations']);
        Route::post('auth/verifyPhoneForFirebase', [AuthController::class, 'verifyPhoneForFirebase']);
        Route::post('auth/verifyPhoneForFirebaseNew', [AuthController::class, 'verifyPhoneForFirebaseNew']);
        Route::post('auth/verifyPhoneForFirebaseDriver', [AuthController::class, 'verifyPhoneForFirebaseDriver']);
        Route::post('auth/verifyPhoneForFirebaseDriverNew', [AuthController::class, 'verifyPhoneForFirebaseDriverNew']);
        Route::post('auth/verifyEmailForReset', [AuthController::class, 'verifyEmailForReset']);
        Route::post('auth/verifyEmailForResetDriver', [AuthController::class, 'verifyEmailForResetDriver']);
        // Send reset password mail
        Route::post('auth/recovery', [ForgotPasswordController::class, 'sendPasswordResetLink']);
        // handle reset password form process
        Route::post('auth/reset', [ResetPasswordController::class, 'callResetPassword']);
        // handle reset password form process
        Route::post('auth/verify', [VerifyAccountController::class, 'verify']);
        Route::post('auth/loginWithMobileOtp', [AuthController::class, 'loginWithMobileOtp']);
        Route::post('auth/loginWithMobileOtpDriver', [AuthController::class, 'loginWithMobileOtpDriver']);
    });

    Route::group(['middleware' => ['jwt', 'jwt.auth']], function () {

        Route::group(['namespace' => 'Profile'], function () {
            Route::get('profile', [ProfileController::class, 'me']);
            Route::post('profile/update', [ProfileController::class, 'updateProfile']);
            Route::post('profile/password', [ProfileController::class, 'updatePassword']);
            Route::post('profile/getProfileById', [ProfileController::class, 'getProfileById']);
            Route::post('profile/byId', [ProfileController::class, 'getById']);
            Route::post('profile/getStoreFromId', [ProfileController::class, 'getStoreFromId']);
            Route::post('validate', [ProfileController::class, 'validate_user']);
        });

        Route::group(['namespace' => 'Auth'], function () {
            Route::post('auth/logout', [LogoutController::class, 'logout']);
        });

        // Payments Routes For Users
        Route::post('payments/createStripeToken', [PaymentsController::class, 'createStripeToken']);
        Route::post('payments/createCustomer', [PaymentsController::class, 'createCustomer']);
        Route::post('payments/getStripeCards', [PaymentsController::class, 'getStripeCards']);
        Route::post('payments/addStripeCards', [PaymentsController::class, 'addStripeCards']);
        Route::post('payments/createStripePayments', [PaymentsController::class, 'createStripePayments']);
        Route::get('getPayPalKey', [PaymentsController::class, 'getPayPalKey']);
        Route::get('getFlutterwaveKey', [PaymentsController::class, 'getFlutterwaveKey']);
        Route::get('getPaystackKey', [PaymentsController::class, 'getPaystackKey']);
        Route::get('getRazorPayKey', [PaymentsController::class, 'getRazorPayKey']);
        Route::get('payments/getPayments', [PaymentsController::class, 'getPayments']);

        Route::post('referralcode/getMyCode', [ReferralCodesController::class, 'getMyCode']);
        Route::post('referral/redeemReferral', [ReferralController::class, 'redeemReferral']);
        Route::post('profile/getMyWallet', [ProfileController::class, 'getMyWallet']);
        Route::post('profile/getMyWalletBalance', [ProfileController::class, 'getMyWalletBalance']);

        // Address Routes
        Route::post('address/getByUid', [AddressController::class, 'getByUid']);
        Route::post('address/deleteMyAddress', [AddressController::class, 'delete']);
        Route::post('address/addNew', [AddressController::class, 'save']);
        Route::post('address/updateMyAddress', [AddressController::class, 'update']);

        Route::post('stores/getStoresData', [StoresController::class, 'getStoresData']);

        // Orders Routes

        Route::post('orders/makeOrder', [OrdersController::class, 'makeOrder']);
        Route::post('orders/createOrderStore', [OrdersController::class, 'createOrderStore']);


        Route::post('orders/create', [OrdersController::class, 'save']);
        Route::post('orders/getById', [OrdersController::class, 'getById']);
        Route::post('orders/sendMailForOrders', [OrdersController::class, 'sendMailForOrders']);
        Route::post('orders/getByUid', [OrdersController::class, 'getByUid']);
        Route::post('orders/searchWithId', [OrdersController::class, 'searchWithId']);
        Route::post('orders/getByOrderId', [OrdersController::class, 'getByOrderId']);

        // Favourite Routes
        Route::post('favourite/create', [FavouriteController::class, 'save']);
        Route::post('favourite/update', [FavouriteController::class, 'update']);
        Route::post('favourite/getMyFav', [FavouriteController::class, 'getMyFav']);
        Route::post('favourite/getMyFavList', [FavouriteController::class, 'getMyFavList']);

        // Reset Password
        Route::post('password/updateUserPasswordWithEmail', [ProfileController::class, 'updateUserPasswordWithEmail']);
        Route::post('password/updateUserPasswordWithPhone', [ProfileController::class, 'updateUserPasswordWithPhone']);
        Route::post('password/updatePasswordFromFirebase', [ProfileController::class, 'updatePasswordFromFirebase']);
        Route::post('orders/updateStatusUser', [OrdersController::class, 'updateStatusStore']);
        Route::post('drivers/edit_profile', [DriversController::class, 'update']);

        Route::post('complaints/registerNewComplaints', [ComplaintsController::class, 'save']);

        Route::post('notification/sendNotification', [ProfileController::class, 'sendNotification']);
        Route::post('notification/sendToStore', [ProfileController::class, 'sendToStore']);

        Route::post('chats/getById', [ChatMessagesController::class, 'getById']);
        Route::post('chats/sendMessage', [ChatMessagesController::class, 'save']);

        Route::post('chats/getChatRooms', [ChatRoomsController::class, 'getChatRooms']);
        Route::post('chats/createChatRooms', [ChatRoomsController::class, 'createChatRooms']);
        Route::post('chats/getChatListBUid', [ChatRoomsController::class, 'getChatListBUid']);

        Route::post('driverInfo/byId', [DriversController::class, 'getById']);
        Route::post('storesInfo/getByIds', [StoresController::class, 'getById']);

        Route::post('ratings/getByStoreId', [RatingsController::class, 'getByStoreId']);
        Route::post('ratings/saveStoreRatings', [RatingsController::class, 'saveStoreRatings']);

        Route::post('ratings/getByProductId', [RatingsController::class, 'getByProductId']);
        Route::post('ratings/saveProductRatings', [RatingsController::class, 'saveProductRatings']);

        Route::post('ratings/getByDriverId', [RatingsController::class, 'getByDriverId']);
        Route::post('ratings/saveDriversRatings', [RatingsController::class, 'saveDriversRatings']);

        Route::post('search/mytva', [TvaController::class, 'searchTva']);

    });

    Route::group(['namespace' => 'Profile'], function () {
        Route::get('users/get_admin', [ProfileController::class, 'get_admin']);
        Route::get('users/get_admin_account', [ProfileController::class, 'get_admin_account']);
        Route::post('uploadImage', [ProfileController::class, 'uploadImage']);

        Route::post('users/emailExist', [ProfileController::class, 'emailExist']);
    });

    Route::group(['middleware' => ['admin_auth', 'jwt.auth']], function () {


        //TVA Routes
        Route::get('tva/getAllTvaWithCountrie', [TvaController::class, 'getAllTvaWithCountrie']);
        Route::post('tva/importCsvTva', [TvaController::class, 'importCsvTva']);
        Route::put('tva/updateTva/{tva}', [TvaController::class, 'updateTva']);
        Route::delete('tva/deleteTva/{tva}', [TvaController::class, 'deleteTva']);
        Route::get('tva/downloadTva/', [TvaController::class, 'downloadTva']);

        // Settings Routes
        Route::get('setttings/getSettingsForOwner', [SettingsController::class, 'getSettingsForOwner']);
        Route::post('setttings/save', [SettingsController::class, 'save']);
        Route::post('setttings/update', [SettingsController::class, 'update']);

        Route::get('home/getAdminDashboard', [OrdersController::class, 'getAdminDashboard']);
        // Gereral Routes
        Route::get('general/getAll', [GeneralController::class, 'getAll']);
        Route::post('general/save', [GeneralController::class, 'save']);
        Route::post('general/update', [GeneralController::class, 'update']);

        // Imports Routes
        Route::post('users/importData', [UsersController::class, 'importData']);
        Route::post('address/importData', [AddressController::class, 'importData']);
        Route::post('banners/importData', [BannersController::class, 'importData']);
        Route::post('category/importData', [CategoryController::class, 'importData']);
        Route::post('chatMessages/importData', [ChatMessagesController::class, 'importData']);
        Route::post('chatRooms/importData', [ChatRoomsController::class, 'importData']);
        Route::post('cities/importData', [CitiesController::class, 'importData']);
        Route::post('contacts/importData', [ContactsController::class, 'importData']);
        Route::post('drivers/importData', [DriversController::class, 'importData']);
        Route::post('favourite/importData', [FavouriteController::class, 'importData']);
        Route::post('general/importData', [GeneralController::class, 'importData']);
        Route::post('manage/importData', [ManageController::class, 'importData']);
        Route::post('offers/importData', [OffersController::class, 'importData']);
        Route::post('orders/importData', [OrdersController::class, 'importData']);
        Route::post('otp/importData', [OtpController::class, 'importData']);
        Route::post('products/importData', [ProductsController::class, 'importData']);
        Route::post('ratings/importData', [RatingsController::class, 'importData']);
        Route::post('stores/importData', [StoresController::class, 'importData']);
        Route::post('sub_categories/importData', [SubCategoryController::class, 'importData']);
        Route::post('subscriber/importData', [SubscriberController::class, 'importData']);

        // Referral Routes
        Route::get('referral/getAll', [ReferralController::class, 'getAll']);
        Route::post('referral/save', [ReferralController::class, 'save']);
        Route::post('referral/update', [ReferralController::class, 'update']);

        // LanguagesController Routes
        Route::post('languages/create', [LanguagesController::class, 'save']);
        Route::post('languages/getById', [LanguagesController::class, 'getById']);
        Route::get('languages/getAll', [LanguagesController::class, 'getAll']);
        Route::post('languages/update', [LanguagesController::class, 'update']);
        Route::post('languages/delete', [LanguagesController::class, 'delete']);
        Route::post('languages/changeDefault', [LanguagesController::class, 'changeDefault']);

        // Cities Routes
        Route::get('cities/getAll', [CitiesController::class, 'getAll']);
        Route::post('cities/create', [CitiesController::class, 'save']);
        Route::post('cities/update', [CitiesController::class, 'update']);
        Route::post('cities/destroy', [CitiesController::class, 'delete']);
        Route::post('cities/getById', [CitiesController::class, 'getById']);

        // Address Routes
        Route::get('address/getAll', [AddressController::class, 'getAll']);
        Route::post('address/create', [AddressController::class, 'save']);
        Route::post('address/update', [AddressController::class, 'update']);
        Route::post('address/destroy', [AddressController::class, 'delete']);
        Route::post('address/getById', [AddressController::class, 'getById']);

        // Store Routes
        Route::get('store/getAll', [StoresController::class, 'getAll']);
        Route::get('store/getStores', [StoresController::class, 'getStores']);
        Route::post('store/create', [StoresController::class, 'save']);
        Route::post('store/update', [StoresController::class, 'update']);
        Route::post('store/destroy', [StoresController::class, 'delete']);
        Route::post('store/getById', [StoresController::class, 'getById']);
        Route::post('store/updateStatus', [StoresController::class, 'updateStatus']);
        Route::post('store/createStoreProfile', [RegisterController::class, 'createStoreProfile']);

        // User Routes
        Route::get('users/getAll', [UsersController::class, 'getAll']); 
        Route::get('users/admins', [UsersController::class, 'admins']);
        Route::post('users/deleteUser', [UsersController::class, 'delete']);
        Route::post('users/adminNewAdmin', [RegisterController::class, 'adminNewAdmin']);
        Route::post('users/sendMailToUsers', [UsersController::class, 'sendMailToUsers']);
        Route::post('users/sendMailToAll', [UsersController::class, 'sendMailToAll']);
        Route::post('users/sendMailToStores', [UsersController::class, 'sendMailToStores']);
        Route::post('users/sendMailToDrivers', [UsersController::class, 'sendMailToDrivers']);
        Route::post('users/userInfoAdmin', [UsersController::class, 'getInfo']);

        // Drivers Routes
        Route::get('drivers/getAll', [DriversController::class, 'getAll']);
        Route::get('drivers/getStores', [DriversController::class, 'getStores']);
        Route::post('drivers/create', [DriversController::class, 'save']);
        Route::post('drivers/update', [DriversController::class, 'update']);
        Route::post('drivers/destroy', [DriversController::class, 'delete']);
        Route::post('drivers/getById', [DriversController::class, 'getById']);
        Route::post('drivers/updateStatus', [DriversController::class, 'updateStatus']);

        // Orders Routes
        Route::get('orders/getAll', [OrdersController::class, 'getAll']);
        Route::get('orders/getStores', [OrdersController::class, 'getStores']);
        Route::post('orders/getByIdAdmin', [OrdersController::class, 'getByIdAdmin']);
        Route::post('orders/update', [OrdersController::class, 'update']);
        Route::post('orders/destroy', [OrdersController::class, 'delete']);
        Route::post('orders/updateStatus', [OrdersController::class, 'updateStatus']);
        Route::post('orders/getStatsOfStore', [OrdersController::class, 'getStoreStatsDataWithDates']);
        Route::post('orders/updateStatusAdmin', [OrdersController::class, 'updateStatusStore']);
        Route::post('orders/getByStoreIdsAdmin', [OrdersController::class, 'getByStoreForApps']);
        Route::post('orders/getStoreStatsDataAdmin', [OrdersController::class, 'getStoreStatsData']);
        // Category Routes
        Route::get('category/getAll', [CategoryController::class, 'getAll']);
        Route::get('category/getStores', [CategoryController::class, 'getStores']);
        Route::post('category/create', [CategoryController::class, 'save']);
        Route::post('category/update', [CategoryController::class, 'update']);
        Route::post('category/destroy', [CategoryController::class, 'delete']);
        Route::post('category/getById', [CategoryController::class, 'getById']);
        Route::post('category/updateStatus', [CategoryController::class, 'updateStatus']);


        // Sub Category Routes
        Route::get('sub_categories/getAll', [SubCategoryController::class, 'getAll']);
        Route::get('sub_categories/getStores', [SubCategoryController::class, 'getStores']);
        Route::post('sub_categories/create', [SubCategoryController::class, 'save']);
        Route::post('sub_categories/update', [SubCategoryController::class, 'update']);
        Route::post('sub_categories/destroy', [SubCategoryController::class, 'delete']);
        Route::post('sub_categories/getById', [SubCategoryController::class, 'getById']);
        Route::post('sub_categories/updateStatus', [SubCategoryController::class, 'updateStatus']);

        // Banners Routes
        Route::get('banners/getAll', [BannersController::class, 'getAll']);
        Route::get('banners/getStores', [BannersController::class, 'getStores']);
        Route::post('banners/create', [BannersController::class, 'save']);
        Route::post('banners/update', [BannersController::class, 'update']);
        Route::post('banners/destroy', [BannersController::class, 'delete']);
        Route::post('banners/getById', [BannersController::class, 'getById']);
        Route::post('banners/updateStatus', [BannersController::class, 'updateStatus']);

        // Offers Routes //

        Route::get('offers/getAll', [OffersController::class, 'getAll']);
        Route::get('offers/getStores', [OffersController::class, 'getStores']);
        Route::post('offers/create', [OffersController::class, 'save']);
        Route::post('offers/update', [OffersController::class, 'update']);
        Route::post('offers/destroy', [OffersController::class, 'delete']);
        Route::post('offers/getById', [OffersController::class, 'getById']);
        Route::post('offers/updateStatus', [OffersController::class, 'updateStatus']);

        // Manage Routes
        Route::get('manage/getAll', [ManageController::class, 'getAll']);
        Route::post('manage/create', [ManageController::class, 'save']);
        Route::post('manage/update', [ManageController::class, 'update']);

        // Popup Routes
        Route::get('popup/getAll', [PopupController::class, 'getAll']);
        Route::post('popup/create', [PopupController::class, 'save']);
        Route::post('popup/update', [PopupController::class, 'update']);

        // Products Routes
        Route::get('products/getAll', [ProductsController::class, 'getAll']);
        Route::get('products/getStores', [ProductsController::class, 'getStores']);
        Route::post('products/create', [ProductsController::class, 'save']);
        Route::post('products/update', [ProductsController::class, 'update']);
        Route::post('products/destroy', [ProductsController::class, 'delete']);
        Route::post('products/getById', [ProductsController::class, 'getById']);
        Route::post('products/updateStatus', [ProductsController::class, 'updateStatus']);
        Route::post('products/updateOffers', [ProductsController::class, 'updateOffers']);
        Route::post('products/updateHome', [ProductsController::class, 'updateHome']);
        Route::post('products/getByStoreIdStoreAllAdmin', [ProductsController::class, 'getByStoreIdStoreAll']);

        // Payments Routes For Admin
        Route::post('payments/paytmRefund', [PaytmPayController::class, 'refundUserRequest']);
        Route::post('payments/paytmRefund', [PaytmPayController::class, 'refundUserRequest']);
        Route::post('payments/getById', [PaymentsController::class, 'getById']);
        Route::post('payments/getPaymentInfo', [PaymentsController::class, 'getPaymentInfo']);
        Route::get('payments/getAll', [PaymentsController::class, 'getAll']);
        Route::post('payments/update', [PaymentsController::class, 'update']);
        Route::post('payments/delete', [PaymentsController::class, 'delete']);
        Route::post('payments/refundFlutterwave', [PaymentsController::class, 'refundFlutterwave']);
        Route::post('payments/payPalRefund', [PaymentsController::class, 'payPalRefund']);
        Route::post('payments/refundPayStack', [PaymentsController::class, 'refundPayStack']);
        Route::post('payments/razorPayRefund', [PaymentsController::class, 'razorPayRefund']);
        Route::post('payments/refundStripePayments', [PaymentsController::class, 'refundStripePayments']);
        Route::post('payments/instaMOJORefund', [PaymentsController::class, 'instaMOJORefund']);

        // Pages Routes
        Route::post('pages/save', [PagesController::class, 'save']);
        Route::post('pages/update', [PagesController::class, 'update']);
        Route::post('pages/getById', [PagesController::class, 'getById']);
        Route::post('pages/delete', [PagesController::class, 'delete']);
        Route::get('pages/getAll', [PagesController::class, 'getAll']);

        Route::post('sendNoficationGlobal', [ProfileController::class, 'sendNoficationGlobal']);
        Route::post('notification/sendToAllUsers', [ProfileController::class, 'sendToAllUsers']);
        Route::post('notification/sendToUsers', [ProfileController::class, 'sendToUsers']);
        Route::post('notification/sendToStores', [ProfileController::class, 'sendToStores']);
        Route::post('notification/sendToDrivers', [ProfileController::class, 'sendToDrivers']);

        Route::get('contacts/getAll', [ContactsController::class, 'getAll']);
        Route::post('contacts/update', [ContactsController::class, 'update']);
        Route::post('mails/replyContactForm', [ContactsController::class, 'replyContactForm']);

        // store request Routes
        Route::get('store_request/getNewRequest', [StoreRequestController::class, 'getNewRequest']);
        Route::post('store_request/rejectRequest', [StoreRequestController::class, 'rejectRequest']);
        Route::post('store_request/acceptRequest', [StoreRequestController::class, 'acceptRequest']);

        Route::get('driver_request/getNewRequest', [DriverRequestController::class, 'getNewRequest']);
        Route::post('driver_request/rejectRequest', [DriverRequestController::class, 'rejectRequest']);
        Route::post('driver_request/acceptRequest', [DriverRequestController::class, 'acceptRequest']);

        // Complaints Routes
        Route::get('complaints/getAll', [ComplaintsController::class, 'getAll']);
        Route::post('complaints/update', [ComplaintsController::class, 'update']);
        Route::post('complaints/replyContactForm', [ComplaintsController::class, 'replyContactForm']);

        Route::post('ratings/getWithStoreIdAdmin', [RatingsController::class, 'getWithStoreId']);

        Route::post('flush/getByKey', [FlushController::class, 'getByKey']);
        Route::post('flush/save', [FlushController::class, 'save']);
        Route::post('flush/update', [FlushController::class, 'update']);
    });

    Route::group(['middleware' => ['store_auth', 'jwt.auth']], function () {
        Route::get('orders/getAll', [OrdersController::class, 'getAll']);
        Route::get('tva/getAllTvaWithCountrie', [TvaController::class, 'getAllTvaWithCountrie']);
        Route::post('orders/getByStoreForApps', [OrdersController::class, 'getByStoreForApps']); 
        Route::post('orders/actionOrder', [OrdersController::class, 'actionOrder']); 
        Route::get('orders/getAllOrderInMyStore', [OrdersController::class, 'getAllOrderInMyStore']);
        Route::get('orders/searchOrderInMyStore', [OrdersController::class, 'searchOrderInMyStore']);
        Route::post('orders/getByIdFromStore', [OrdersController::class, 'getByIdFromStore']);
        Route::post('orders/updateStatusStore', [OrdersController::class, 'updateStatusStore']);

        Route::post('drivers/geyByCity', [DriversController::class, 'geyByCity']);
        Route::post('drivers/edit_profile', [DriversController::class, 'update']);

        Route::post('products/getByStoreIdStore', [ProductsController::class, 'getByStoreId']);
        Route::post('products/getByStoreIdStoreAll', [ProductsController::class, 'getByStoreIdStoreAll']);
        Route::post('products/updateProducts', [ProductsController::class, 'update']);
        Route::post('products/saveProduct', [ProductsController::class, 'save']);
        Route::post('products/getByIdStore', [ProductsController::class, 'getByIdgetByIdStore']);
        Route::get('categories/getActiveItem', [CategoryController::class, 'getActiveItem']);
        Route::post('subcate/getByCId', [SubCategoryController::class, 'getByCId']);

        Route::post('stores/getByIds', [StoresController::class, 'getById']);
        Route::post('stores/updateDetails', [StoresController::class, 'update']);

        //categories/forStore
        Route::post('orders/getStoreStatsData', [OrdersController::class, 'getStoreStatsData']);
        Route::post('orders/getStoreStatsDataWithDates', [OrdersController::class, 'getStoreStatsDataWithDates']);

        Route::post('ratings/getWithStoreId', [RatingsController::class, 'getWithStoreId']);
    });

    Route::group(['middleware' => ['driver_auth']], function () {
        Route::post('driver/byId', [DriversController::class, 'getById']);
        Route::post('password/updateUserPasswordWithEmailDriver', [ProfileController::class, 'updateUserPasswordWithEmailDriver']);
        Route::post('password/updateUserPasswordWithPhoneDriver', [ProfileController::class, 'updateUserPasswordWithPhoneDriver']);
        Route::post('password/updatePasswordFromFirebaseDriver', [ProfileController::class, 'updatePasswordFromFirebaseDriver']);
        Route::post('driver/logout', [DriversController::class, 'logout']);
        Route::post('drivers/edit_myProfile', [DriversController::class, 'update']);
        Route::post('stores/getStoreInfoFromDriver', [StoresController::class, 'getById']);
        Route::post('orders/getByIdFromDriver', [OrdersController::class, 'getByIdFromDriver']);
        Route::post('orders/getByDriverIdForApp', [OrdersController::class, 'getByDriverIdForApp']);

        Route::post('orders/updateStatusDriver', [OrdersController::class, 'updateStatusStore']);
        Route::post('noti/sendNotification', [ProfileController::class, 'sendNotification']);
        Route::post('profile/userByIdFromDriver', [ProfileController::class, 'getById']);

        Route::post('ratings/getWithDriverId', [RatingsController::class, 'getWithDriverId']);
    });


    // Public Routes

    // Payments Routes For User Public
    Route::get('payNow',[PaytmPayController::class, 'payNow']);
    Route::get('payNowWeb',[PaytmPayController::class, 'payNowWeb']);
    Route::post('paytm-callback',[PaytmPayController::class, 'paytmCallback']); 
    Route::post('paytm-webCallback',[PaytmPayController::class, 'webCallback']);
    Route::get('refundUserRequest',[PaytmPayController::class, 'refundUserRequest']);

    Route::get('success_payments',[PaymentsController::class, 'success_payments']);
    Route::get('failed_payments',[PaymentsController::class, 'failed_payments']);
    Route::get('instaMOJOWebSuccess',[PaymentsController::class, 'instaMOJOWebSuccess']);
    Route::get('payments/payPalPay', [PaymentsController::class, 'payPalPay']);
    Route::get('payments/razorPay', [PaymentsController::class, 'razorPay']);
    Route::get('payments/VerifyRazorPurchase', [PaymentsController::class, 'VerifyRazorPurchase']);
    Route::post('payments/capureRazorPay', [PaymentsController::class, 'capureRazorPay']);
    Route::post('payments/instamojoPay', [PaymentsController::class, 'instamojoPay']);
    Route::get('payments/flutterwavePay', [PaymentsController::class, 'flutterwavePay']);
    Route::get('payments/paystackPay', [PaymentsController::class, 'paystackPay']);
    Route::get('payments/payKunPay', [PaymentsController::class, 'payKunPay']);


    // Languages Roues
    Route::get('languages/getLanguages', [LanguagesController::class, 'getLanguages']);

    // Pages Routes
    Route::post('pages/getContent', [PagesController::class, 'getById']);

    // Setting Routes
    Route::get('settings/getDefault', [SettingsController::class, 'getDefault']);
    Route::post('settings/getByLanguageId', [SettingsController::class, 'getByLanguageId']);

    Route::get('settings/getDefaultWeb', [SettingsController::class, 'getDefaultWeb']);
    Route::post('settings/getByLanguageIdWeb', [SettingsController::class, 'getByLanguageIdWeb']);

    Route::post('home/searchWithCity', [ProductsController::class, 'searchWithCity']);
    Route::post('home/testProduct', [ProductsController::class, 'getProductInStoreViaCountrie']);
    Route::post('home/searchWithZipCode', [ProductsController::class, 'searchWithZipCode']);
    Route::post('home/searchWithGeoLocation', [ProductsController::class, 'searchWithGeoLocation']);


    Route::post('home/searchStoreWithCity', [ProductsController::class, 'searchStoreWithCity']);
    Route::post('home/searchStoreWithZipCode', [ProductsController::class, 'searchStoreWithZipCode']);
    Route::post('home/searchStoreWithGeoLocation', [ProductsController::class, 'searchStoreWithGeoLocation']);

    Route::post('home/getProductsWithCity', [ProductsController::class, 'getProductsWithCity']);
    Route::post('home/getProductsWithZipCodes', [ProductsController::class, 'getProductsWithZipCodes']);
    Route::post('home/getProductsWithLocation', [ProductsController::class, 'getProductsWithLocation']);

    Route::post('home/getTopRateProductsWithCity', [ProductsController::class, 'getTopRateProductsWithCity']);
    Route::post('home/getTopRateProductsWithZipcodes', [ProductsController::class, 'getTopRateProductsWithZipcodes']);
    Route::post('home/getTopRateProductsWithLocation', [ProductsController::class, 'getTopRateProductsWithLocation']);

    Route::post('home/getOffersProductsWithCity', [ProductsController::class, 'getOffersProductsWithCity']);
    Route::post('home/getOffersProductsWithLocation', [ProductsController::class, 'getOffersProductsWithLocation']);
    Route::post('home/getOffersProductsWithZipcodes', [ProductsController::class, 'getOffersProductsWithZipcodes']);

    Route::post('products/searchQuery', [ProductsController::class, 'searchQuery']);
    Route::post('products/getWithSubCategory', [ProductsController::class, 'getWithSubCategory']);
    Route::post('products/getWithSubCategoryId', [ProductsController::class, 'getWithSubCategoryId']);
    Route::post('products/getById', [ProductsController::class, 'getById']);
    Route::post('products/getTopRated', [ProductsController::class, 'getTopRated']);
    Route::post('products/getByStoreId', [ProductsController::class, 'getByStoreId']);

    Route::get('category/getHome', [CategoryController::class, 'getHome']);
    Route::post('banners/userBanners', [BannersController::class, 'userBanners']);
    Route::post('subCategories/getFromCateId', [SubCategoryController::class, 'getFromCateId']);

    Route::post('verifyPhoneSignup', [ProfileController::class, 'verifyPhoneSignup']);
    Route::post('sendVerificationOnMail', [ProfileController::class, 'sendVerificationOnMail']);
    Route::post('otp/verifyOTP',[OtpController::class, 'verifyOTP'] );
    Route::post('otp/verifyOTPReset',[OtpController::class, 'verifyOTPReset'] );
    Route::post('otp/verifyOTPResetDriver',[OtpController::class, 'verifyOTPResetDriver'] );
    Route::get('success_verified',[AuthController::class, 'success_verified']);
    Route::post('otp/verifyPhone',[OtpController::class, 'verifyPhone'] );
    Route::post('otp/verifyPhoneNew',[OtpController::class, 'verifyPhoneNew'] );
    Route::post('otp/verifyPhoneDriver',[OtpController::class, 'verifyPhoneDriver'] );
    Route::post('otp/verifyPhoneDriverNew',[OtpController::class, 'verifyPhoneDriverNew'] );
    Route::post('otp/generateTempToken',[OtpController::class, 'generateTempToken'] );
    Route::post('otp/generateTempTokenEmail',[OtpController::class, 'generateTempTokenEmail'] );
    Route::post('otp/generateTempTokenDriver',[OtpController::class, 'generateTempTokenDriver'] );

    Route::post('contacts/create',[ContactsController::class, 'save'] ); 
    Route::post('sendMailToAdmin',[ContactsController::class, 'sendMailToAdmin']); 
    Route::get('offers/getMyOffers',[OffersController::class, 'getMyOffers'] );

    // Store register routes
    Route::post('join_store/checkEmail', [StoreRequestController::class, 'checkEmail']);
    Route::post('join_store/saveStore', [StoreRequestController::class, 'save']);
    Route::post('join_store/thankyouReply', [StoreRequestController::class, 'thankyouReply']);

    Route::get('orders/printInvoice', [OrdersController::class, 'printInvoice']);
    Route::get('orders/printStoreInvoice', [OrdersController::class, 'printStoreInvoice']);

    Route::post('join_driver/checkEmail', [DriverRequestController::class, 'checkEmail']);
    Route::post('join_driver/saveDriver', [DriverRequestController::class, 'save']);

    Route::post('ratings/getProductsRatings', [RatingsController::class, 'getProductsRatings']);
});