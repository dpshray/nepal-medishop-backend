<?php

namespace App\Services;

use App\Enums\Purchase\PaymentStatusEnum;
use App\Models\Payment\Payment;
use App\Models\Purchase\Order;
use App\Models\Product\Service\ServiceBooking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Create a payment record for any payable model (Order, ServiceBooking, etc.)
     */
    public function createPayment(Model $payable, string $gateway, string $transactionId): Payment
    {
        return Payment::create([
            'payable_type'     => $payable->getMorphClass(),
            'payable_id'       => $payable->getKey(),
            'payment_gateway'  => $gateway,
            'payment_status'   => PaymentStatusEnum::INITIATED->value,
            'transaction_id'   => $transactionId,
            'reference_id'     => 'REF-' . ($payable->order_code ?? $payable->getKey()),
            'amount'           => $payable->price,
            'currency'         => 'NPR',
        ]);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(
        Payment $payment,
        string $status,
        ?array $gatewayResponse = null
    ): Payment {
        $updateData = [
            'payment_status' => $status,
        ];

        if ($gatewayResponse) {
            $updateData['gateway_response'] = $gatewayResponse;
        }

        if ($status === PaymentStatusEnum::PAID->value) {
            $updateData['paid_at'] = now();
        } elseif ($status === PaymentStatusEnum::FAILED->value) {
            $updateData['failed_at'] = now();
        }

        $payment->update($updateData);
        return $payment->fresh();
    }

    /**
     * Process successful payment — updates the Payment and its related payable model
     */
    public function processSuccessfulPayment(Payment $payment, array $gatewayData): void
    {
        $this->updatePaymentStatus($payment, PaymentStatusEnum::PAID->value, $gatewayData);

        // Update payment_status on the payable (Order or ServiceBooking)
        $payment->payable?->update([
            'payment_status' => PaymentStatusEnum::PAID->value,
        ]);
    }

    /**
     * Process failed payment — updates the Payment and its related payable model
     */
    public function processFailedPayment(Payment $payment, array $gatewayData): void
    {
        $this->updatePaymentStatus($payment, PaymentStatusEnum::FAILED->value, $gatewayData);

        // Update payment_status on the payable (Order or ServiceBooking)
        $payment->payable?->update([
            'payment_status' => PaymentStatusEnum::FAILED->value,
        ]);
    }

    /**
     * Find a payment by order_code — searches both Order and ServiceBooking payables
     */
    public function getPaymentByOrderCode(string $orderCode): ?Payment
    {
        return Payment::where(function ($q) use ($orderCode) {
            $q->where('payable_type', (new Order)->getMorphClass())
                ->whereHasMorph('payable', [Order::class], function ($q) use ($orderCode) {
                    $q->where('order_code', $orderCode);
                });
        })->orWhere(function ($q) use ($orderCode) {
            $q->where('payable_type', (new ServiceBooking)->getMorphClass())
                ->whereHasMorph('payable', [ServiceBooking::class], function ($q) use ($orderCode) {
                    $q->where('order_code', $orderCode);
                });
        })->latest()->first();
    }

    /**
     * Get payment by transaction ID
     */
    public function getPaymentByTransactionId(string $transactionId): ?Payment
    {
        return Payment::where('transaction_id', $transactionId)->first();
    }
}
