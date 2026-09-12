<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\ShopRegistrationController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;
use App\Http\Controllers\Admin\ApparelCategoryController;
use App\Http\Controllers\Admin\ShopBranchController;

Route::post('/shop-registrations', [ShopRegistrationController::class, 'store']);
Route::get('/plans', [SubscriptionPlanController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/support-tickets', [SupportTicketController::class, 'store']);
    Route::get('/support-tickets/mine', [SupportTicketController::class, 'mine']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user,
    ]);
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/active-shops', [AdminController::class, 'activeShops']);
    Route::get('/subscription-report', [AdminController::class, 'subscriptionReport']);
    Route::patch('/shops/{shop}/visibility', [AdminController::class, 'toggleShopVisibility']);
    Route::get('/shops/pending', [AdminController::class, 'pendingShops']);
    Route::get('/shops', [AdminController::class, 'shopDirectory']);
    Route::patch('/shops/{shop}/status', [AdminController::class, 'updateShopStatus']);
    Route::post('/shops/{shop}/approve', [AdminController::class, 'approveShop']);
    Route::post('/shops/{shop}/reject', [AdminController::class, 'rejectShop']);
    Route::post('/shop-registrations/{registration}/approve', [AdminController::class, 'approveRegistration']);
    Route::post('/shop-registrations/{registration}/reject', [AdminController::class, 'rejectRegistration']);
    Route::get('/audit-logs', [AdminController::class, 'auditLogs']);
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::patch('/accounts/{user}', [AccountController::class, 'update']);
    Route::get('/support-tickets', [AdminSupportTicketController::class, 'index']);
    Route::patch('/support-tickets/{ticket}', [AdminSupportTicketController::class, 'update']);
    Route::get('/apparel-categories', [ApparelCategoryController::class, 'index']);
    Route::patch('/apparel-categories/{category}', [ApparelCategoryController::class, 'update']);
    Route::get('/shop-branches', [ShopBranchController::class, 'index']);
    Route::patch('/shop-branches/{branch}', [ShopBranchController::class, 'update']);
    Route::apiResource('subscription-plans', SubscriptionPlanController::class);
});