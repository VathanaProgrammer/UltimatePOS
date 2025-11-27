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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/product/all', [ProductController::class, "all"])->name('api.product.all');
Route::get("/category/all", [CategoryController::class, "all"])->name('api.category.all');
Route::get('/product/reward/all', [ApiRewardController::class, 'getData']);

Route::post("/telegram/webhook", [TelegramBotWebhookController::class, "webhook"])->name('webhook');

Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);

    Route::post('/api_logout', [AuthController::class, 'api.logout']);

    Route::post('/store-order', [OrderController::class, 'store']);

    Route::get('/addresses/all', [AddressController::class, 'show']);
    Route::post('/addresses', [AddressController::class, 'store']);
});

// Collector Auth Routes
Route::post('/collector/register', [CollectorAuthController::class, 'register']);
Route::post('/collector/login', [CollectorAuthController::class, 'login']); 
Route::get('/collector/index', [CollectorAuthController::class, 'index']); 
Route::post('/collector/save', [CustomerController::class, 'store']);
Route::get('/collector/user', [CustomerController::class, 'user']);

// Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
//     Route::get('/collector/user', [CollectorAuthController::class, 'user']);
//     Route::post('/collector/logout', [CollectorAuthController::class, 'logout']);
// });