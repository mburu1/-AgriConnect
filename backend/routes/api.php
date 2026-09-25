<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\FarmerController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\CmsController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| AgriConnect API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api (set in bootstrap/app.php)
|
| Role middleware groups:
|   auth:sanctum    → any authenticated user
|   role:ADMIN      → admin only
|   role:FARMER     → verified farmer only
|
*/

// ─── Health Check ─────────────────────────────────────────────────────
Route::get('/health', fn() => response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()]));

// ─── Authentication ────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register',         [AuthController::class, 'register']);
    Route::post('/register/farmer',  [AuthController::class, 'registerFarmer']);
    Route::post('/login',            [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout',       [AuthController::class, 'logout']);
        Route::get('/me',            [AuthController::class, 'me']);
    });
});

// ─── Public Catalogue ─────────────────────────────────────────────────
Route::get('/products',                      [ProductController::class, 'index']);
Route::get('/products/{slug}',               [ProductController::class, 'show']);
Route::get('/products/{productId}/reviews',  [ReviewController::class, 'index']);
Route::get('/categories',                    [ProductController::class, 'categories']);

// ─── Public Farmers ───────────────────────────────────────────────────
Route::get('/farmers',          [FarmerController::class, 'index']);
Route::get('/farmers/{slug}',   [FarmerController::class, 'show']);

// ─── Public CMS ───────────────────────────────────────────────────────
Route::prefix('cms')->group(function () {
    Route::get('/articles',          [CmsController::class, 'articles']);
    Route::get('/articles/{slug}',   [CmsController::class, 'article']);
    Route::get('/pages/{slug}',      [CmsController::class, 'page']);
    Route::get('/banners',           [CmsController::class, 'banners']);
    Route::get('/counties',          [CmsController::class, 'counties']);
    Route::get('/jobs',              [CmsController::class, 'jobs']);
    Route::get('/jobs/{slug}',       [CmsController::class, 'job']);
    Route::post('/jobs/{id}/apply',  [CmsController::class, 'applyForJob']);
    Route::post('/enquiries',        [CmsController::class, 'storeEnquiry']);
});

// ─── M-Pesa Callback (unauthenticated – Safaricom webhook) ────────────
Route::post('/payments/mpesa/callback', [OrderController::class, 'mpesaCallback']);

// ─── Authenticated Customer Routes ────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Cart (supports both auth & session-based via X-Session-Id header)
    Route::prefix('cart')->group(function () {
        Route::get('/',                 [CartController::class, 'show']);
        Route::post('/items',           [CartController::class, 'addItem']);
        Route::delete('/items/{id}',    [CartController::class, 'removeItem']);
        Route::delete('/',              [CartController::class, 'clear']);
        Route::post('/merge',           [CartController::class, 'merge']);
    });

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/',                     [OrderController::class, 'index']);
        Route::post('/',                    [OrderController::class, 'store']);
        Route::get('/{id}',                 [OrderController::class, 'show']);
        Route::post('/{id}/pay',            [OrderController::class, 'pay']);
        Route::post('/{id}/cancel',         [OrderController::class, 'cancel']);
    });

    // Reviews
    Route::post('/products/{productId}/reviews', [ReviewController::class, 'store']);

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/',                     [NotificationController::class, 'index']);
        Route::post('/{id}/read',           [NotificationController::class, 'markRead']);
        Route::post('/read-all',            [NotificationController::class, 'markAllRead']);
    });
});

// ─── Farmer Routes ────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:FARMER'])->prefix('farmer')->group(function () {
    Route::get('/profile',              [FarmerController::class, 'myProfile']);
    Route::put('/profile',              [FarmerController::class, 'updateProfile']);
    Route::post('/verification',        [FarmerController::class, 'submitVerification']);
    Route::post('/locations',           [FarmerController::class, 'addLocation']);

    // Products
    Route::post('/products',            [ProductController::class, 'store']);
    Route::put('/products/{id}',        [ProductController::class, 'update']);
    Route::delete('/products/{id}',     [ProductController::class, 'destroy']);

    // Farmer's own orders
    Route::get('/orders',               [OrderController::class, 'index']);
    Route::post('/orders/{id}/status',  [OrderController::class, 'updateStatus']);
});

// ─── Admin Routes ─────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:ADMIN'])->prefix('admin')->group(function () {
    // Order management
    Route::get('/orders',               [OrderController::class, 'index']);
    Route::post('/orders/{id}/status',  [OrderController::class, 'updateStatus']);
});
