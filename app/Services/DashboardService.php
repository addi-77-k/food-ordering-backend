<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;

class DashboardService
{
    /**
     * Get aggregated statistics for the dashboard.
     */
    public function getStatistics(): array
    {
        $totalOrders = Order::count();
        $pendingOrders = Order::where('order_status', 'pending')->count();
        $confirmedOrders = Order::where('order_status', 'confirmed')->count();
        $preparingOrders = Order::where('order_status', 'preparing')->count();
        $readyOrders = Order::where('order_status', 'ready')->count();
        $outForDeliveryOrders = Order::where('order_status', 'out_for_delivery')->count();
        $completedOrders = Order::where('order_status', 'completed')->count();
        $cancelledOrders = Order::where('order_status', 'cancelled')->count();

        $totalCustomers = Customer::count();
        $totalMenuItems = MenuItem::count();
        $totalCategories = Category::count();

        // Calculate total revenue from completed orders
        $totalRevenue = (float) Order::where('order_status', 'completed')->sum('total');

        return [
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'confirmed_orders' => $confirmedOrders,
            'preparing_orders' => $preparingOrders,
            'ready_orders' => $readyOrders,
            'out_for_delivery_orders' => $outForDeliveryOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'total_customers' => $totalCustomers,
            'total_menu_items' => $totalMenuItems,
            'total_categories' => $totalCategories,
            'total_revenue' => round($totalRevenue, 2),
        ];
    }

    /**
     * Get recent orders.
     */
    public function getRecentOrders(int $limit = 10)
    {
        return Order::with('customer')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get sales breakdown by order status and payments.
     */
    public function getSalesSummary(): array
    {
        $statistics = $this->getStatistics();

        $paidOrders = Order::where('payment_status', 'paid')->count();
        $pendingPaymentOrders = Order::where('payment_status', 'pending')->count();
        $cashOrders = Order::where('payment_method', 'cash_on_delivery')->count();
        $onlineOrders = Order::whereIn('payment_method', ['card', 'online'])->count();

        return [
            'kpis' => $statistics,
            'payment_summary' => [
                'paid_orders_count' => $paidOrders,
                'pending_payment_count' => $pendingPaymentOrders,
                'cash_on_delivery_count' => $cashOrders,
                'online_payment_count' => $onlineOrders,
            ],
            'status_distribution' => [
                'pending' => $statistics['pending_orders'],
                'confirmed' => $statistics['confirmed_orders'],
                'preparing' => $statistics['preparing_orders'],
                'ready' => $statistics['ready_orders'],
                'out_for_delivery' => $statistics['out_for_delivery_orders'],
                'completed' => $statistics['completed_orders'],
                'cancelled' => $statistics['cancelled_orders'],
            ],
        ];
    }
}
