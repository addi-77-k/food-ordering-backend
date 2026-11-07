<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\MenuItemController;
use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('api.auth.register');
        Route::post('login', [AuthController::class, 'login'])->name('api.auth.login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('api.auth.logout');
            Route::get('me', [AuthController::class, 'me'])->name('api.auth.me');
            Route::put('profile', [AuthController::class, 'updateProfile'])->name('api.auth.profile');
            Route::put('password', [AuthController::class, 'updatePassword'])->name('api.auth.password');
            Route::post('refresh', [AuthController::class, 'refresh'])->name('api.auth.refresh');
        });
    });

    // Categories Routes
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('api.categories.index');
        Route::post('/', [CategoryController::class, 'store'])->name('api.categories.store');
        Route::get('{id}', [CategoryController::class, 'show'])->name('api.categories.show');
        Route::put('{id}', [CategoryController::class, 'update'])->name('api.categories.update');
        Route::delete('{id}', [CategoryController::class, 'destroy'])->name('api.categories.destroy');
        Route::patch('{id}/status', [CategoryController::class, 'updateStatus'])->name('api.categories.status');
        Route::get('{id}/menu-items', [CategoryController::class, 'getMenuItems'])->name('api.categories.menu_items');
    });

    // Menu Items Routes
    Route::prefix('menu-items')->group(function () {
        Route::get('/', [MenuItemController::class, 'index'])->name('api.menu_items.index');
        Route::post('/', [MenuItemController::class, 'store'])->name('api.menu_items.store');
        Route::get('{id}', [MenuItemController::class, 'show'])->name('api.menu_items.show');
        Route::put('{id}', [MenuItemController::class, 'update'])->name('api.menu_items.update');
        Route::delete('{id}', [MenuItemController::class, 'destroy'])->name('api.menu_items.destroy');
        Route::patch('{id}/availability', [MenuItemController::class, 'updateAvailability'])->name('api.menu_items.availability');
    });

    // Customers Routes
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('api.customers.index');
        Route::post('/', [CustomerController::class, 'store'])->name('api.customers.store');
        Route::get('{id}', [CustomerController::class, 'show'])->name('api.customers.show');
        Route::put('{id}', [CustomerController::class, 'update'])->name('api.customers.update');
        Route::delete('{id}', [CustomerController::class, 'destroy'])->name('api.customers.destroy');
        Route::get('{id}/orders', [CustomerController::class, 'getOrders'])->name('api.customers.orders');
    });

    // Orders Routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('api.orders.index');
        Route::post('/', [OrderController::class, 'store'])->name('api.orders.store');
        Route::get('{id}', [OrderController::class, 'show'])->name('api.orders.show');
        Route::put('{id}', [OrderController::class, 'update'])->name('api.orders.update');
        Route::patch('{id}/status', [OrderController::class, 'updateStatus'])->name('api.orders.status');
        Route::post('{id}/cancel', [OrderController::class, 'cancel'])->name('api.orders.cancel');
        Route::delete('{id}', [OrderController::class, 'destroy'])->name('api.orders.destroy');
    });

    // Dashboard & Statistics Routes
    Route::prefix('dashboard')->group(function () {
        Route::get('statistics', [DashboardController::class, 'statistics'])->name('api.dashboard.statistics');
        Route::get('recent-orders', [DashboardController::class, 'recentOrders'])->name('api.dashboard.recent_orders');
        Route::get('sales-summary', [DashboardController::class, 'salesSummary'])->name('api.dashboard.sales_summary');
    });
});
