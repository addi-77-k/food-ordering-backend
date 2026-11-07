<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use Inertia\Testing\AssertableInertia as Assert;

test('admin dashboard overview renders with live statistics and recent orders', function () {
    $response = $this->get('/admin/dashboard');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard')
            ->has('statistics')
            ->has('recentOrders')
            ->has('salesSummary')
        );
});

test('admin can access orders management page', function () {
    $response = $this->get('/admin/orders');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/orders/index')
            ->has('orders.data')
            ->has('filters')
        );
});

test('admin can advance order status through state machine', function () {
    $order = Order::create([
        'order_number' => 'ORD-TEST-ADMIN-01',
        'items' => [],
        'subtotal' => 20.00,
        'total' => 20.00,
        'order_status' => 'pending',
    ]);

    $response = $this->patch("/admin/orders/{$order->id}/status", [
        'status' => 'confirmed',
        'comment' => 'Confirmed by manager',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(Order::find($order->id)->order_status)->toBe('confirmed');
});

test('admin can cancel an active order', function () {
    $order = Order::create([
        'order_number' => 'ORD-TEST-ADMIN-CANCEL',
        'items' => [],
        'subtotal' => 25.00,
        'total' => 25.00,
        'order_status' => 'pending',
    ]);

    $response = $this->post("/admin/orders/{$order->id}/cancel", [
        'reason' => 'Admin canceled due to stock issue',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(Order::find($order->id)->order_status)->toBe('cancelled');
});

test('admin can access menu items catalog page and toggle availability', function () {
    $category = Category::first() ?? Category::create(['name' => 'Pizza', 'slug' => 'pizza']);
    $item = MenuItem::create([
        'category_id' => (string) $category->id,
        'name' => 'Admin Test Pizza',
        'price' => 12.00,
        'availability' => true,
        'preparation_time' => 15,
    ]);

    $response = $this->get('/admin/menu-items');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/menu-items/index')
            ->has('items.data')
            ->has('categories')
        );

    // Toggle stock
    $toggleRes = $this->patch("/admin/menu-items/{$item->id}/availability");
    $toggleRes->assertRedirect();

    expect(MenuItem::find($item->id)->availability)->toBeFalse();
});

test('admin can access categories management page and create category', function () {
    $response = $this->get('/admin/categories');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/categories/index')
            ->has('categories')
        );

    $createRes = $this->post('/admin/categories', [
        'name' => 'Smoothies & Shakes',
        'description' => 'Cold blended fruit beverages',
        'status' => 'active',
    ]);

    $createRes->assertRedirect();
    $createRes->assertSessionHas('success');

    expect(Category::where('slug', 'smoothies-shakes')->exists())->toBeTrue();
});

test('admin can access customer directory and view profiles', function () {
    $response = $this->get('/admin/customers');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/customers/index')
            ->has('customers.data')
            ->has('filters')
        );
});
