<?php

use App\Models\Category;
use App\Models\MenuItem;

beforeEach(function () {
    MenuItem::where('name', 'like', '%TestItem%')->delete();
    Category::where('name', 'like', '%TestCat%')->delete();
});

test('can list menu items with pagination and filters', function () {
    $response = $this->getJson('/api/v1/menu-items');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Menu items retrieved successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'price', 'availability', 'preparation_time'],
            ],
            'meta',
        ]);
});

test('can search menu items by keyword', function () {
    $category = Category::create(['name' => 'TestCat Food', 'slug' => 'testcat-food']);

    MenuItem::create([
        'category_id' => (string) $category->id,
        'name' => 'TestItem Garlic Bread',
        'description' => 'Crispy bread with garlic butter',
        'price' => 5.99,
        'availability' => true,
    ]);

    $response = $this->getJson('/api/v1/menu-items?search=Garlic');

    $response->assertOk();
    expect(count($response->json('data')))->toBeGreaterThanOrEqual(1)
        ->and($response->json('data.0.name'))->toContain('Garlic');
});

test('can create a new menu item', function () {
    $category = Category::create(['name' => 'TestCat Sandwiches', 'slug' => 'testcat-sandwiches']);

    $payload = [
        'category_id' => (string) $category->id,
        'name' => 'TestItem Club Sandwich',
        'description' => 'Triple-decker toasted sandwich',
        'price' => 11.50,
        'image_url' => 'https://example.com/club.jpg',
        'availability' => true,
        'preparation_time' => 10,
    ];

    $response = $this->postJson('/api/v1/menu-items', $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'TestItem Club Sandwich',
                'price' => 11.50,
                'availability' => true,
            ],
        ]);
});

test('can show a menu item by id', function () {
    $category = Category::create(['name' => 'TestCat Drinks', 'slug' => 'testcat-drinks']);

    $item = MenuItem::create([
        'category_id' => (string) $category->id,
        'name' => 'TestItem Espresso',
        'price' => 3.50,
        'availability' => true,
    ]);

    $response = $this->getJson("/api/v1/menu-items/{$item->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => (string) $item->id,
                'name' => 'TestItem Espresso',
                'price' => 3.50,
            ],
        ]);
});

test('can update a menu item', function () {
    $category = Category::create(['name' => 'TestCat Bakery', 'slug' => 'testcat-bakery']);

    $item = MenuItem::create([
        'category_id' => (string) $category->id,
        'name' => 'TestItem Croissant',
        'price' => 4.00,
        'availability' => true,
    ]);

    $response = $this->putJson("/api/v1/menu-items/{$item->id}", [
        'name' => 'TestItem Almond Croissant',
        'price' => 4.75,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'TestItem Almond Croissant',
                'price' => 4.75,
            ],
        ]);
});

test('can toggle menu item availability', function () {
    $category = Category::create(['name' => 'TestCat Mains', 'slug' => 'testcat-mains']);

    $item = MenuItem::create([
        'category_id' => (string) $category->id,
        'name' => 'TestItem Steak',
        'price' => 25.00,
        'availability' => true,
    ]);

    $response = $this->patchJson("/api/v1/menu-items/{$item->id}/availability", [
        'availability' => false,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'availability' => false,
            ],
        ]);

    expect(MenuItem::find($item->id)->availability)->toBeFalse();
});

test('can delete a menu item', function () {
    $category = Category::create(['name' => 'TestCat Sides', 'slug' => 'testcat-sides']);

    $item = MenuItem::create([
        'category_id' => (string) $category->id,
        'name' => 'TestItem Fries',
        'price' => 4.50,
        'availability' => true,
    ]);

    $response = $this->deleteJson("/api/v1/menu-items/{$item->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Menu item deleted successfully',
        ]);

    expect(MenuItem::find($item->id))->toBeNull();
});
