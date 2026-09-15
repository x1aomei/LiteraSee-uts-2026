<?php
// routes/api.php

use App\Http\Controllers\Api\Admin\BookController as AdminBookController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\MidtransNotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - LiteraSee Backend
|--------------------------------------------------------------------------
*/

// ============================================
// AUTH (Public, dengan rate limit)
// ============================================
Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    });
});

// ============================================
// PUBLIC CATALOG
// ============================================
Route::get('/home', [CatalogController::class, 'home']);
Route::get('/catalog', [CatalogController::class, 'index']);
Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/books/{slug}', [CatalogController::class, 'show']);

// ============================================
// CART (Guest + Auth)
// ============================================
Route::prefix('cart')->group(function () {
    Route::get('/session', [CartController::class, 'session']);
    Route::get('/summary', [CartController::class, 'summary']);
    Route::get('/', [CartController::class, 'index']);
    Route::post('/add', [CartController::class, 'add']);
    Route::patch('/{itemId}', [CartController::class, 'update']);
    Route::delete('/{itemId}', [CartController::class, 'remove']);
});

// ============================================
// MIDTRANS WEBHOOK (Public, no auth)
// ============================================
Route::post('/midtrans/notification', [MidtransNotificationController::class, 'handle']);

// ============================================
// AUTHENTICATED (Customer)
// ============================================
Route::middleware('auth:sanctum')->group(function () {

    // ----- Profile -----
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);

    // ----- Wishlist -----
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/toggle/{book}', [WishlistController::class, 'toggle']);
        Route::delete('/{book}', [WishlistController::class, 'destroy']);
    });

    // ----- Checkout -----
    Route::get('/checkout', [CheckoutController::class, 'index']);
    Route::post('/checkout', [CheckoutController::class, 'store']);

    // ----- Orders (customer) -----
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{order}', [OrderController::class, 'show']);
        Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
    });

    // ----- Payments -----
    Route::post('/payments/{order}/snap-token', [PaymentController::class, 'getSnapToken']);
    Route::post('/payments/{order}/check-status', [PaymentController::class, 'checkStatus']);
});

// ============================================
// ADMIN (Auth + Admin Middleware)
// ============================================
Route::middleware(['auth:sanctum', 'admin'])
    ->prefix('admin')
    ->group(function () {

        // ----- Dashboard -----
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);

        // ----- Books (CRUD) -----
        Route::apiResource('books', AdminBookController::class);
        Route::delete('books/{book}/images/{image}', [AdminBookController::class, 'deleteImage']);
        Route::patch('books/{book}/images/{image}/primary', [AdminBookController::class, 'setPrimaryImage']);

        // ----- Categories (CRUD) -----
        Route::apiResource('categories', AdminCategoryController::class);

        // ----- Orders -----
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);

        // ----- Reports -----
        Route::get('/reports/sales', [AdminReportController::class, 'sales']);
        Route::get('/reports/export-sales', [AdminReportController::class, 'exportSales']);

        // ----- Users Management -----
        // PENTING: /top-buyers HARUS di atas /{user} biar ngga dianggap ID
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/top-buyers', [AdminUserController::class, 'topBuyers']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole']);
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);
    });