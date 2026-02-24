<?php

namespace App\Services;

use App\Enums\Purchase\PaymentStatusEnum;
use App\Models\Payment\Payment;
use App\Models\Purchase\Order;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Create a payment record for an order
     */
    public function createPayment(Order $order, string $gateway, string $transactionId): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'payment_gateway' => $gateway,
            'payment_status' => PaymentStatusEnum::INITIATED->value,
            'transaction_id' => $transactionId,
            'reference_id' => 'REF-' . $order->order_code,
            'amount' => $order->price,
            'currency' => 'NPR',
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
     * Process successful payment
     */
    public function processSuccessfulPayment(Payment $payment, array $gatewayData): void
    {
        // Update payment status
        $this->updatePaymentStatus($payment, PaymentStatusEnum::PAID->value, $gatewayData);

        // Update order payment status
        $payment->order->update([
            'payment_status' => PaymentStatusEnum::PAID->value,
        ]);
    }

    /**
     * Process failed payment
     */
    public function processFailedPayment(Payment $payment, array $gatewayData): void
    {
        // Update payment status
        $this->updatePaymentStatus($payment, PaymentStatusEnum::FAILED->value, $gatewayData);

        // Update order payment status
        $payment->order->update([
            'payment_status' => PaymentStatusEnum::FAILED->value,
        ]);
    }

    /**
     * Get payment by order code
     */
    public function getPaymentByOrderCode(string $orderCode): ?Payment
    {
        return Payment::whereHas('order', function ($query) use ($orderCode) {
            $query->where('order_code', $orderCode);
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
