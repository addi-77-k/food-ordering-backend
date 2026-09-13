<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\MenuItemStoreRequest;
use App\Http\Requests\Api\V1\MenuItemUpdateRequest;
use App\Http\Resources\MenuItemResource;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MenuItemController extends BaseApiController
{
    #[OA\Get(
        path: '/menu-items',
        summary: 'List menu items with filtering, search, sorting and pagination',
        tags: ['Menu Items'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', description: 'Search by name or description', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'category_id', in: 'query', description: 'Filter by Category ID', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'available', in: 'query', description: 'Filter by availability (true/false)', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'sort', in: 'query', description: 'Sort by: price, name, preparation_time, created_at', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'order', in: 'query', description: 'Order direction', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'page', in: 'query', description: 'Page number', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Items per page', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Menu items retrieved successfully')]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = MenuItem::with('category');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->has('available')) {
            $available = filter_var($request->input('available'), FILTER_VALIDATE_BOOLEAN);
            $query->where('availability', $available);
        }

        $sort = $request->input('sort', 'name');
        $order = strtolower($request->input('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'price', 'preparation_time', 'created_at'];
        if (in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $order);
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 15)));
        $items = $query->paginate($perPage);

        return $this->sendResponse(
            MenuItemResource::collection($items->items()),
            'Menu items retrieved successfully',
            200,
            [
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'last_page' => $items->lastPage(),
            ]
        );
    }

    #[OA\Post(
        path: '/menu-items',
        summary: 'Create a new menu item',
        tags: ['Menu Items'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['category_id', 'name', 'price'],
                    properties: [
                        new OA\Property(property: 'category_id', type: 'string', example: '65e...'),
                        new OA\Property(property: 'name', type: 'string', example: 'Truffle Mushroom Burger'),
                        new OA\Property(property: 'description', type: 'string', example: 'Brioche bun with Angus beef'),
                        new OA\Property(property: 'price', type: 'number', format: 'float', example: 14.99),
                        new OA\Property(property: 'image_url', type: 'string', example: 'https://example.com/burger.jpg'),
                        new OA\Property(property: 'availability', type: 'boolean', example: true),
                        new OA\Property(property: 'preparation_time', type: 'integer', example: 20),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Menu item created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(MenuItemStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $category = Category::find($data['category_id']);
        if (! $category) {
            return $this->sendError("Category with ID '{$data['category_id']}' not found.", [], 422);
        }

        $item = MenuItem::create($data);

        return $this->sendResponse(new MenuItemResource($item->load('category')), 'Menu item created successfully', 201);
    }

    #[OA\Get(
        path: '/menu-items/{id}',
        summary: 'Get menu item details by ID',
        tags: ['Menu Items'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Menu item retrieved successfully'),
            new OA\Response(response: 404, description: 'Menu item not found'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $item = MenuItem::with('category')->find($id);

        if (! $item) {
            return $this->sendError('Menu item not found', [], 404);
        }

        return $this->sendResponse(new MenuItemResource($item), 'Menu item retrieved successfully');
    }

    #[OA\Put(
        path: '/menu-items/{id}',
        summary: 'Update an existing menu item',
        tags: ['Menu Items'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'category_id', type: 'string'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'price', type: 'number', format: 'float'),
                        new OA\Property(property: 'availability', type: 'boolean'),
                        new OA\Property(property: 'preparation_time', type: 'integer'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Menu item updated successfully'),
            new OA\Response(response: 404, description: 'Menu item not found'),
        ]
    )]
    public function update(MenuItemUpdateRequest $request, string $id): JsonResponse
    {
        $item = MenuItem::find($id);

        if (! $item) {
            return $this->sendError('Menu item not found', [], 404);
        }

        $data = $request->validated();
        if (isset($data['category_id'])) {
            $category = Category::find($data['category_id']);
            if (! $category) {
                return $this->sendError("Category with ID '{$data['category_id']}' not found.", [], 422);
            }
        }

        $item->update($data);

        return $this->sendResponse(new MenuItemResource($item->fresh('category')), 'Menu item updated successfully');
    }

    #[OA\Delete(
        path: '/menu-items/{id}',
        summary: 'Delete a menu item',
        tags: ['Menu Items'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Menu item deleted successfully'),
            new OA\Response(response: 404, description: 'Menu item not found'),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $item = MenuItem::find($id);

        if (! $item) {
            return $this->sendError('Menu item not found', [], 404);
        }

        $item->delete();

        return $this->sendResponse(null, 'Menu item deleted successfully');
    }

    #[OA\Patch(
        path: '/menu-items/{id}/availability',
        summary: 'Toggle or set menu item availability',
        tags: ['Menu Items'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Availability updated'),
            new OA\Response(response: 404, description: 'Menu item not found'),
        ]
    )]
    public function updateAvailability(Request $request, string $id): JsonResponse
    {
        $item = MenuItem::find($id);

        if (! $item) {
            return $this->sendError('Menu item not found', [], 404);
        }

        $newAvailability = $request->has('availability')
            ? filter_var($request->input('availability'), FILTER_VALIDATE_BOOLEAN)
            : ! $item->availability;

        $item->update(['availability' => $newAvailability]);

        return $this->sendResponse(
            new MenuItemResource($item->fresh('category')),
            'Menu item availability updated to ' . ($newAvailability ? 'available' : 'unavailable')
        );
    }
}
