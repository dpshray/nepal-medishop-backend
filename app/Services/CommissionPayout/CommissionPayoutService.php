<?php

namespace App\Services\CommissionPayout;

use App\Models\Payout\VendorPayout;
use App\Models\Purchase\OrderItem;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionPayoutService
{
    // -----------------------------------------------------------------------
    // ADMIN: Commission & Payout summary table
    // One row per vendor — the main leaderboard view

    public function getAdminTable(
        Carbon $from,
        Carbon $to,
        array  $filters  = [],
        int    $perPage  = 25
    ): array {
        $query = Vendor::query()
            ->join('users', 'users.id', '=', 'vendors.user_id')

            // Latest payout record for this vendor (for status + settlement date)
            ->leftJoinSub(
                VendorPayout::select(
                    'vendor_id',
                    DB::raw('MAX(id) AS latest_payout_id'),
                    DB::raw('MAX(status) AS payout_status'),
                    DB::raw('MAX(settlement_date) AS settlement_date')
                )->groupBy('vendor_id'),
                'latest_payout',
                'latest_payout.vendor_id',
                '=',
                'users.id'
            )

            // order_items in the date range for this vendor
            ->leftJoin('order_items as oi', 'oi.assigned_vendor_id', '=', 'vendors.id')
            ->leftJoin('orders', function ($join) use ($from, $to) {
                $join->on('orders.id', '=', 'oi.order_id')
                    ->whereBetween('orders.created_at', [$from, $to])
                    ->whereNotIn('orders.status', ['cancelled']);
            })

            ->select([
                'vendors.id                AS vendor_id',
                'vendors.store_name',
                'users.name                AS owner_name',
                'users.commission_percentage AS commission_rate',
                DB::raw("COALESCE(latest_payout.payout_status, 'pending') AS payout_status"),
                DB::raw('latest_payout.settlement_date AS settlement_date'),
            ])
            ->selectRaw('
                -- Gross sales: sum of all non-cancelled order item totals
                COALESCE(
                    SUM(CASE WHEN oi.status != "CANCELLED" AND orders.id IS NOT NULL
                             THEN oi.total ELSE 0 END)
                , 0) AS gross_sales,

                -- Commission amount: gross * rate / 100
                COALESCE(
                    SUM(CASE WHEN oi.status != "CANCELLED" AND orders.id IS NOT NULL
                             THEN oi.total ELSE 0 END)
                    * users.commission_percentage / 100
                , 0) AS commission_amount,

                -- Refund adjustments: placeholder 0 until order_refunds table exists
                0 AS refund_adjustments,

                -- Net payable = gross_sales - commission_amount - refund_adjustments
                COALESCE(
                    SUM(CASE WHEN oi.status != "CANCELLED" AND orders.id IS NOT NULL
                             THEN oi.total ELSE 0 END)
                    * (1 - users.commission_percentage / 100)
                , 0) AS net_payable
            ')

            ->groupBy(
                'vendors.id',
                'vendors.store_name',
                'users.name',
                'users.commission_percentage',
                'latest_payout.payout_status',
                'latest_payout.settlement_date'
            );

        // ── Filters ──────────────────────────────────────────────────────────

        if (!empty($filters['vendor_id'])) {
            $query->where('vendors.user_id', $filters['vendor_id']);
        }

        if (!empty($filters['payout_status'])) {
            if ($filters['payout_status'] === 'pending') {
                // pending means no payout record OR status = pending
                $query->where(function ($q) {
                    $q->whereNull('latest_payout.payout_status')
                        ->orWhere('latest_payout.payout_status', 'pending');
                });
            } else {
                $query->where('latest_payout.payout_status', $filters['payout_status']);
            }
        }

        // ── Pagination ───────────────────────────────────────────────────────

        $page    = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, (int) $perPage);
        $total   = (int) (clone $query)->get()->count();

        $rows = $query
            ->orderByDesc('gross_sales')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'data'         => $rows->map(fn($r) => $this->formatAdminRow($r)),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    // -----------------------------------------------------------------------
    // ADMIN: Per-vendor drill-down — order-level breakdown
    // GET /admin/reports/commission-payout/{vendor}/orders
    // -----------------------------------------------------------------------

    public function getVendorOrderBreakdown(
        int    $vendorId,
        Carbon $from,
        Carbon $to,
        array  $filters = [],
        int    $perPage = 25
    ): array {
        $vendor = Vendor::with('user')->where('user_id', $vendorId)->firstOrFail();

        $query = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            // join vendor's user to get commission_percentage at time of order
            // NOTE: this is the CURRENT rate — once you snapshot rate per order
            // in a commission_logs table, join that instead
            ->join('vendors', 'vendors.user_id', '=', 'order_items.assigned_vendor_id')
            ->join('users', 'users.id', '=', 'vendors.user_id')

            ->where('order_items.assigned_vendor_id', $vendorId)
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('orders.status', ['cancelled'])
            ->whereNotIn('order_items.status', ['cancelled'])

            ->select([
                'orders.id                    AS order_id',
                'orders.order_code',
                DB::raw("CONVERT_TZ(orders.created_at, '+00:00', '+05:45') AS order_date"),
                'order_items.item_name        AS product_name',
                'order_items.quantity',
                'order_items.price            AS unit_price',
                'order_items.total            AS item_total',
                'order_items.status           AS item_status',
                'users.commission_percentage  AS commission_rate',
            ])
            ->selectRaw('
                ROUND(order_items.total * users.commission_percentage / 100, 2) AS commission_amount,
                ROUND(order_items.total * (1 - users.commission_percentage / 100), 2) AS vendor_earning,
                0 AS refund_amount  -- update when refunds table exists
            ')
            ->orderByDesc('orders.created_at');

        // ── Pagination ───────────────────────────────────────────────────────

        $page    = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, (int) $perPage);
        $total   = (int) (clone $query)->get()->count();

        $rows = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        // Totals for this vendor in the period
        $totals = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('vendors', 'vendors.id', '=', 'order_items.assigned_vendor_id')
            ->join('users', 'users.id', '=', 'vendors.user_id')
            ->where('order_items.assigned_vendor_id', $vendorId)
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('orders.status', ['cancelled'])
            ->whereNotIn('order_items.status', ['cancelled'])
            ->selectRaw('
                COALESCE(SUM(order_items.total), 0)                                        AS gross_sales,
                COALESCE(SUM(order_items.total * users.commission_percentage / 100), 0)    AS total_commission,
                COALESCE(SUM(order_items.total * (1 - users.commission_percentage / 100)), 0) AS net_payable,
                COUNT(DISTINCT orders.id)                                                   AS order_count
            ')
            ->first();

        return [
            'vendor' => [
                'id'              => $vendor->id,
                'store_name'      => $vendor->store_name,
                'owner_name'      => $vendor->user->name,
                'commission_rate' => $vendor->user->commission_percentage,
            ],
            'period_totals' => [
                'gross_sales'       => round((float) $totals->gross_sales, 2),
                'total_commission'  => round((float) $totals->total_commission, 2),
                'refund_adjustments' => 0,
                'net_payable'       => round((float) $totals->net_payable, 2),
                'order_count'       => (int) $totals->order_count,
            ],
            'orders' => [
                'data'         => $rows,
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => (int) ceil($total / $perPage),
            ],
        ];
    }

    // -----------------------------------------------------------------------
    // VENDOR: Their own payout history
    // GET /vendor/reports/my-payout
    // -----------------------------------------------------------------------

    public function getVendorPayoutHistory(
        int    $vendorId,
        Carbon $from,
        Carbon $to,
        array  $filters = [],
        int    $perPage = 25
    ): array {
        $query = VendorPayout::where('vendor_id', $vendorId)
            ->where('period_from', '<=', $to->toDateString())
            ->where('period_to', '>=', $from->toDateString());

        if (!empty($filters['payout_status'])) {
            $query->where('status', $filters['payout_status']);
        }

        $query->orderByDesc('created_at');

        $page    = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, (int) $perPage);
        $total   = (int) (clone $query)->get()->count();

        $rows = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    // -----------------------------------------------------------------------
    // VENDOR: Request a payout for a given period
    // POST /vendor/reports/my-payout/request
    //
    // Calculates gross/commission/net from live order data for the period,
    // creates a vendor_payouts row with status=pending.
    // -----------------------------------------------------------------------

    public function requestPayout(int $vendorId, Carbon $from, Carbon $to): array
    {
        // Block if there's already a pending/processing payout overlapping this period
        $existing = VendorPayout::where('vendor_id', $vendorId)
            ->whereIn('status', ['pending', 'processing'])
            ->where('period_from', '<=', $to->toDateString())
            ->where('period_to',   '>=', $from->toDateString())
            ->first();

        if ($existing) {
            return [
                'success' => false,
                'message' => 'A payout request for this period is already ' . $existing->status . '.',
                'payout'  => null,
            ];
        }

        // Calculate totals from orders in the period
        $totals = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('vendors', 'vendors.id', '=', 'order_items.assigned_vendor_id')
            ->join('users', 'users.id', '=', 'vendors.user_id')
            ->where('order_items.assigned_vendor_id', $vendorId)
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereNotIn('orders.status', ['cancelled'])
            ->whereNotIn('order_items.status', ['cancelled'])
            ->selectRaw('
                COALESCE(SUM(order_items.total), 0)                                            AS gross_sales,
                COALESCE(SUM(order_items.total * users.commission_percentage / 100), 0)        AS commission_amount,
                COALESCE(SUM(order_items.total * (1 - users.commission_percentage / 100)), 0)  AS net_payable,
                MAX(users.commission_percentage)                                                AS commission_rate
            ')
            ->first();

        if ((float) $totals->gross_sales <= 0) {
            return [
                'success' => false,
                'message' => 'No sales found for the selected period. Nothing to pay out.',
                'payout'  => null,
            ];
        }

        $payout = VendorPayout::create([
            'vendor_id'           => $vendorId,
            'period_from'         => $from->toDateString(),
            'period_to'           => $to->toDateString(),
            'gross_sales'         => round((float) $totals->gross_sales, 2),
            'commission_amount'   => round((float) $totals->commission_amount, 2),
            'refund_adjustments'  => 0, // update when refunds table exists
            'net_payable'         => round((float) $totals->net_payable, 2),
            'commission_rate'     => (float) $totals->commission_rate,
            'status'              => 'pending',
            'requested_at'        => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Payout request submitted successfully. Admin will process it shortly.',
            'payout'  => $payout,
        ];
    }

    // -----------------------------------------------------------------------
    // ADMIN: Update payout status (processing / paid / rejected)
    // PATCH /admin/reports/commission-payout/{payout}
    // -----------------------------------------------------------------------

    public function updatePayoutStatus(
        VendorPayout $payout,
        string       $status,
        ?string      $remarks = null,
        ?int         $processedBy = null
    ): array {
        $allowed = ['processing', 'paid', 'rejected'];

        if (!in_array($status, $allowed)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }

        $payout->update([
            'status'          => $status,
            'remarks'         => $remarks,
            'processed_by'    => $processedBy,
            'settlement_date' => $status === 'paid' ? now() : $payout->settlement_date,
        ]);

        return [
            'success' => true,
            'message' => "Payout marked as {$status}.",
            'payout'  => $payout->fresh(),
        ];
    }

    // -----------------------------------------------------------------------
    // PRIVATE HELPERS
    // -----------------------------------------------------------------------

    private function formatAdminRow(object $row): array
    {
        return [
            'vendor_id'           => $row->vendor_id,
            'store_name'          => $row->store_name,
            'owner_name'          => $row->owner_name,
            'commission_rate'     => (float) $row->commission_rate,
            'gross_sales'         => round((float) $row->gross_sales, 2),
            'commission_amount'   => round((float) $row->commission_amount, 2),
            'refund_adjustments'  => round((float) $row->refund_adjustments, 2),
            'net_payable'         => round((float) $row->net_payable, 2),
            'payout_status'       => $row->payout_status ?? 'pending',
            'settlement_date'     => $row->settlement_date,
        ];
    }
}
