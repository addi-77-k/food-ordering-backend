<?php

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    // Clean test customers
    Customer::where('email', 'like', '%test%')->delete();
});

test('a customer can register successfully', function () {
    $payload = [
        'name' => 'Test Customer',
        'email' => 'test_customer_reg@example.com',
        'password' => 'secret1234',
        'password_confirmation' => 'secret1234',
        'phone' => '+15550001111',
        'address' => '100 Test Blvd, Suite 1',
    ];

    $response = $this->postJson('/api/v1/auth/register', $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Customer registered successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                'customer' => ['id', 'name', 'email', 'phone', 'address', 'status'],
                'token',
                'token_type',
            ],
        ]);

    expect(Customer::where('email', 'test_customer_reg@example.com')->exists())->toBeTrue();
});

test('registration fails with duplicate email', function () {
    Customer::create([
        'name' => 'Existing User',
        'email' => 'test_dup@example.com',
        'password' => Hash::make('secret1234'),
    ]);

    $payload = [
        'name' => 'Duplicate User',
        'email' => 'test_dup@example.com',
        'password' => 'secret1234',
        'password_confirmation' => 'secret1234',
    ];

    $response = $this->postJson('/api/v1/auth/register', $payload);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('customer can login with valid credentials and receive bearer token', function () {
    Customer::create([
        'name' => 'Login Test',
        'email' => 'test_login@example.com',
        'password' => Hash::make('mypassword123'),
        'status' => 'active',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test_login@example.com',
        'password' => 'mypassword123',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Login successful',
        ])
        ->assertJsonStructure([
            'data' => ['customer', 'token', 'token_type'],
        ]);
});

test('login fails with invalid credentials', function () {
    Customer::create([
        'name' => 'Login Test',
        'email' => 'test_login_bad@example.com',
        'password' => Hash::make('correctpassword'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test_login_bad@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid email or password',
        ]);
});

test('authenticated customer can fetch their profile', function () {
    $customer = Customer::create([
        'name' => 'Profile User',
        'email' => 'test_profile@example.com',
        'password' => Hash::make('secret1234'),
        'phone' => '+15559998888',
    ]);

    $token = $customer->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/me');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Profile User',
                'email' => 'test_profile@example.com',
            ],
        ]);
});

test('authenticated customer can update profile details', function () {
    $customer = Customer::create([
        'name' => 'Original Name',
        'email' => 'test_update@example.com',
        'password' => Hash::make('secret1234'),
    ]);

    $token = $customer->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/v1/auth/profile', [
            'name' => 'Updated Name',
            'phone' => '+15558887777',
            'address' => '500 New Street',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Updated Name',
                'phone' => '+15558887777',
                'address' => '500 New Street',
            ],
        ]);
});

test('authenticated customer can change password', function () {
    $customer = Customer::create([
        'name' => 'Password User',
        'email' => 'test_pwd@example.com',
        'password' => Hash::make('oldpassword123'),
    ]);

    $token = $customer->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/v1/auth/password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);

    $customer->refresh();
    expect(Hash::check('newpassword123', $customer->password))->toBeTrue();
});

test('customer can refresh token and logout', function () {
    $customer = Customer::create([
        'name' => 'Token User',
        'email' => 'test_token@example.com',
        'password' => Hash::make('secret1234'),
    ]);

    $token = $customer->createToken('test')->plainTextToken;

    // Refresh token
    $refreshRes = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/refresh');

    $refreshRes->assertOk()
        ->assertJsonStructure([
            'data' => ['token', 'token_type'],
        ]);

    $newToken = $refreshRes->json('data.token');

    // Logout using new token
    $logoutRes = $this->withHeader('Authorization', "Bearer {$newToken}")
        ->postJson('/api/v1/auth/logout');

    $logoutRes->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
});
