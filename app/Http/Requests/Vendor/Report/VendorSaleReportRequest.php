<?php

namespace App\Http\Requests\Vendor\Report;

use App\Enums\Purchase\OrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorSaleReportRequest extends FormRequest
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
            'date_from'    => ['nullable', 'date'],
            'date_to'      => ['nullable', 'date', 'after_or_equal:date_from'],
            'preset'       => ['nullable', Rule::in([
                'today',
                'yesterday',
                'last_7_days',
                'last_30_days',
                'this_month',
                'last_month',
                'this_year',
                'custom',
            ])],
            'category_id'  => ['nullable', 'integer', 'exists:categories,id'],

            // product filter: item_id on order_items is stored as varchar
            'product_id'   => ['nullable', 'integer', 'exists:products,id'],

            'order_status' => ['nullable', Rule::in([
                OrderStatusEnum::PENDING->value,
                OrderStatusEnum::SHIPPED->value,
                OrderStatusEnum::DELIVERED->value,
                OrderStatusEnum::CANCELLED->value,
                OrderStatusEnum::RETURNED->value,
            ])],

            // trend grouping
            'group_by'     => ['nullable', Rule::in(['day', 'week', 'month'])],

            'page'         => ['nullable', 'integer', 'min:1'],
            'per_page'     => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
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
            'today'       => ['from' => $npt->copy()->startOfDay()->utc(),                              'to' => $npt->copy()->endOfDay()->utc()],
            'yesterday'   => ['from' => $npt->copy()->subDay()->startOfDay()->utc(),                    'to' => $npt->copy()->subDay()->endOfDay()->utc()],
            'last_7_days' => ['from' => $npt->copy()->subDays(6)->startOfDay()->utc(),                  'to' => $npt->copy()->endOfDay()->utc()],
            'this_month'  => ['from' => $npt->copy()->startOfMonth()->startOfDay()->utc(),              'to' => $npt->copy()->endOfDay()->utc()],
            'last_month'  => ['from' => $npt->copy()->subMonth()->startOfMonth()->startOfDay()->utc(),  'to' => $npt->copy()->subMonth()->endOfMonth()->endOfDay()->utc()],
            'this_year'   => ['from' => $npt->copy()->startOfYear()->startOfDay()->utc(),               'to' => $npt->copy()->endOfDay()->utc()],
            default       => ['from' => $npt->copy()->subDays(29)->startOfDay()->utc(),                 'to' => $npt->copy()->endOfDay()->utc()],
        };
    }

    public function groupBy(): string
    {
        if ($this->filled('group_by')) {
            return $this->input('group_by');
        }

        $range    = $this->resolvedDateRange();
        $daysDiff = $range['from']->diffInDays($range['to']);

        return match (true) {
            $daysDiff <= 31  => 'day',
            $daysDiff <= 120 => 'week',
            default          => 'month',
        };
    }
}
