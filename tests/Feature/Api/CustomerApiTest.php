<?php

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    Customer::where('email', 'like', '%testcust%')->delete();
});

test('can list customers with pagination and search', function () {
    $response = $this->getJson('/api/v1/customers');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Customers retrieved successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'status', 'created_at'],
            ],
            'meta',
        ]);
});

test('can create a customer through admin endpoint', function () {
    $payload = [
        'name' => 'Admin Created Customer',
        'email' => 'testcust_admin@example.com',
        'password' => 'secret1234',
        'phone' => '+15554443333',
        'address' => '99 Admin Way',
        'status' => 'active',
    ];

    $response = $this->postJson('/api/v1/customers', $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Admin Created Customer',
                'email' => 'testcust_admin@example.com',
                'status' => 'active',
            ],
        ]);
});

test('can show a customer by id', function () {
    $customer = Customer::create([
        'name' => 'Show Customer',
        'email' => 'testcust_show@example.com',
        'password' => Hash::make('secret1234'),
    ]);

    $response = $this->getJson("/api/v1/customers/{$customer->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => (string) $customer->id,
                'email' => 'testcust_show@example.com',
            ],
        ]);
});

test('can update an existing customer', function () {
    $customer = Customer::create([
        'name' => 'Before Update',
        'email' => 'testcust_edit@example.com',
        'password' => Hash::make('secret1234'),
        'status' => 'active',
    ]);

    $response = $this->putJson("/api/v1/customers/{$customer->id}", [
        'name' => 'After Update',
        'status' => 'suspended',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'After Update',
                'status' => 'suspended',
            ],
        ]);
});

test('can get all orders belonging to a customer', function () {
    $customer = Customer::create([
        'name' => 'Order History Customer',
        'email' => 'testcust_orders@example.com',
        'password' => Hash::make('secret1234'),
    ]);

    Order::create([
        'customer_id' => (string) $customer->id,
        'order_number' => 'ORD-TEST-9999',
        'items' => [
            ['name' => 'Test Pizza', 'quantity' => 1, 'unit_price' => 12.00, 'subtotal' => 12.00],
        ],
        'subtotal' => 12.00,
        'delivery_fee' => 3.00,
        'total' => 15.00,
        'order_status' => 'completed',
    ]);

    $response = $this->getJson("/api/v1/customers/{$customer->id}/orders");

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonCount(1, 'data');
});

test('can delete a customer', function () {
    $customer = Customer::create([
        'name' => 'Delete Customer',
        'email' => 'testcust_del@example.com',
        'password' => Hash::make('secret1234'),
    ]);

    $response = $this->deleteJson("/api/v1/customers/{$customer->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Customer deleted successfully',
        ]);

    expect(Customer::find($customer->id))->toBeNull();
});
