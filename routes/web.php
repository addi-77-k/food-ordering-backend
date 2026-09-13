<?php

use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMenuItemController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

// Admin Console Routes
Route::prefix('admin')->group(function () {
    Route::redirect('/', '/admin/dashboard');
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Orders Management
    Route::get('orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::patch('orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.status');
    Route::post('orders/{id}/cancel', [AdminOrderController::class, 'cancel'])->name('admin.orders.cancel');
    Route::delete('orders/{id}', [AdminOrderController::class, 'destroy'])->name('admin.orders.destroy');

    // Menu Items Management
    Route::get('menu-items', [AdminMenuItemController::class, 'index'])->name('admin.menu_items.index');
    Route::post('menu-items', [AdminMenuItemController::class, 'store'])->name('admin.menu_items.store');
    Route::put('menu-items/{id}', [AdminMenuItemController::class, 'update'])->name('admin.menu_items.update');
    Route::patch('menu-items/{id}/availability', [AdminMenuItemController::class, 'toggleAvailability'])->name('admin.menu_items.availability');
    Route::delete('menu-items/{id}', [AdminMenuItemController::class, 'destroy'])->name('admin.menu_items.destroy');

    // Categories Management
    Route::get('categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('categories/{id}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
    Route::patch('categories/{id}/status', [AdminCategoryController::class, 'toggleStatus'])->name('admin.categories.status');
    Route::delete('categories/{id}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');

    // Customers Management
    Route::get('customers', [AdminCustomerController::class, 'index'])->name('admin.customers.index');
    Route::post('customers', [AdminCustomerController::class, 'store'])->name('admin.customers.store');
    Route::put('customers/{id}', [AdminCustomerController::class, 'update'])->name('admin.customers.update');
    Route::delete('customers/{id}', [AdminCustomerController::class, 'destroy'])->name('admin.customers.destroy');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
