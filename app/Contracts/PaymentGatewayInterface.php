<?php

namespace App\Contracts;

use App\Models\Purchase\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Initiate payment with the gateway
     * 
     * @param Order $order
     * @return array Payment details including payment_url
     */
    public function initiate(Order $order): array;

    /**
     * Verify payment with the gateway
     * 
     * @param string $transactionId
     * @return array Verification result
     */
    public function verify(string $transactionId): array;

    /**
     * Handle callback from payment gateway
     * 
     * @param Request $request
     * @return array Callback processing result
     */
    public function handleCallback(Request $request): array;
}
