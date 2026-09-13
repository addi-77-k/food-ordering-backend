<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Map of valid status transitions.
     */
    protected array $allowedTransitions = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['preparing', 'cancelled'],
        'preparing' => ['ready', 'cancelled'],
        'ready' => ['out_for_delivery'],
        'out_for_delivery' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    /**
     * Create a new order with snapshotted items and calculated totals.
     */
    public function createOrder(array $data, ?Customer $customer = null): Order
    {
        $customerId = $customer ? $customer->id : ($data['customer_id'] ?? null);

        if (! $customerId) {
            throw ValidationException::withMessages([
                'customer_id' => ['Customer ID is required to place an order.'],
            ]);
        }

        $itemsInput = $data['items'] ?? [];
        if (empty($itemsInput) || ! is_array($itemsInput)) {
            throw ValidationException::withMessages([
                'items' => ['Order must contain at least one item.'],
            ]);
        }

        $snapshottedItems = [];
        $subtotal = 0.0;

        foreach ($itemsInput as $index => $itemData) {
            $menuItemId = $itemData['menu_item_id'] ?? null;
            $quantity = (int) ($itemData['quantity'] ?? 1);

            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => ['Quantity must be greater than zero.'],
                ]);
            }

            $menuItem = MenuItem::find($menuItemId);

            if (! $menuItem) {
                throw ValidationException::withMessages([
                    "items.{$index}.menu_item_id" => ["Menu item '{$menuItemId}' not found."],
                ]);
            }

            if (! $menuItem->availability) {
                throw ValidationException::withMessages([
                    "items.{$index}.menu_item_id" => ["Menu item '{$menuItem->name}' is currently unavailable."],
                ]);
            }

            $unitPrice = (float) $menuItem->price;
            $lineTotal = round($unitPrice * $quantity, 2);
            $subtotal += $lineTotal;

            $snapshottedItems[] = [
                'menu_item_id' => (string) $menuItem->id,
                'name' => $menuItem->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $lineTotal,
            ];
        }

        $deliveryFee = isset($data['delivery_fee']) ? (float) $data['delivery_fee'] : 5.00;
        $discount = isset($data['discount']) ? (float) $data['discount'] : 0.00;
        $total = max(0.0, round($subtotal + $deliveryFee - $discount, 2));

        $orderNumber = 'ORD-' . strtoupper(Str::random(4)) . '-' . rand(1000, 9999);

        $initialStatus = 'pending';
        $history = [
            [
                'status' => $initialStatus,
                'comment' => $data['notes'] ?? 'Order placed successfully',
                'created_at' => now()->toIso8601String(),
                'actor' => $customer ? 'customer' : 'admin',
            ],
        ];

        return Order::create([
            'customer_id' => (string) $customerId,
            'order_number' => $orderNumber,
            'items' => $snapshottedItems,
            'subtotal' => round($subtotal, 2),
            'delivery_fee' => $deliveryFee,
            'discount' => $discount,
            'total' => $total,
            'delivery_address' => $data['delivery_address'] ?? 'Customer Address',
            'payment_method' => $data['payment_method'] ?? 'cash_on_delivery',
            'payment_status' => $data['payment_status'] ?? 'pending',
            'order_status' => $initialStatus,
            'notes' => $data['notes'] ?? null,
            'status_history' => $history,
        ]);
    }

    /**
     * Advance / update an order's status along the lifecycle.
     */
    public function updateStatus(Order $order, string $newStatus, ?string $comment = null, string $actor = 'admin'): Order
    {
        $currentStatus = $order->order_status;

        if ($currentStatus === $newStatus) {
            return $order;
        }

        $allowed = $this->allowedTransitions[$currentStatus] ?? [];

        if (! in_array($newStatus, $allowed, true)) {
            $allowedStr = empty($allowed) ? 'None (Order is in terminal state)' : implode(', ', $allowed);
            throw ValidationException::withMessages([
                'status' => [
                    "Invalid order status transition from '{$currentStatus}' to '{$newStatus}'. Allowed transitions: {$allowedStr}.",
                ],
            ]);
        }

        $history = $order->status_history ?? [];
        $history[] = [
            'from' => $currentStatus,
            'status' => $newStatus,
            'comment' => $comment ?? "Status updated to {$newStatus}",
            'created_at' => now()->toIso8601String(),
            'actor' => $actor,
        ];

        $updateData = [
            'order_status' => $newStatus,
            'status_history' => $history,
        ];

        if ($newStatus === 'completed' && $order->payment_method === 'cash_on_delivery') {
            $updateData['payment_status'] = 'paid';
        }

        $order->update($updateData);

        return $order->fresh();
    }

    /**
     * Cancel an order if allowed.
     */
    public function cancelOrder(Order $order, ?string $reason = null, string $actor = 'customer'): Order
    {
        $nonCancellableStatuses = ['ready', 'out_for_delivery', 'completed', 'cancelled'];

        if (in_array($order->order_status, $nonCancellableStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot cancel order in '{$order->order_status}' status."],
            ]);
        }

        return $this->updateStatus(
            $order,
            'cancelled',
            $reason ?? 'Order was cancelled.',
            $actor
        );
    }
}
