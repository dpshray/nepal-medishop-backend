<?php

namespace App\Http\Requests\Admin\Report;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminVendorReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date_from'     => ['nullable', 'date'],
            'date_to'       => ['nullable', 'date', 'after_or_equal:date_from'],
            'preset'        => ['nullable', Rule::in([
                'today',
                'yesterday',
                'last_7_days',
                'last_30_days',
                'this_month',
                'last_month',
                'this_year',
                'custom',
            ])],

            // Search by vendor store name or user name
            'search'        => ['nullable', 'string', 'max:100'],

            // active = verified_at is not null, suspended = verified_at is null
            'vendor_status' => ['nullable', Rule::in(['active', 'suspended'])],

            'category_id'   => ['nullable', 'integer', 'exists:categories,id'],

            // Column to sort by
            'sort_by'       => ['nullable', Rule::in([
                'store_name',
                'total_orders',
                'gmv',
                'net_revenue',
                'commission',
                'fulfillment_rate',
                'cancellation_rate',
                'return_rate',
            ])],
            'sort_dir'      => ['nullable', Rule::in(['asc', 'desc'])],

            'page'          => ['nullable', 'integer', 'min:1'],
            'per_page'      => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
        ];
    }
    public function resolvedDateRange(): array
    {
        $preset = $this->input('preset', 'last_30_days');

        if ($preset === 'custom') {
            return [
                'from' => now('Asia/Kathmandu')->parse($this->input('date_from'))->startOfDay()->utc(),
                'to'   => now('Asia/Kathmandu')->parse($this->input('date_to'))->endOfDay()->utc(),
            ];
        }

        $npt = now('Asia/Kathmandu');

        return match ($preset) {
            'today'       => ['from' => $npt->copy()->startOfDay()->utc(),                         'to' => $npt->copy()->endOfDay()->utc()],
            'yesterday'   => ['from' => $npt->copy()->subDay()->startOfDay()->utc(),               'to' => $npt->copy()->subDay()->endOfDay()->utc()],
            'last_7_days' => ['from' => $npt->copy()->subDays(6)->startOfDay()->utc(),             'to' => $npt->copy()->endOfDay()->utc()],
            'this_month'  => ['from' => $npt->copy()->startOfMonth()->startOfDay()->utc(),         'to' => $npt->copy()->endOfDay()->utc()],
            'last_month'  => ['from' => $npt->copy()->subMonth()->startOfMonth()->startOfDay()->utc(), 'to' => $npt->copy()->subMonth()->endOfMonth()->endOfDay()->utc()],
            'this_year'   => ['from' => $npt->copy()->startOfYear()->startOfDay()->utc(),          'to' => $npt->copy()->endOfDay()->utc()],
            default       => ['from' => $npt->copy()->subDays(29)->startOfDay()->utc(),            'to' => $npt->copy()->endOfDay()->utc()],
        };
    }
}
