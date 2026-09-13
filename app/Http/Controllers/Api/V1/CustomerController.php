<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CustomerStoreRequest;
use App\Http\Requests\Api\V1\CustomerUpdateRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class CustomerController extends BaseApiController
{
    #[OA\Get(
        path: '/customers',
        summary: 'List customers with filtering, search and pagination',
        tags: ['Customers'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', description: 'Search by name, email, or phone', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', description: 'Filter by status: active, inactive, suspended', schema: new OA\Schema(type: 'string', enum: ['active', 'inactive', 'suspended'])),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Customers retrieved successfully')]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $query->orderBy('created_at', 'desc');

        $perPage = min(100, max(1, (int) $request->input('per_page', 15)));
        $customers = $query->paginate($perPage);

        return $this->sendResponse(
            CustomerResource::collection($customers->items()),
            'Customers retrieved successfully',
            200,
            [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ]
        );
    }

    #[OA\Post(
        path: '/customers',
        summary: 'Create a new customer (Admin use)',
        tags: ['Customers'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['name', 'email', 'password'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Alice Smith'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'alice@example.com'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                        new OA\Property(property: 'phone', type: 'string', example: '+15551234567'),
                        new OA\Property(property: 'address', type: 'string', example: '742 Evergreen Terrace'),
                        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'suspended']),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Customer created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(CustomerStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $existing = Customer::where('email', $data['email'])->first();
        if ($existing) {
            return $this->sendError('Validation failed', ['email' => ['The email has already been taken.']], 422);
        }

        $data['password'] = Hash::make($data['password']);
        $customer = Customer::create($data);

        return $this->sendResponse(new CustomerResource($customer), 'Customer created successfully', 201);
    }

    #[OA\Get(
        path: '/customers/{id}',
        summary: 'Get customer details by ID',
        tags: ['Customers'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Customer retrieved successfully'),
            new OA\Response(response: 404, description: 'Customer not found'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return $this->sendError('Customer not found', [], 404);
        }

        return $this->sendResponse(new CustomerResource($customer), 'Customer retrieved successfully');
    }

    #[OA\Put(
        path: '/customers/{id}',
        summary: 'Update an existing customer',
        tags: ['Customers'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'email', type: 'string', format: 'email'),
                        new OA\Property(property: 'phone', type: 'string'),
                        new OA\Property(property: 'address', type: 'string'),
                        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'suspended']),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Customer updated successfully'),
            new OA\Response(response: 404, description: 'Customer not found'),
        ]
    )]
    public function update(CustomerUpdateRequest $request, string $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return $this->sendError('Customer not found', [], 404);
        }

        $data = $request->validated();
        if (isset($data['email']) && $data['email'] !== $customer->email) {
            $existing = Customer::where('email', $data['email'])->where('_id', '!=', $customer->id)->first();
            if ($existing) {
                return $this->sendError('Validation error', ['email' => ['The email has already been taken.']], 422);
            }
        }

        $customer->update($data);

        return $this->sendResponse(new CustomerResource($customer->fresh()), 'Customer updated successfully');
    }

    #[OA\Delete(
        path: '/customers/{id}',
        summary: 'Delete a customer',
        tags: ['Customers'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Customer deleted successfully'),
            new OA\Response(response: 404, description: 'Customer not found'),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return $this->sendError('Customer not found', [], 404);
        }

        $customer->delete();

        return $this->sendResponse(null, 'Customer deleted successfully');
    }

    #[OA\Get(
        path: '/customers/{id}/orders',
        summary: 'Get all orders placed by a specific customer',
        tags: ['Customers'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Customer orders list'),
            new OA\Response(response: 404, description: 'Customer not found'),
        ]
    )]
    public function getOrders(string $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return $this->sendError('Customer not found', [], 404);
        }

        $orders = $customer->orders()->orderBy('created_at', 'desc')->get();

        return $this->sendResponse(OrderResource::collection($orders), 'Customer orders retrieved successfully');
    }
}
