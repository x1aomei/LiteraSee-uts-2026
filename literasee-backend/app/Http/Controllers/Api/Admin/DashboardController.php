<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $stats = [
            'total_revenue' => (float) Order::whereIn('status', ['processing', 'shipped', 'delivered'])
                ->sum('total_amount'),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')
                ->where('payment_status', 'paid')->count(),
            'total_products' => Book::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'low_stock' => Book::where('stock', '<=', 5)->where('stock', '>', 0)->count(),

            // Tambahan statistik user
            'new_customers_today' => User::where('role', 'customer')
                ->whereDate('created_at', today())->count(),
            'new_customers_this_month' => User::where('role', 'customer')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
            'active_buyers' => User::whereHas('orders', fn($q) => $q->where('payment_status', 'paid'))
                ->count(),
        ];

        $recentOrders = Order::with(['user', 'items'])
            ->latest()
            ->take(5)
            ->get();

        $recentCustomers = User::where('role', 'customer')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'avatar_url' => $u->avatar_url,
                'registered_at' => $u->created_at->toISOString(),
            ]);

        $topProducts = Book::withCount(['orderItems as sold' => function ($q) {
                $q->select(DB::raw('SUM(quantity)'))
                  ->whereHas('order', fn($qq) => $qq->where('payment_status', 'paid'));
            }])
            ->having('sold', '>', 0)
            ->orderByDesc('sold')
            ->take(5)
            ->get();

        $revenueChart = Order::select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total'),
            ])
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $this->successResponse([
            'stats' => $stats,
            'recent_orders' => OrderResource::collection($recentOrders),
            'recent_customers' => $recentCustomers,
            'top_products' => $topProducts->map(fn($b) => [
                'id' => $b->id,
                'title' => $b->title,
                'sold' => (int) $b->sold,
                'image_url' => $b->image_url,
            ]),
            'revenue_chart' => $revenueChart,
        ]);
    }
}
