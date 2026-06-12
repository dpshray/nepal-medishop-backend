<?php

namespace App\Services\Report;

use App\Enums\Purchase\OrderStatusEnum;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductPerformanceService
{
    public function getTable(
        Carbon $from,
        Carbon $to,
        array  $filters = [],
        string $view    = 'best_sellers',
        int    $perPage = 25
    ): array {
        $query = Product::query()

            // ── Pull in the vendor via product_vendors pivot ─────────────────
            ->join('product_vendors', 'product_vendors.product_id', '=', 'products.id')
            ->whereNull('product_vendors.deleted_at')

            // ── Vendor record for store name ─────────────────────────────────
            ->join('vendors', 'vendors.user_id', '=', 'product_vendors.vendor_id')

            // ── Primary category (first category linked to this product) ─────
            // Using a subquery so we get exactly one category name per row.
            ->joinSub(
                DB::table('category_product')
                    ->join('categories', 'categories.id', '=', 'category_product.category_id')
                    ->select(
                        'category_product.product_id',
                        DB::raw('MIN(categories.id) AS category_id'),
                        DB::raw('MIN(categories.name) AS category_name')
                    )
                    ->groupBy('category_product.product_id'),
                'primary_cat',
                'primary_cat.product_id',
                '=',
                'products.id'
            )

            // ── Sold units & revenue: order_items joined to orders ───────────
            // order_items.item_id is a VARCHAR storing the product_id.
            // We cast it to UNSIGNED for the join condition.
            ->leftJoin('order_items', function ($join) use ($from, $to) {
                $join->on(DB::raw('CAST(order_items.item_id AS UNSIGNED)'), '=', 'products.id')
                    ->whereNotIn('order_items.status', [OrderStatusEnum::CANCELLED->value]);
            })
            ->leftJoin('orders', function ($join) use ($from, $to) {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->whereBetween('orders.created_at', [$from, $to])
                    ->whereNotIn('orders.status', [OrderStatusEnum::CANCELLED->value]);
            })

            // ── Select all report columns ────────────────────────────────────
            ->select([
                'products.id                              AS product_id',
                'products.name                            AS product_name',
                'products.slug                            AS product_slug',
                'vendors.id                               AS vendor_id',
                'vendors.store_name                       AS vendor_name',
                'primary_cat.category_id',
                'primary_cat.category_name',
            ])
            ->selectRaw('
                COALESCE(SUM(order_items.quantity), 0)   AS units_sold,
                COALESCE(SUM(order_items.total), 0)      AS revenue,
 
                -- Stockout occurrences: how many vendor_product_price rows
                -- for this product+vendor combo have units_in_stock = 0
                (
                    SELECT COUNT(*)
                    FROM vendor_product_prices vpp
                    WHERE vpp.product_vendor_id = product_vendors.id
                      AND vpp.units_in_stock = 0
                      AND vpp.deleted_at IS NULL
                ) AS stockout_occurrences
            ')

            ->groupBy(
                'products.id',
                'products.name',
                'products.slug',
                'vendors.id',
                'vendors.store_name',
                'primary_cat.category_id',
                'primary_cat.category_name',
                'product_vendors.id'   // needed for the stockout subquery correlated on product_vendors.id
            );

        // ── Optional filters ─────────────────────────────────────────────────
        if (!empty($filters['vendor_id'])) {
            $query->where('vendors.user_id', $filters['vendor_id']);
        }

        if (!empty($filters['category_id'])) {
            // Filter by any category (not just primary) so subcategory products appear
            $query->whereExists(function ($sub) use ($filters) {
                $sub->select(DB::raw(1))
                    ->from('category_product')
                    ->whereColumn('category_product.product_id', 'products.id')
                    ->where('category_product.category_id', $filters['category_id']);
            });
        }
        if (!empty($filters['search'])) {
            $query->where('products.name', 'like', "%{$filters['search']}%");
        }
        // ── View-specific ordering ───────────────────────────────────────────
        match ($view) {
            'worst_sellers'  => $query->orderBy('units_sold', 'asc')->orderBy('revenue', 'asc'),
            'most_returned'  => $query->orderBy('return_rate_pct', 'desc')->orderBy('units_sold', 'desc'),
            default          => $query->orderBy('units_sold', 'desc')->orderBy('revenue', 'desc'), // best_sellers
        };

        // ── Pagination ───────────────────────────────────────────────────────
        $page    = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, (int) $perPage);
        $total   = (int) (clone $query)->get()->count();

        $results = $query
            ->offset(($page - 1) * $perPage)
            ->orderBy('products.id', 'desc')
            ->limit($perPage)
            ->get();

        return [
            'data'         => $results,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }
}
