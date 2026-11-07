<?php

test('can retrieve real-time dashboard statistics and KPIs', function () {
    $response = $this->getJson('/api/v1/dashboard/statistics');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Dashboard statistics retrieved successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                'total_orders',
                'pending_orders',
                'confirmed_orders',
                'preparing_orders',
                'ready_orders',
                'out_for_delivery_orders',
                'completed_orders',
                'cancelled_orders',
                'total_customers',
                'total_menu_items',
                'total_categories',
                'total_revenue',
            ],
        ]);
});

test('can retrieve recent orders feed', function () {
    $response = $this->getJson('/api/v1/dashboard/recent-orders');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Recent orders retrieved successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'order_number', 'order_status', 'total', 'created_at'],
            ],
        ]);
});

test('can retrieve sales summary and payment breakdown', function () {
    $response = $this->getJson('/api/v1/dashboard/sales-summary');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Sales summary retrieved successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                'kpis',
                'payment_summary' => [
                    'paid_orders_count',
                    'pending_payment_count',
                    'cash_on_delivery_count',
                    'online_payment_count',
                ],
                'status_distribution' => [
                    'pending',
                    'confirmed',
                    'preparing',
                    'ready',
                    'out_for_delivery',
                    'completed',
                    'cancelled',
                ],
            ],
        ]);
});
