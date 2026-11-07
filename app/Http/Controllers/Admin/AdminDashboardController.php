<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(): Response
    {
        $statistics = $this->dashboardService->getStatistics();
        $recentOrders = $this->dashboardService->getRecentOrders(8);
        $salesSummary = $this->dashboardService->getSalesSummary();

        return Inertia::render('admin/dashboard', [
            'statistics' => $statistics,
            'recentOrders' => OrderResource::collection($recentOrders)->resolve(),
            'salesSummary' => $salesSummary,
        ]);
    }
}
