<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): Response
    {
        $query = Order::with('customer');

        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('order_status', $status);
            }
        }

        if ($paymentStatus = $request->input('payment_status')) {
            if ($paymentStatus !== 'all') {
                $query->where('payment_status', $paymentStatus);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('delivery_address', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('admin/orders/index', [
            'orders' => [
                'data' => OrderResource::collection($orders->items())->resolve(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
            'filters' => [
                'status' => $request->input('status', 'all'),
                'payment_status' => $request->input('payment_status', 'all'),
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,preparing,ready,out_for_delivery,completed,cancelled'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $order = Order::findOrFail($id);

        try {
            $this->orderService->updateStatus(
                $order,
                $request->input('status'),
                $request->input('comment', "Status updated to {$request->input('status')} by Admin"),
                'admin'
            );

            return back()->with('success', "Order #{$order->order_number} advanced to {$request->input('status')}.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function cancel(Request $request, string $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        try {
            $this->orderService->cancelOrder(
                $order,
                $request->input('reason', 'Cancelled by Admin Console'),
                'admin'
            );

            return back()->with('success', "Order #{$order->order_number} has been cancelled.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        $order = Order::findOrFail($id);
        $orderNumber = $order->order_number;
        $order->delete();

        return back()->with('success', "Order #{$orderNumber} deleted successfully.");
    }
}
