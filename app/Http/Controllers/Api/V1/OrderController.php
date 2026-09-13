<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\OrderCancelRequest;
use App\Http\Requests\Api\V1\OrderStatusUpdateRequest;
use App\Http\Requests\Api\V1\OrderStoreRequest;
use App\Http\Requests\Api\V1\OrderUpdateRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class OrderController extends BaseApiController
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    #[OA\Get(
        path: '/orders',
        summary: 'List food orders with filtering and pagination',
        tags: ['Orders'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'completed', 'cancelled'])),
            new OA\Parameter(name: 'payment_status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['pending', 'paid', 'failed', 'refunded'])),
            new OA\Parameter(name: 'customer_id', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'order', in: 'query', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Orders retrieved successfully')]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Order::with('customer');

        if ($status = $request->input('status')) {
            $query->where('order_status', $status);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        $sort = $request->input('sort', 'created_at');
        $order = strtolower($request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['created_at', 'total', 'order_number', 'order_status'];
        if (in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $order);
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 15)));
        $orders = $query->paginate($perPage);

        return $this->sendResponse(
            OrderResource::collection($orders->items()),
            'Orders retrieved successfully',
            200,
            [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ]
        );
    }

    #[OA\Post(
        path: '/orders',
        summary: 'Place a new food order with item snapshotting',
        tags: ['Orders'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['delivery_address', 'items'],
                    properties: [
                        new OA\Property(property: 'customer_id', type: 'string'),
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(properties: [
                                new OA\Property(property: 'menu_item_id', type: 'string'),
                                new OA\Property(property: 'quantity', type: 'integer', example: 2),
                            ])
                        ),
                        new OA\Property(property: 'delivery_address', type: 'string', example: 'Unit 12B, Salcedo Village, Makati City'),
                        new OA\Property(property: 'delivery_fee', type: 'number', format: 'float', example: 49.00),
                        new OA\Property(property: 'discount', type: 'number', format: 'float', example: 0.00),
                        new OA\Property(property: 'payment_method', type: 'string', enum: ['cash_on_delivery', 'card', 'online'], example: 'cash_on_delivery'),
                        new OA\Property(property: 'notes', type: 'string', example: 'Paki-iwan po sa may lobby guard, salamat po.'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Order placed successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(OrderStoreRequest $request): JsonResponse
    {
        $customer = $request->user();

        try {
            $order = $this->orderService->createOrder($request->validated(), $customer);

            return $this->sendResponse(new OrderResource($order->load('customer')), 'Order placed successfully', 201);
        } catch (ValidationException $e) {
            return $this->sendError($e->getMessage(), $e->errors(), 422);
        } catch (\Throwable $e) {
            return $this->sendError('Failed to create order: '.$e->getMessage(), [], 500);
        }
    }

    #[OA\Get(
        path: '/orders/{id}',
        summary: 'Get full order details with status history and item snapshots',
        tags: ['Orders'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Order details retrieved'),
            new OA\Response(response: 404, description: 'Order not found'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $order = Order::with('customer')->find($id);

        if (! $order) {
            return $this->sendError('Order not found', [], 404);
        }

        return $this->sendResponse(new OrderResource($order), 'Order retrieved successfully');
    }

    #[OA\Put(
        path: '/orders/{id}',
        summary: 'Update order metadata (delivery address, notes, payment status)',
        tags: ['Orders'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'delivery_address', type: 'string'),
                        new OA\Property(property: 'payment_status', type: 'string', enum: ['pending', 'paid', 'failed', 'refunded']),
                        new OA\Property(property: 'notes', type: 'string'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Order updated successfully'),
            new OA\Response(response: 404, description: 'Order not found'),
        ]
    )]
    public function update(OrderUpdateRequest $request, string $id): JsonResponse
    {
        $order = Order::find($id);

        if (! $order) {
            return $this->sendError('Order not found', [], 404);
        }

        $order->update($request->validated());

        return $this->sendResponse(new OrderResource($order->fresh('customer')), 'Order updated successfully');
    }

    #[OA\Patch(
        path: '/orders/{id}/status',
        summary: 'Advance order lifecycle status with state-machine validation',
        tags: ['Orders'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['status'],
                    properties: [
                        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'completed', 'cancelled'], example: 'confirmed'),
                        new OA\Property(property: 'comment', type: 'string', example: 'Kitchen started preparing order'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Order status updated successfully'),
            new OA\Response(response: 422, description: 'Invalid lifecycle transition'),
            new OA\Response(response: 404, description: 'Order not found'),
        ]
    )]
    public function updateStatus(OrderStatusUpdateRequest $request, string $id): JsonResponse
    {
        $order = Order::find($id);

        if (! $order) {
            return $this->sendError('Order not found', [], 404);
        }

        try {
            $updated = $this->orderService->updateStatus(
                $order,
                $request->validated('status'),
                $request->validated('comment'),
                'api_admin'
            );

            return $this->sendResponse(new OrderResource($updated->load('customer')), 'Order status advanced successfully');
        } catch (ValidationException $e) {
            return $this->sendError($e->getMessage(), $e->errors(), 422);
        }
    }

    #[OA\Post(
        path: '/orders/{id}/cancel',
        summary: 'Cancel order if not in terminal or out-for-delivery state',
        tags: ['Orders'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [new OA\Property(property: 'reason', type: 'string', example: 'Customer requested cancellation')]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Order cancelled successfully'),
            new OA\Response(response: 422, description: 'Cannot cancel order in current state'),
            new OA\Response(response: 404, description: 'Order not found'),
        ]
    )]
    public function cancel(OrderCancelRequest $request, string $id): JsonResponse
    {
        $order = Order::find($id);

        if (! $order) {
            return $this->sendError('Order not found', [], 404);
        }

        try {
            $cancelled = $this->orderService->cancelOrder(
                $order,
                $request->input('reason', 'Cancelled via API'),
                'customer'
            );

            return $this->sendResponse(new OrderResource($cancelled->load('customer')), 'Order cancelled successfully');
        } catch (ValidationException $e) {
            return $this->sendError($e->getMessage(), $e->errors(), 422);
        }
    }

    #[OA\Delete(
        path: '/orders/{id}',
        summary: 'Delete an order (Admin maintenance)',
        tags: ['Orders'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Order deleted successfully'),
            new OA\Response(response: 404, description: 'Order not found'),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $order = Order::find($id);

        if (! $order) {
            return $this->sendError('Order not found', [], 404);
        }

        $order->delete();

        return $this->sendResponse(null, 'Order deleted successfully');
    }
}
