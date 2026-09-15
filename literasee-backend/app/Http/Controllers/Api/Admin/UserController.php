<?php
// app/Http/Controllers/Api/Admin/UserController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/admin/users
     *
     * Query params:
     * - q: search by name/email
     * - role: filter by role (customer|admin)
     * - sort: newest|oldest|most_orders|most_spent
     * - per_page: default 15
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->when($request->q, function ($q, $kw) {
                $q->where(function ($qq) use ($kw) {
                    $qq->where('name', 'like', "%{$kw}%")
                       ->orWhere('email', 'like', "%{$kw}%")
                       ->orWhere('phone', 'like', "%{$kw}%");
                });
            })
            ->when($request->role, fn($q, $r) => $q->where('role', $r))
            ->withCount('orders')
            ->withSum([
                'orders as total_spent' => fn($q) => $q->where('payment_status', 'paid')
            ], 'total_amount');

        // Sorting
        match ($request->get('sort', 'newest')) {
            'oldest' => $query->oldest(),
            'most_orders' => $query->orderByDesc('orders_count'),
            'most_spent' => $query->orderByDesc('total_spent'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->latest(),
        };

        $users = $query->paginate($request->per_page ?? 15);

        // Tambahkan info tambahan
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar_url' => $user->avatar_url,
                'phone' => $user->phone,
                'address' => $user->address,
                'orders_count' => $user->orders_count,
                'total_spent' => (float) ($user->total_spent ?? 0),
                'total_spent_formatted' => 'Rp ' . number_format($user->total_spent ?? 0, 0, ',', '.'),
                'email_verified' => !is_null($user->email_verified_at),
                'created_at' => $user->created_at->toISOString(),
                'last_order_at' => $user->orders()->latest()->value('created_at'),
            ];
        });

        // Stats keseluruhan
        $stats = [
            'total_users' => User::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'new_this_month' => User::where('role', 'customer')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'active_buyers' => User::whereHas('orders', fn($q) => $q->where('payment_status', 'paid'))
                ->count(),
        ];

        return $this->successResponse([
            'users' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * GET /api/admin/users/{user}
     * Detail user + riwayat order.
     */
    public function show(User $user)
    {
        $user->loadCount('orders');
        $user->loadSum([
            'orders as total_spent' => fn($q) => $q->where('payment_status', 'paid')
        ], 'total_amount');

        $recentOrders = $user->orders()
            ->with('items')
            ->latest()
            ->take(10)
            ->get();

        // Statistik user
        $orderStats = DB::table('orders')
            ->where('user_id', $user->id)
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN payment_status = "paid" THEN 1 ELSE 0 END) as paid_orders,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders
            ')
            ->first();

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar_url' => $user->avatar_url,
                'phone' => $user->phone,
                'address' => $user->address,
                'email_verified' => !is_null($user->email_verified_at),
                'created_at' => $user->created_at->toISOString(),
            ],
            'stats' => [
                'total_orders' => (int) ($orderStats->total_orders ?? 0),
                'paid_orders' => (int) ($orderStats->paid_orders ?? 0),
                'cancelled_orders' => (int) ($orderStats->cancelled_orders ?? 0),
                'total_spent' => (float) ($user->total_spent ?? 0),
                'total_spent_formatted' => 'Rp ' . number_format($user->total_spent ?? 0, 0, ',', '.'),
            ],
            'recent_orders' => OrderResource::collection($recentOrders),
        ]);
    }

    /**
     * PATCH /api/admin/users/{user}/role
     * Ubah role user (customer <-> admin).
     */
    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:customer,admin',
        ]);

        // Prevent admin menghapus role admin dirinya sendiri
        if ($user->id === auth()->id() && $validated['role'] === 'customer') {
            return $this->errorResponse(
                'Tidak bisa mengubah role diri sendiri menjadi customer.',
                422
            );
        }

        $user->update(['role' => $validated['role']]);

        return $this->successResponse(
            new UserResource($user->fresh()),
            "Role diubah menjadi {$validated['role']}."
        );
    }

    /**
     * DELETE /api/admin/users/{user}
     * Hapus user (soft delete via relation cascade).
     */
    public function destroy(User $user)
    {
        // Prevent admin menghapus dirinya sendiri
        if ($user->id === auth()->id()) {
            return $this->errorResponse('Tidak bisa menghapus akun sendiri.', 422);
        }

        // Prevent hapus admin terakhir
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return $this->errorResponse('Tidak bisa menghapus admin terakhir.', 422);
        }

        // Hapus avatar
        if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
        }

        $user->tokens()->delete();
        $user->delete();

        return $this->noContentResponse('User berhasil dihapus.');
    }

    /**
     * GET /api/admin/users/top-buyers
     * Top 10 pembeli paling loyal.
     */
    public function topBuyers()
    {
        $topBuyers = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum([
                'orders as total_spent' => fn($q) => $q->where('payment_status', 'paid')
            ], 'total_amount')
            ->having('total_spent', '>', 0)
            ->orderByDesc('total_spent')
            ->take(10)
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'orders_count' => $user->orders_count,
                'total_spent' => (float) $user->total_spent,
                'total_spent_formatted' => 'Rp ' . number_format($user->total_spent, 0, ',', '.'),
            ]);

        return $this->successResponse($topBuyers);
    }
}