<?php

namespace App\Services\Report;

use App\Enums\Purchase\OrderStatusEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesOverviewService
{
    public function getSummaryCards(Carbon $from, Carbon $to, array $filters = []): array
    {
        $query = DB::table('orders')
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('orders.status', [OrderStatusEnum::CANCELLED->value]); // exclude cancelled from GMV

        // ── Optional filters ────────────────────────────────────────────────
        $this->applyVendorFilter($query, $filters);
        $this->applyPaymentMethodFilter($query, $filters);
        $this->applyStatusFilter($query, $filters);

        if (!empty($filters['category_id'])) {
            // Filter orders that contain at least one item in this category
            $query->whereExists(function ($sub) use ($filters) {
                $sub->select(DB::raw(1))
                    ->from('order_items')
                    ->join('category_product', function ($j) {
                        $j->on(DB::raw("CAST(order_items.item_id AS UNSIGNED)"), '=', 'category_product.product_id');
                    })
                    ->whereColumn('order_items.order_id', 'orders.id')
                    ->where('category_product.category_id', $filters['category_id']);
            });
        }
        $totals = $query->selectRaw('
                COUNT(DISTINCT orders.id)         AS total_orders,
                COALESCE(SUM(orders.price), 0)    AS total_gmv,
                COALESCE(AVG(orders.price), 0)    AS avg_order_value
            ')
            ->first();

        $itemsSold = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('orders.status', ['cancelled'])
            ->when(
                !empty($filters['vendor_id']),
                fn($q) =>
                $q->where('order_items.assigned_vendor_id', $filters['vendor_id'])
            )
            ->sum('order_items.quantity');

        // ── Net revenue: GMV minus refunds ──────────────────────────────────
        // TODO: Replace with real refunds table once created.
        // For now we approximate: orders with status 'returned' are treated as refunds.
        // $refundTotal = DB::table('orders')
        //     ->whereBetween('created_at', [$from, $to])
        //     ->where('status', OrderStatusEnum::RETURNED->value)
        //     ->sum('price');

        // ── Commission ──────────────────────────────────────────────────────
        // TODO: Query vendor_commissions table once it exists.
        // Placeholder: 0
        $commissionEarned = 0;
        $commissionEarned = DB::table('orders')
            ->join('vendors', 'vendors.id', '=', 'orders.assigned_vendor_id')
            ->join('users', 'users.id', '=', 'vendors.user_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('orders.status', [OrderStatusEnum::CANCELLED->value])
            ->when(
                !empty($filters['vendor_id']),
                fn($q) =>
                $q->where('vendors.id', $filters['vendor_id'])
            )
            ->selectRaw('SUM(orders.price * users.commission_percentage / 100) AS commission')
            ->value('commission') ?? 0;
        return [
            'total_gmv'          => round((float) $totals->total_gmv, 2),
            'total_orders'       => (int) $totals->total_orders,
            'avg_order_value'    => round((float) $totals->avg_order_value, 2),
            'total_items_sold'   => (int) $itemsSold,
            // 'total_refunds'      => round((float) $refundTotal, 2),
            // 'net_revenue'        => round((float) $totals->total_gmv - $refundTotal, 2),
            'commission_earned'  => round((float) $commissionEarned, 2),
        ];
    }

    // -----------------------------------------------------------------------
    // REVENUE TREND (line chart)
    // -----------------------------------------------------------------------

    /**
     * Returns revenue grouped by day/week/month for the line chart.
     * All dates converted to NPT (UTC+5:45) for display.
     */
    public function getRevenueTrend(Carbon $from, Carbon $to, string $groupBy, array $filters = []): array
    {
        // MySQL CONVERT_TZ handles the UTC→NPT conversion directly in the query.
        // NPT is UTC+5:45, stored in MySQL tz tables as 'Asia/Kathmandu'.
        // If tz tables aren't populated, use: +5:45 offset directly.
        $dateExpr = match ($groupBy) {
            'day'   => "DATE(CONVERT_TZ(orders.created_at, '+00:00', '+05:45'))",
            'week'  => "DATE(DATE_SUB(CONVERT_TZ(orders.created_at, '+00:00', '+05:45'), INTERVAL WEEKDAY(CONVERT_TZ(orders.created_at, '+00:00', '+05:45')) DAY))",
            'month' => "DATE_FORMAT(CONVERT_TZ(orders.created_at, '+00:00', '+05:45'), '%Y-%m-01')",
        };

        $query = DB::table('orders')
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('orders.status', [OrderStatusEnum::CANCELLED->value])
            ->selectRaw("
                {$dateExpr}                      AS period,
                COUNT(orders.id)                 AS order_count,
                COALESCE(SUM(orders.price), 0)   AS gmv
            ")
            ->groupByRaw($dateExpr)
            ->orderByRaw($dateExpr);

        $this->applyVendorFilter($query, $filters);
        $this->applyPaymentMethodFilter($query, $filters);

        return $query->get()->toArray();
    }

    // -----------------------------------------------------------------------
    // ORDERS OVER TIME (bar chart)
    // -----------------------------------------------------------------------

    public function getOrdersTrend(Carbon $from, Carbon $to, string $groupBy, array $filters = []): array
    {
        // Reuses the same grouping logic — just a different selection
        return $this->getRevenueTrend($from, $to, $groupBy, $filters);
        // The controller decides which fields to send to each chart
    }

    // -----------------------------------------------------------------------
    // SALES BY CATEGORY (donut chart)
    // -----------------------------------------------------------------------

    public function getSalesByCategory(Carbon $from, Carbon $to, array $filters = []): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('category_product', DB::raw('CAST(order_items.item_id AS UNSIGNED)'), '=', 'category_product.product_id')
            ->join('categories', 'categories.id', '=', 'category_product.category_id')
            ->whereNull('categories.parent_id') // top-level categories only
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('orders.status', [OrderStatusEnum::CANCELLED->value])
            ->when(
                !empty($filters['vendor_id']),
                fn($q) =>
                $q->where('order_items.assigned_vendor_id', $filters['vendor_id'])
            )
            ->selectRaw('
                categories.id,
                categories.name            AS category_name,
                SUM(order_items.quantity)  AS units_sold,
                SUM(order_items.total)     AS revenue
            ')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get()
            ->toArray();
    }

    // -----------------------------------------------------------------------
    // SALES BY DISTRICT (map / bar chart)
    // -----------------------------------------------------------------------

    public function getSalesByDistrict(Carbon $from, Carbon $to, array $filters = []): array
    {
        return DB::table('orders')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->join('vendors', 'vendors.id', '=', 'order_items.assigned_vendor_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('orders.status', [OrderStatusEnum::CANCELLED->value])
            ->when(
                !empty($filters['vendor_id']),
                fn($q) =>
                $q->where('vendors.id', $filters['vendor_id'])
            )
            ->selectRaw('
                vendors.district,
                COUNT(DISTINCT orders.id) AS order_count,
                SUM(orders.price)         AS gmv
            ')
            ->groupBy('vendors.district')
            ->orderByDesc('gmv')
            ->get()
            ->toArray();
    }

    // -----------------------------------------------------------------------
    // DETAIL TABLE  (paginated)
    // -----------------------------------------------------------------------

    /**
     * Daily breakdown table — each row is one calendar day in NPT.
     */
    public function getDetailTable(Carbon $from, Carbon $to, array $filters = [], int $perPage = 25)
    {
        $dateExpr = "DATE(CONVERT_TZ(orders.created_at, '+00:00', '+05:45'))";

        $query = DB::table('orders')
            ->whereBetween('orders.created_at', [$from, $to])
            ->join('vendors', 'vendors.id', '=', 'orders.assigned_vendor_id')
            ->join('users', 'users.id', '=', 'vendors.user_id')
            ->selectRaw("
                {$dateExpr}  AS date,
                COUNT(orders.id)  AS order_count,
                COALESCE(SUM(orders.price), 0)  AS gmv,
                COALESCE(SUM(orders.price * users.commission_percentage / 100), 0)  AS commission
")
            ->groupByRaw($dateExpr)
            ->orderByRaw("{$dateExpr} DESC");

        $this->applyVendorFilter($query, $filters);
        $this->applyPaymentMethodFilter($query, $filters);
        $this->applyStatusFilter($query, $filters);

        // Manual pagination over a raw query
        $page    = max(1, (int) ($filters['page'] ?? 1));
        $total   = (int) ((clone $query)->get()->count());
        $perPage = max(1, (int) $perPage); // prevents division by zero too
        $page    = max(1, (int) $page);
        $results = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();
        return [
            'data'         => $results,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    // -----------------------------------------------------------------------
    // PRIVATE HELPERS
    // -----------------------------------------------------------------------

    private function applyVendorFilter($query, array $filters): void
    {
        if (!empty($filters['vendor_id'])) {
            // orders.assigned_vendor_id links order to its vendor
            $query->where('orders.assigned_vendor_id', $filters['vendor_id']);
        }
    }

    private function applyPaymentMethodFilter($query, array $filters): void
    {
        if (!empty($filters['payment_method'])) {
            $query->where('orders.payment_method', $filters['payment_method']);
        }
    }

    private function applyStatusFilter($query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('orders.status', $filters['status']);
        }
    }
}
