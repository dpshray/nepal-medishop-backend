<?php

namespace App\Models\Payout;

use App\Models\Traits\UuidModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class VendorPayout extends Model
{
    //
    use UuidModelTrait;
    protected $fillable = [
        'uuid',
        'vendor_id',
        'period_from',
        'period_to',
        'gross_sales',
        'commission_amount',
        'refund_adjustments',
        'net_payable',
        'commission_rate',
        'status',
        'requested_at',
        'settlement_date',
        'remarks',
        'processed_by',
    ];

    protected $casts = [
        'period_from'         => 'date',
        'period_to'           => 'date',
        'gross_sales'         => 'decimal:2',
        'commission_amount'   => 'decimal:2',
        'refund_adjustments'  => 'decimal:2',
        'net_payable'         => 'decimal:2',
        'commission_rate'     => 'decimal:2',
        'requested_at'        => 'datetime',
        'settlement_date'     => 'datetime',
    ];


    // ── Relationships ────────────────────────────────────────────────────────

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopeForVendor(Builder $query, int $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeInPeriod(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('period_from', [$from, $to]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isEditable(): bool
    {
        return in_array($this->status, ['pending', 'rejected']);
    }
}
