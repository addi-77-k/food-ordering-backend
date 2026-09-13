<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\OrderResource;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class DashboardController extends BaseApiController
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * @OA\Get(
     *     path="/dashboard/statistics",
     *     summary="Retrieve real-time food ordering statistics and KPIs",
     *     tags={"Dashboard"},
     *     @OA\Response(
     *         response=200,
     *         description="Statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Dashboard statistics retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_orders", type="integer", example=142),
     *                 @OA\Property(property="pending_orders", type="integer", example=8),
     *                 @OA\Property(property="confirmed_orders", type="integer", example=5),
     *                 @OA\Property(property="preparing_orders", type="integer", example=7),
     *                 @OA\Property(property="ready_orders", type="integer", example=3),
     *                 @OA\Property(property="out_for_delivery_orders", type="integer", example=4),
     *                 @OA\Property(property="completed_orders", type="integer", example=110),
     *                 @OA\Property(property="cancelled_orders", type="integer", example=5),
     *                 @OA\Property(property="total_customers", type="integer", example=89),
     *                 @OA\Property(property="total_menu_items", type="integer", example=35),
     *                 @OA\Property(property="total_categories", type="integer", example=6),
     *                 @OA\Property(property="total_revenue", type="number", format="float", example=4520.75)
     *             )
     *         )
     *     )
     * )
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->dashboardService->getStatistics();

        return $this->sendResponse($stats, 'Dashboard statistics retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/dashboard/recent-orders",
     *     summary="Get the 10 most recent orders placed on the platform",
     *     tags={"Dashboard"},
     *     @OA\Response(response=200, description="Recent orders list")
     * )
     */
    public function recentOrders(): JsonResponse
    {
        $orders = $this->dashboardService->getRecentOrders(10);

        return $this->sendResponse(OrderResource::collection($orders), 'Recent orders retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/dashboard/sales-summary",
     *     summary="Get comprehensive sales summary and payment breakdown",
     *     tags={"Dashboard"},
     *     @OA\Response(response=200, description="Sales summary retrieved")
     * )
     */
    public function salesSummary(): JsonResponse
    {
        $summary = $this->dashboardService->getSalesSummary();

        return $this->sendResponse($summary, 'Sales summary retrieved successfully');
    }
}
