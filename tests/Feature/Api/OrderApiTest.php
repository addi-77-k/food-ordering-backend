<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    Order::where('order_number', 'like', '%TEST%')->delete();
    Customer::where('email', 'like', '%testorder%')->delete();
    MenuItem::where('name', 'like', '%TestOrder%')->delete();
    Category::where('name', 'like', '%TestOrder%')->delete();
});

test('can list orders with pagination', function () {
    $response = $this->getJson('/api/v1/orders');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Orders retrieved successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'order_number', 'order_status', 'payment_status', 'total'],
            ],
            'meta',
        ]);
});

test('can place an order with snapshotted items and calculated total', function () {
    $customer = Customer::create([
        'name' => 'Order Placer',
        'email' => 'testorder_buyer@example.com',
        'password' => Hash::make('secret1234'),
        'address' => '100 Foodie Lane',
    ]);

    $category = Category::create(['name' => 'TestOrder Category', 'slug' => 'testorder-cat']);
    $item = MenuItem::create([
        'category_id' => (string) $category->id,
        'name' => 'TestOrder Supreme Pizza',
        'price' => 15.00,
        'availability' => true,
    ]);

    $payload = [
        'customer_id' => (string) $customer->id,
        'items' => [
            ['menu_item_id' => (string) $item->id, 'quantity' => 2],
        ],
        'delivery_address' => '100 Foodie Lane',
        'delivery_fee' => 5.00,
        'discount' => 2.00,
        'payment_method' => 'card',
        'notes' => 'Extra crispy please',
    ];

    $response = $this->postJson('/api/v1/orders', $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Order placed successfully',
            'data' => [
                'subtotal' => 30.00,
                'delivery_fee' => 5.00,
                'discount' => 2.00,
                'total' => 33.00,
                'order_status' => 'pending',
                'delivery_address' => '100 Foodie Lane',
            ],
        ]);

    $orderData = $response->json('data');
    expect($orderData['items'])->toHaveCount(1)
        ->and($orderData['items'][0]['name'])->toBe('TestOrder Supreme Pizza')
        ->and($orderData['items'][0]['quantity'])->toBe(2)
        ->and($orderData['items'][0]['unit_price'])->toEqual(15.00)
        ->and($orderData['items'][0]['subtotal'])->toEqual(30.00);
});

test('fails to place order when menu item is unavailable', function () {
    $customer = Customer::create([
        'name' => 'Order Placer 2',
        'email' => 'testorder_unavail@example.com',
        'password' => Hash::make('secret1234'),
    ]);

    $category = Category::create(['name' => 'TestOrder Cat 2', 'slug' => 'testorder-cat-2']);
    $item = MenuItem::create([
        'category_id' => (string) $category->id,
        'name' => 'TestOrder Sold Out Dish',
        'price' => 20.00,
        'availability' => false,
    ]);

    $response = $this->postJson('/api/v1/orders', [
        'customer_id' => (string) $customer->id,
        'items' => [
            ['menu_item_id' => (string) $item->id, 'quantity' => 1],
        ],
        'delivery_address' => '123 Any Street',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('can view order details by id', function () {
    $order = Order::create([
        'order_number' => 'ORD-TEST-SHOW-01',
        'items' => [
            ['name' => 'Sample Burger', 'quantity' => 1, 'unit_price' => 10.00, 'subtotal' => 10.00],
        ],
        'subtotal' => 10.00,
        'total' => 10.00,
        'order_status' => 'pending',
    ]);

    $response = $this->getJson("/api/v1/orders/{$order->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => (string) $order->id,
                'order_number' => 'ORD-TEST-SHOW-01',
            ],
        ]);
});

test('can advance order status along the state machine lifecycle', function () {
    $order = Order::create([
        'order_number' => 'ORD-TEST-LIFECYCLE-01',
        'items' => [],
        'subtotal' => 15.00,
        'total' => 15.00,
        'order_status' => 'pending',
    ]);

    // pending -> confirmed
    $res1 = $this->patchJson("/api/v1/orders/{$order->id}/status", [
        'status' => 'confirmed',
        'comment' => 'Restaurant confirmed order',
    ]);
    $res1->assertOk()
        ->assertJson([
            'success' => true,
            'data' => ['order_status' => 'confirmed'],
        ]);

    // confirmed -> preparing
    $res2 = $this->patchJson("/api/v1/orders/{$order->id}/status", [
        'status' => 'preparing',
    ]);
    $res2->assertOk()
        ->assertJson([
            'data' => ['order_status' => 'preparing'],
        ]);
});

test('rejects invalid order status transition', function () {
    $order = Order::create([
        'order_number' => 'ORD-TEST-INVALID-TRANS',
        'items' => [],
        'subtotal' => 20.00,
        'total' => 20.00,
        'order_status' => 'pending',
    ]);

    // pending cannot skip directly to completed
    $response = $this->patchJson("/api/v1/orders/{$order->id}/status", [
        'status' => 'completed',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('can cancel order when in cancellable status', function () {
    $order = Order::create([
        'order_number' => 'ORD-TEST-CANCEL-01',
        'items' => [],
        'subtotal' => 18.00,
        'total' => 18.00,
        'order_status' => 'pending',
    ]);

    $response = $this->postJson("/api/v1/orders/{$order->id}/cancel", [
        'reason' => 'Changed my mind',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'order_status' => 'cancelled',
            ],
        ]);

    expect(Order::find($order->id)->order_status)->toBe('cancelled');
});

test('cannot cancel order when completed or out for delivery', function () {
    $order = Order::create([
        'order_number' => 'ORD-TEST-NOCANCEL-01',
        'items' => [],
        'subtotal' => 25.00,
        'total' => 25.00,
        'order_status' => 'completed',
    ]);

    $response = $this->postJson("/api/v1/orders/{$order->id}/cancel", [
        'reason' => 'Cancel already delivered',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('can delete an order', function () {
    $order = Order::create([
        'order_number' => 'ORD-TEST-DELETE-01',
        'items' => [],
        'subtotal' => 10.00,
        'total' => 10.00,
        'order_status' => 'cancelled',
    ]);

    $response = $this->deleteJson("/api/v1/orders/{$order->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Order deleted successfully',
        ]);

    expect(Order::find($order->id))->toBeNull();
});
