<?php

namespace App\Services\Report;

use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VendorPerformanceService
{
    const FLAG_CANCELLATION_RATE = 15.0;
    const FLAG_FULFILLMENT_RATE  = 70.0;

    public function getLeaderboard(
        Carbon $from,
        Carbon $to,
        array  $filters  = [],
        string $sortBy   = 'gmv',
        string $sortDir  = 'desc',
        int    $perPage  = 25
    ): array {
        $query = Vendor::query()
            ->join('users', 'users.id', '=', 'vendors.user_id')

            // Plain LEFT JOIN — no whereHas, no placeholder
            ->leftJoin('order_items as oi', 'oi.assigned_vendor_id', '=', 'vendors.id')

            // Date range goes in the ON clause, not WHERE, so zero-order vendors still appear
            ->leftJoin('orders', function ($join) use ($from, $to) {
                $join->on('orders.id', '=', 'oi.order_id')
                    ->whereBetween('orders.created_at', [$from, $to]);
            })

            ->select([
                'vendors.id          AS vendor_id',
                'vendors.store_name',
                'vendors.district',
                'vendors.verified_at',
                'users.name          AS owner_name',
                'users.commission_percentage',
            ])
            ->selectRaw('
                COUNT(DISTINCT orders.id) AS total_orders,

                COUNT(oi.id) AS total_items,

                COALESCE(
                    SUM(CASE WHEN oi.status != "CANCELLED" AND orders.id IS NOT NULL
                             THEN oi.total ELSE 0 END)
                , 0) AS gmv,

                COALESCE(
                    SUM(CASE WHEN oi.status != "CANCELLED" AND orders.id IS NOT NULL
                             THEN oi.total ELSE 0 END)
                , 0) AS net_revenue,

                COALESCE(
                    SUM(CASE WHEN oi.status != "CANCELLED" AND orders.id IS NOT NULL
                             THEN oi.total ELSE 0 END)
                    * users.commission_percentage / 100
                , 0) AS commission,

                CASE
                    WHEN COUNT(CASE WHEN oi.status != "CANCELLED" AND orders.id IS NOT NULL THEN 1 END) > 0
                    THEN ROUND(
                        COUNT(CASE WHEN oi.status = "DELIVERED" THEN 1 END)
                        / COUNT(CASE WHEN oi.status != "CANCELLED" AND orders.id IS NOT NULL THEN 1 END)
                        * 100
                    , 2)
                    ELSE 0
                END AS fulfillment_rate,

                CASE
                    WHEN COUNT(oi.id) > 0
                    THEN ROUND(
                        COUNT(CASE WHEN oi.status = "CANCELLED" THEN 1 END)
                        / COUNT(oi.id) * 100
                    , 2)
                    ELSE 0
                END AS cancellation_rate
            ')

            ->groupBy(
                'vendors.id',
                'vendors.store_name',
                'vendors.district',
                'vendors.verified_at',
                'users.name',
                'users.commission_percentage'
            );

        // ── Filters ──────────────────────────────────────────────────────────

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('vendors.store_name', 'LIKE', $search)
                    ->orWhere('users.name', 'LIKE', $search);
            });
        }

        if (!empty($filters['vendor_status'])) {
            if ($filters['vendor_status'] === 'active') {
                $query->whereNotNull('vendors.verified_at');
            } else {
                $query->whereNull('vendors.verified_at');
            }
        }

        if (!empty($filters['category_id'])) {
            $query->whereExists(function ($sub) use ($filters) {
                $sub->select(DB::raw(1))
                    ->from('product_vendors as pv_cat')
                    ->join('category_product as cp', 'cp.product_id', '=', 'pv_cat.product_id')
                    ->whereColumn('pv_cat.vendor_id', 'vendors.id')
                    ->whereNull('pv_cat.deleted_at')
                    ->where('cp.category_id', $filters['category_id']);
            });
        }

        // ── Sort ─────────────────────────────────────────────────────────────

        $sortMap = [
            'store_name'        => 'vendors.store_name',
            'total_orders'      => 'total_orders',
            'gmv'               => 'gmv',
            'net_revenue'       => 'net_revenue',
            'commission'        => 'commission',
            'fulfillment_rate'  => 'fulfillment_rate',
            'cancellation_rate' => 'cancellation_rate',
        ];

        $sortColumn = $sortMap[$sortBy] ?? 'gmv';
        $sortDir    = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortColumn, $sortDir);

        // ── Pagination ───────────────────────────────────────────────────────

        $page    = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, (int) $perPage);
        $total   = (int) (clone $query)->get()->count();

        $rows = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $offset = ($page - 1) * $perPage;

        $data = $rows->map(function ($row, $index) use ($offset) {
            return [
                'rank'              => $offset + $index + 1,
                'vendor_id'         => $row->vendor_id,
                'store_name'        => $row->store_name,
                'owner_name'        => $row->owner_name,
                'district'          => $row->district,
                'status'            => $row->verified_at ? 'active' : 'suspended',
                'total_orders'      => (int)   $row->total_orders,
                'gmv'               => round((float) $row->gmv, 2),
                'net_revenue'       => round((float) $row->net_revenue, 2),
                'commission'        => round((float) $row->commission, 2),
                'fulfillment_rate'  => (float) $row->fulfillment_rate,
                'cancellation_rate' => (float) $row->cancellation_rate,
            ];
        });

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }
}
