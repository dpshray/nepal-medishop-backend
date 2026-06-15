<?php

namespace App\Services\Report\Vendor;

use App\Enums\Purchase\OrderItemStatusEnum;
use App\Enums\Purchase\OrderStatusEnum;
use App\Models\Purchase\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VendorSalesService
{
    // SUMMARY CARDS
    // My revenue | My orders | Items sold | Avg order value

    public function getSummaryCards(int $vendorId, Carbon $from, Carbon $to, array $filters = []): array
    {
        $query = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.assigned_vendor_id', $vendorId)
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('order_items.status', [OrderItemStatusEnum::CANCELLED->value]);

        $this->applyCategoryFilter($query, $filters);
        $this->applyProductFilter($query, $filters);
        $this->applyStatusFilter($query, $filters);

        $totals = (clone $query)
            ->selectRaw('
                COALESCE(SUM(order_items.total), 0)          AS my_revenue,
                COALESCE(SUM(order_items.quantity), 0)       AS items_sold,
                COUNT(DISTINCT orders.id)                    AS my_orders
            ')
            ->first();

        $myOrders = max(1, (int) $totals->my_orders);

        return [
            'my_revenue'       => round((float) $totals->my_revenue, 2),
            'my_orders'        => (int) $totals->my_orders,
            'items_sold'       => (int) $totals->items_sold,
            'avg_order_value'  => round((float) $totals->my_revenue / $myOrders, 2),
        ];
    }

    // REVENUE TREND (line chart)
    // Grouped by day / week / month in NPT

    public function getRevenueTrend(int $vendorId, Carbon $from, Carbon $to, string $groupBy, array $filters = []): array
    {
        $dateExpr = match ($groupBy) {
            'day'   => "DATE(CONVERT_TZ(orders.created_at, '+00:00', '+05:45'))",
            'week'  => "DATE(DATE_SUB(CONVERT_TZ(orders.created_at, '+00:00', '+05:45'), INTERVAL WEEKDAY(CONVERT_TZ(orders.created_at, '+00:00', '+05:45')) DAY))",
            'month' => "DATE_FORMAT(CONVERT_TZ(orders.created_at, '+00:00', '+05:45'), '%Y-%m-01')",
        };

        $query = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.assigned_vendor_id', $vendorId)
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('order_items.status', [OrderItemStatusEnum::CANCELLED->value])
            ->selectRaw("
                {$dateExpr}                              AS period,
                COUNT(DISTINCT orders.id)                AS order_count,
                COALESCE(SUM(order_items.total), 0)      AS revenue
            ")
            ->groupByRaw($dateExpr)
            ->orderByRaw($dateExpr);

        $this->applyCategoryFilter($query, $filters);
        $this->applyProductFilter($query, $filters);

        return $query->get()->map(fn($row) => [
            'label'       => $row->period,
            'revenue'     => round((float) $row->revenue, 2),
            'order_count' => (int) $row->order_count,
        ])->toArray();
    }

    // TOP PRODUCTS (bar chart)
    // Top 10 products by revenue for this vendor in the period

    public function getTopProducts(int $vendorId, Carbon $from, Carbon $to, array $filters = []): array
    {
        $query = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.assigned_vendor_id', $vendorId)
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('order_items.status', [OrderItemStatusEnum::CANCELLED->value])
            ->selectRaw('
                order_items.item_id                      AS product_id,
                order_items.item_name                    AS product_name,
                COALESCE(SUM(order_items.quantity), 0)   AS units_sold,
                COALESCE(SUM(order_items.total), 0)      AS revenue
            ')
            ->groupBy('order_items.item_id', 'order_items.item_name')
            ->orderByDesc('revenue')
            ->limit(10);

        $this->applyCategoryFilter($query, $filters);

        return $query->get()->toArray();
    }

    // DETAIL TABLE
    // Date | Orders | Revenue | Refunds | Net  (paginated, one row per day)

    public function getDetailTable(int $vendorId, Carbon $from, Carbon $to, array $filters = [], int $perPage = 25): array
    {
        $dateExpr = "DATE(CONVERT_TZ(orders.created_at, '+00:00', '+05:45'))";

        $query = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.assigned_vendor_id', $vendorId)
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw("
                {$dateExpr}                                                          AS date,
                COUNT(DISTINCT orders.id)                                            AS order_count,
                COALESCE(SUM(CASE WHEN order_items.status != 'CANCELLED'
                                  THEN order_items.total ELSE 0 END), 0)            AS revenue,
                0                                                                    AS refunds,
                COALESCE(SUM(CASE WHEN order_items.status != 'CANCELLED'
                                  THEN order_items.total ELSE 0 END), 0)            AS net
            ")
            ->groupByRaw($dateExpr)
            ->orderByRaw("{$dateExpr} DESC");

        $this->applyCategoryFilter($query, $filters);
        $this->applyProductFilter($query, $filters);
        $this->applyStatusFilter($query, $filters);

        $page    = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, (int) $perPage);
        $total   = (int) (clone $query)->get()->count();

        $rows = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn($row) => [
                'date'        => $row->date,
                'order_count' => (int)   $row->order_count,
                'revenue'     => round((float) $row->revenue, 2),
                'refunds'     => round((float) $row->refunds, 2),   // 0 until refunds table exists
                'net'         => round((float) $row->net, 2),
            ]);

        return [
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => $total > 0 ? (int) ceil($total / $perPage) : 1,
        ];
    }

    //Helper function

    private function applyCategoryFilter($query, array $filters): void
    {
        if (!empty($filters['category_id'])) {
            $query->whereExists(function ($sub) use ($filters) {
                $sub->select(DB::raw(1))
                    ->from('category_product')
                    ->whereColumn('category_product.product_id', DB::raw('CAST(order_items.item_id AS UNSIGNED)'))
                    ->where('category_product.category_id', $filters['category_id']);
            });
        }
    }

    private function applyProductFilter($query, array $filters): void
    {
        if (!empty($filters['product_id'])) {
            // item_id is varchar on order_items, so cast for comparison
            $query->where(DB::raw('CAST(order_items.item_id AS UNSIGNED)'), $filters['product_id']);
        }
    }

    private function applyStatusFilter($query, array $filters): void
    {
        if (!empty($filters['order_status'])) {
            $query->where('order_items.status', strtoupper($filters['order_status']));
        }
    }
}
