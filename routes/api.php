<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\CollectorAuthController;
use App\Http\Controllers\Api\ApiRewardController;
use App\Http\Controllers\Api\TelegramBotWebhookController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DeliveryAuthController;
use App\Http\Controllers\Api\DeliveryController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

Route::get('telegram-link', [TelegramController::class, 'getTelegramLink'])->name('api.telegram');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/product/all', [ProductController::class, "all"])->name('api.product.all');
Route::get("/category/all", [CategoryController::class, "all"])->name('api.category.all');
Route::get('/product/reward/all', [ApiRewardController::class, 'getData']);

Route::post("/webhook", [TelegramBotWebhookController::class, "webhook"])->name('webhook');

Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);

    Route::post('/api_logout', [AuthController::class, 'api.logout']);

    Route::post('/store-order', [OrderController::class, 'store']);

    Route::get('/addresses/all', [AddressController::class, 'show']);
    Route::post('/addresses', [AddressController::class, 'store']);
});

Route::middleware(['jwt.cookie'])->get('/collector/user', [CollectorAuthController::class, 'user']);

// Collector Auth Routes
Route::post('/collector/register', [CollectorAuthController::class, 'register']);
Route::post('/collector/login', [CollectorAuthController::class, 'login']);
Route::get('/collector/index', [CollectorAuthController::class, 'index']);
Route::post('/collector/save', [CustomerController::class, 'store']);


// Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
//     Route::get('/collector/user', [CollectorAuthController::class, 'user']);
//     Route::post('/collector/logout', [CollectorAuthController::class, 'logout']);
// });

Route::post('/delivery/login', [DeliveryAuthController::class, 'login']);
Route::post('/delivery/register', [DeliveryAuthController::class, 'register']);

Route::middleware(['jwt.delivery'])
    ->prefix('delivery')
    ->group(function () {

        Route::get('profile', [DeliveryAuthController::class, 'profile']);
        Route::post('logout', [DeliveryAuthController::class, 'logout']);

        Route::get('orders', [DeliveryController::class, 'getOrders']);
        Route::post('decrypt-qr', [DeliveryController::class, 'decryptQr']);
        Route::post('confirm-delivery', [DeliveryController::class, 'assignDeliveryPerson']);
        Route::post('save-drop-off', [DeliveryController::class, 'save']);
        Route::post('save-comment', [DeliveryController::class, 'save_comment']);
        Route::post('update-profile-pic', [DeliveryController::class, 'update_profile_pic']);
        Route::get('getMaps', [DeliveryController::class, 'getMapData']);
    });


Route::get('delivery/check-session', function () {
    return response()->json(['alive' => true]);
})->middleware('jwt.delivery'); // same middleware