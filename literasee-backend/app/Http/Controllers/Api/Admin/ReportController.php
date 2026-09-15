<?php
// app/Http/Controllers/Api/Admin/ReportController.php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\SalesReportExport;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/admin/reports/sales?date_from=&date_to=
     */
    public function sales(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $orders = Order::with(['user', 'items'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('payment_status', 'paid')
            ->latest()
            ->paginate(20);

        $summary = Order::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('payment_status', 'paid')
            ->selectRaw('COUNT(*) as total_orders, SUM(total_amount) as total_revenue')
            ->first();

        $byCategory = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('books', 'books.id', '=', 'order_items.book_id')
            ->join('categories', 'categories.id', '=', 'books.category_id')
            ->whereDate('orders.created_at', '>=', $dateFrom)
            ->whereDate('orders.created_at', '<=', $dateTo)
            ->where('orders.payment_status', 'paid')
            ->groupBy('categories.id', 'categories.name')
            ->select('categories.name', DB::raw('SUM(order_items.subtotal) as total'))
            ->orderByDesc('total')
            ->get();

        return $this->successResponse([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'summary' => [
                'total_orders' => (int) ($summary->total_orders ?? 0),
                'total_revenue' => (float) ($summary->total_revenue ?? 0),
            ],
            'by_category' => $byCategory,
            'orders' => $orders,
        ]);
    }

    /**
     * GET /api/admin/reports/export-sales?date_from=&date_to=
     */
    public function exportSales(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        return Excel::download(
            new SalesReportExport($dateFrom, $dateTo),
            "laporan-penjualan-{$dateFrom}-sd-{$dateTo}.xlsx"
        );
    }
}