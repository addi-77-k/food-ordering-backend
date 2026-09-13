<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CategoryStoreRequest;
use App\Http\Requests\Api\V1\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\MenuItemResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class CategoryController extends BaseApiController
{
    #[OA\Get(
        path: '/categories',
        summary: 'List categories with filtering and pagination',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', description: 'Search by name or description', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', description: 'Filter by status', required: false, schema: new OA\Schema(type: 'string', enum: ['active', 'inactive'])),
            new OA\Parameter(name: 'sort', in: 'query', description: 'Sort field: name, created_at', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'order', in: 'query', description: 'Sort order', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'page', in: 'query', description: 'Page number', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Items per page (max 100)', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Categories retrieved successfully')]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $sort = $request->input('sort', 'name');
        $order = strtolower($request->input('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'created_at', 'status'];
        if (in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $order);
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 15)));
        $categories = $query->paginate($perPage);

        return $this->sendResponse(
            CategoryResource::collection($categories->items()),
            'Categories retrieved successfully',
            200,
            [
                'current_page' => $categories->currentPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
                'last_page' => $categories->lastPage(),
            ]
        );
    }

    #[OA\Post(
        path: '/categories',
        summary: 'Create a new food category',
        tags: ['Categories'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['name'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Artisan Pizzas'),
                        new OA\Property(property: 'description', type: 'string', example: 'Hand-stretched sourdough pizzas'),
                        new OA\Property(property: 'image_url', type: 'string', example: 'https://example.com/pizza.jpg'),
                        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Category created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(CategoryStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        $category = Category::create($data);

        return $this->sendResponse(new CategoryResource($category), 'Category created successfully', 201);
    }

    #[OA\Get(
        path: '/categories/{id}',
        summary: 'Get category details by ID',
        tags: ['Categories'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Category MongoDB ID', schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Category retrieved successfully'),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return $this->sendError('Category not found', [], 404);
        }

        return $this->sendResponse(new CategoryResource($category), 'Category retrieved successfully');
    }

    #[OA\Put(
        path: '/categories/{id}',
        summary: 'Update a category',
        tags: ['Categories'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'description', type: 'string'),
                        new OA\Property(property: 'image_url', type: 'string'),
                        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive']),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Category updated successfully'),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function update(CategoryUpdateRequest $request, string $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return $this->sendError('Category not found', [], 404);
        }

        $data = $request->validated();
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return $this->sendResponse(new CategoryResource($category->fresh()), 'Category updated successfully');
    }

    #[OA\Delete(
        path: '/categories/{id}',
        summary: 'Delete a category',
        tags: ['Categories'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Category deleted successfully'),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return $this->sendError('Category not found', [], 404);
        }

        $category->delete();

        return $this->sendResponse(null, 'Category deleted successfully');
    }

    #[OA\Patch(
        path: '/categories/{id}/status',
        summary: 'Toggle or set category status',
        tags: ['Categories'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Status updated successfully'),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return $this->sendError('Category not found', [], 404);
        }

        $newStatus = $request->input('status');
        if (! $newStatus) {
            $newStatus = $category->status === 'active' ? 'inactive' : 'active';
        }

        if (! in_array($newStatus, ['active', 'inactive'], true)) {
            return $this->sendError('Invalid status value.', [], 422);
        }

        $category->update(['status' => $newStatus]);

        return $this->sendResponse(new CategoryResource($category->fresh()), "Category status changed to {$newStatus}");
    }

    #[OA\Get(
        path: '/categories/{id}/menu-items',
        summary: 'Get all menu items in a category',
        tags: ['Categories'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Category menu items retrieved'),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function getMenuItems(string $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return $this->sendError('Category not found', [], 404);
        }

        $items = $category->menuItems()->get();

        return $this->sendResponse(MenuItemResource::collection($items), 'Category menu items retrieved successfully');
    }
}
