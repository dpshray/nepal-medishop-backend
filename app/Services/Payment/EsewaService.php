<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EsewaService implements PaymentGatewayInterface
{
    private string $merchant_code;
    private string $secret_key;
    private string $payment_url;
    private string $verify_url;

    public function __construct()
    {
        $this->merchant_code = config('esewa.merchant_code');
        $this->secret_key = config('esewa.secret_key');
        $this->payment_url = config('esewa.payment_url');
        $this->verify_url = config('esewa.verify_url');
    }

    /**
     * Initiate payment with eSewa
     */
    public function initiate(Model $payable): array
    {
        $transaction_uuid = 'TXN' . rand(10000, 99999);

        $payload = [
            'amount' => $payable->price,
            'tax_amount' => 0,
            'total_amount' => $payable->price,
            'transaction_uuid' => $transaction_uuid,
            'product_code' => $this->merchant_code,
            'product_service_charge' => 0,
            'product_delivery_charge' => 0,
            'success_url' => route('api.esewa.success'),
            'failure_url' => route('api.esewa.failure'),
        ];

        // Fields required for signature
        $signedFieldNames = 'total_amount,transaction_uuid,product_code';

        // Build signature string
        $signatureString =
            "total_amount={$payload['total_amount']}," .
            "transaction_uuid={$payload['transaction_uuid']}," .
            "product_code={$payload['product_code']}";

        // Generate signature
        $signature = base64_encode(
            hash_hmac(
                'sha256',
                $signatureString,
                $this->secret_key,
                true
            )
        );

        return [
            'payment_url' => $this->payment_url,
            'transaction_uuid' => $transaction_uuid,
            'payload' => array_merge($payload, [
                'signed_field_names' => $signedFieldNames,
                'signature' => $signature,
            ]),
        ];
    }

    /**
     * Verify payment with eSewa
     */
    public function verify(string $transactionId, ?float $totalAmount = null): array
    {
        try {
            $params = [
                'product_code' => $this->merchant_code,
                'transaction_uuid' => $transactionId,
            ];

            // Add total_amount if provided
            if ($totalAmount !== null) {
                $params['total_amount'] = $totalAmount;
            }

            $response = Http::get($this->verify_url, $params);

            if ($response->successful()) {
                $data = $response->json();

                // Check if payment was successful
                if (isset($data['status']) && strtoupper($data['status']) === 'COMPLETE') {
                    return [
                        'success' => true,
                        'data' => $data,
                        'message' => 'Payment verified successfully',
                    ];
                }
            }

            return [
                'success' => false,
                'data' => $response->json(),
                'message' => 'Payment verification failed',
            ];
        } catch (\Exception $e) {
            Log::error('eSewa verification error: ' . $e->getMessage());

            return [
                'success' => false,
                'data' => null,
                'message' => 'Payment verification error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle callback from eSewa
     */
    public function handleCallback(Request $request): array
    {
        // eSewa sends data as base64-encoded JSON in 'data' parameter
        if (!$request->has('data')) {
            return [
                'success' => false,
                'message' => 'Missing data parameter from eSewa',
            ];
        }

        // Decode the base64 data
        $decodedData = base64_decode($request->input('data'));
        $data = json_decode($decodedData, true);

        if (!$data) {
            return [
                'success' => false,
                'message' => 'Invalid data format from eSewa',
            ];
        }

        // Validate required fields
        $requiredFields = ['transaction_uuid', 'total_amount', 'status', 'transaction_code'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return [
                    'success' => false,
                    'message' => "Missing required field: {$field}",
                ];
            }
        }

        // Validate signature
        if (!$this->validateSignature($data)) {
            return [
                'success' => false,
                'message' => 'Invalid signature from eSewa',
                'data' => $data,
            ];
        }

        // Check if status is COMPLETE
        if (strtoupper($data['status']) !== 'COMPLETE') {
            return [
                'success' => false,
                'message' => 'Payment not completed',
                'data' => $data,
            ];
        }

        // Verify the payment with eSewa API
        $verificationResult = $this->verify($data['transaction_uuid'], (float)$data['total_amount']);

        if ($verificationResult['success']) {
            // Add transaction_code to the verification result
            $verificationResult['data']['transaction_code'] = $data['transaction_code'];
            $verificationResult['data']['transaction_uuid'] = $data['transaction_uuid'];
        }

        return $verificationResult;
    }

    /**
     * Validate eSewa signature
     */
    private function validateSignature(array $data): bool
    {
        if (!isset($data['signature']) || !isset($data['signed_field_names'])) {
            return false;
        }

        $signedFields = explode(',', $data['signed_field_names']);
        $signatureString = '';

        foreach ($signedFields as $field) {
            if (isset($data[$field])) {
                $signatureString .= "{$field}={$data[$field]},";
            }
        }

        $signatureString = rtrim($signatureString, ',');

        $expectedSignature = base64_encode(
            hash_hmac('sha256', $signatureString, $this->secret_key, true)
        );

        return hash_equals($expectedSignature, $data['signature']);
    }
}
