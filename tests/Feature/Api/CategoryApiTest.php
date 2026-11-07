<?php

use App\Models\Category;
use App\Models\MenuItem;

beforeEach(function () {
    Category::where('name', 'like', '%TestCat%')->delete();
});

test('can list categories with pagination', function () {
    $response = $this->getJson('/api/v1/categories');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Categories retrieved successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'description', 'status', 'created_at'],
            ],
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
        ]);
});

test('can search and filter categories', function () {
    Category::create([
        'name' => 'TestCat Salads',
        'slug' => 'testcat-salads',
        'description' => 'Healthy greens',
        'status' => 'active',
    ]);

    Category::create([
        'name' => 'TestCat Drinks',
        'slug' => 'testcat-drinks',
        'description' => 'Cold beverages',
        'status' => 'inactive',
    ]);

    $res = $this->getJson('/api/v1/categories?search=Salads');
    $res->assertOk();
    expect(count($res->json('data')))->toBeGreaterThanOrEqual(1);

    $statusRes = $this->getJson('/api/v1/categories?search=TestCat&status=inactive');
    $statusRes->assertOk();
    expect(count($statusRes->json('data')))->toBe(1)
        ->and($statusRes->json('data.0.status'))->toBe('inactive');
});

test('can create a new category', function () {
    $payload = [
        'name' => 'TestCat Gelato',
        'description' => 'Authentic Italian iced desserts',
        'image_url' => 'https://example.com/gelato.jpg',
        'status' => 'active',
    ];

    $response = $this->postJson('/api/v1/categories', $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'TestCat Gelato',
                'slug' => 'testcat-gelato',
                'description' => 'Authentic Italian iced desserts',
            ],
        ]);
});

test('can get category details by id', function () {
    $category = Category::create([
        'name' => 'TestCat Detail',
        'slug' => 'testcat-detail',
        'description' => 'Details here',
        'status' => 'active',
    ]);

    $response = $this->getJson("/api/v1/categories/{$category->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => (string) $category->id,
                'name' => 'TestCat Detail',
            ],
        ]);
});

test('can update a category', function () {
    $category = Category::create([
        'name' => 'TestCat Old',
        'slug' => 'testcat-old',
        'description' => 'Old description',
    ]);

    $response = $this->putJson("/api/v1/categories/{$category->id}", [
        'name' => 'TestCat New',
        'description' => 'Updated description',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'TestCat New',
                'slug' => 'testcat-new',
                'description' => 'Updated description',
            ],
        ]);
});

test('can update category status', function () {
    $category = Category::create([
        'name' => 'TestCat Status',
        'slug' => 'testcat-status',
        'status' => 'active',
    ]);

    $response = $this->patchJson("/api/v1/categories/{$category->id}/status", [
        'status' => 'inactive',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'status' => 'inactive',
            ],
        ]);
});

test('can get menu items in a category', function () {
    $category = Category::create([
        'name' => 'TestCat Pasta',
        'slug' => 'testcat-pasta',
    ]);

    MenuItem::create([
        'category_id' => (string) $category->id,
        'name' => 'Test Pasta Carbonara',
        'price' => 15.50,
        'availability' => true,
    ]);

    $response = $this->getJson("/api/v1/categories/{$category->id}/menu-items");

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonCount(1, 'data');
});

test('can delete a category', function () {
    $category = Category::create([
        'name' => 'TestCat Delete',
        'slug' => 'testcat-delete',
    ]);

    $response = $this->deleteJson("/api/v1/categories/{$category->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);

    expect(Category::find($category->id))->toBeNull();
});
