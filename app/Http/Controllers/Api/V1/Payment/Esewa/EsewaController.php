<?php

namespace App\Http\Controllers\Api\V1\Payment\Esewa;

use App\Enums\Purchase\PaymentMethodEnum;
use App\Enums\Purchase\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PaymentInitiateRequest;
use App\Models\Purchase\Order;
use App\Services\Payment\EsewaService;
use App\Services\PaymentService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class EsewaController extends Controller
{
    use ResponseTrait;

    protected EsewaService $esewaService;
    protected PaymentService $paymentService;

    public function __construct(EsewaService $esewaService, PaymentService $paymentService)
    {
        $this->esewaService = $esewaService;
        $this->paymentService = $paymentService;
    }

    /**
     * @OA\Post(
     *     path="/payment/esewa/initiate",
     *     summary="Initiate eSewa payment",
     *     description="Initiate payment for an existing order using eSewa",
     *     operationId="initiateEsewaPayment",
     *     tags={"Payment - eSewa"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_code"},
     *             @OA\Property(property="order_code", type="string", example="ABC123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment initiated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment initiated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="payment_url", type="string", example="https://rc-epay.esewa.com.np/api/epay/main/v2/form"),
     *                 @OA\Property(property="transaction_uuid", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="amount", type="number", example=1500.00),
     *                 @OA\Property(
     *                     property="payload",
     *                     type="object",
     *                     description="Form data to submit to eSewa"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function initiate(PaymentInitiateRequest $request)
    {
        try {
            $order = Order::where('order_code', $request->order_code)->firstOrFail();

            // Validate order can be paid
            if ($order->payment_method !== PaymentMethodEnum::ESEWA->value) {
                return $this->apiError('This order is not configured for eSewa payment', 400);
            }

            if ($order->payment_status === PaymentStatusEnum::PAID->value) {
                return $this->apiError('This order has already been paid', 400);
            }

            if (!in_array($order->payment_status, [PaymentStatusEnum::PENDING->value, PaymentStatusEnum::FAILED->value])) {
                return $this->apiError('This order cannot be paid at this time', 400);
            }

            // Initiate payment with eSewa
            $paymentData = $this->esewaService->initiate($order);

            // Create payment record
            $payment = $this->paymentService->createPayment(
                $order,
                'eSewa',
                $paymentData['transaction_uuid']
            );

            return $this->apiSuccess('Payment initiated successfully', [
                'payment_url' => $paymentData['payment_url'],
                'transaction_uuid' => $paymentData['transaction_uuid'],
                'amount' => $order->price,
                'payload' => $paymentData['payload'],
            ]);
        } catch (\Exception $e) {
            Log::error('eSewa payment initiation error: ' . $e->getMessage());
            return $this->apiError('Failed to initiate payment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/payment/esewa/success",
     *     summary="eSewa success callback",
     *     description="Handle successful payment callback from eSewa",
     *     operationId="esewaSuccessCallback",
     *     tags={"Payment - eSewa"},
     *     @OA\Parameter(
     *         name="transaction_uuid",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="total_amount",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(response=200, description="Payment processed successfully")
     * )
     */
    public function success(Request $request)
    {
        try {
            // Handle callback
            $agent = new Agent();
            $callbackResult = $this->esewaService->handleCallback($request);

            if (!$callbackResult['success']) {
                // For error cases, redirect with error message
                $errorUrl = $agent->isMobile()
                    ? env('MOBILE_APP_URL', env('FRONTEND_URL')) . '/payment-failed?message=' . urlencode($callbackResult['message'])
                    : env('FRONTEND_URL') . '/payment-failed?message=' . urlencode($callbackResult['message']);

                return redirect()->away($errorUrl);
            }

            // Get payment record
            $payment = $this->paymentService->getPaymentByTransactionId($callbackResult['data']['transaction_uuid']);

            if (!$payment) {
                $errorUrl = $agent->isMobile()
                    ? env('MOBILE_APP_URL', env('FRONTEND_URL')) . '/payment-failed?message=' . urlencode('Payment record not found')
                    : env('FRONTEND_URL') . '/payment-failed?message=' . urlencode('Payment record not found');

                return redirect()->away($errorUrl);
            }

            // Process successful payment
            DB::transaction(function () use ($payment, $callbackResult) {
                $this->paymentService->processSuccessfulPayment($payment, $callbackResult['data']);
            });

            // Prepare response data
            $responseData = [
                'order_code' => $payment->payable?->order_code,
                'amount' => $payment->amount,
                'transaction_id' => $payment->transaction_id,
                'transaction_code' => $callbackResult['data']['transaction_code'] ?? null,
            ];

            // Redirect based on device type
            if ($agent->isMobile()) {
                // Mobile redirect
                $redirectUrl = env('MOBILE_APP_URL', env('FRONTEND_URL')) . '/payment-success?data=' . urlencode(json_encode($responseData));
            } else {
                // Web redirect
                $redirectUrl = env('FRONTEND_URL') . '/thank-you?data=' . urlencode(json_encode($responseData));
            }

            return redirect()->away($redirectUrl);
        } catch (\Exception $e) {
            Log::error('eSewa success callback error: ' . $e->getMessage());

            $agent = new Agent();
            $errorUrl = $agent->isMobile()
                ? env('MOBILE_APP_URL', env('FRONTEND_URL')) . '/payment-failed?message=' . urlencode('Failed to process payment')
                : env('FRONTEND_URL') . '/payment-failed?message=' . urlencode('Failed to process payment');

            return redirect()->away($errorUrl);
        }
    }

    /**
     * @OA\Get(
     *     path="/payment/esewa/failure",
     *     summary="eSewa failure callback",
     *     description="Handle failed payment callback from eSewa",
     *     operationId="esewaFailureCallback",
     *     tags={"Payment - eSewa"},
     *     @OA\Parameter(
     *         name="transaction_uuid",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Payment failure processed")
     * )
     */
    public function failure(Request $request)
    {
        // Log everything eSewa sends so we can inspect it
        Log::info('eSewa failure callback received', $request->all());

        try {
            $agent = new Agent();

            // eSewa sends transaction_uuid as a plain query param on failure
            // (no base64-encoded 'data' like on success)
            $transactionUuid = $request->query('transaction_uuid');

            if (!$transactionUuid) {
                $errorUrl = $agent->isMobile()
                    ? env('MOBILE_APP_URL', env('FRONTEND_URL')) . '/payment-failed?message=' . urlencode('Transaction not identified')
                    : env('FRONTEND_URL') . '/payment-failed?message=' . urlencode('Transaction not identified');

                return redirect()->away($errorUrl);
            }

            // Find the payment record by transaction UUID
            $payment = $this->paymentService->getPaymentByTransactionId($transactionUuid);

            if (!$payment) {
                Log::warning('eSewa failure: payment record not found for uuid: ' . $transactionUuid);

                $errorUrl = $agent->isMobile()
                    ? env('MOBILE_APP_URL', env('FRONTEND_URL')) . '/payment-failed?message=' . urlencode('Payment record not found')
                    : env('FRONTEND_URL') . '/payment-failed?message=' . urlencode('Payment record not found');

                return redirect()->away($errorUrl);
            }

            // Mark payment as failed
            DB::transaction(function () use ($payment, $request) {
                $this->paymentService->processFailedPayment($payment, $request->all());
            });

            $errorUrl = $agent->isMobile()
                ? env('MOBILE_APP_URL', env('FRONTEND_URL')) . '/payment-failed?order_code=' . ($payment->payable?->order_code) . '&message=' . urlencode('Payment was not completed')
                : env('FRONTEND_URL') . '/payment-failed?order_code=' . ($payment->payable?->order_code) . '&message=' . urlencode('Payment was not completed');

            return redirect()->away($errorUrl);
        } catch (\Exception $e) {
            Log::error('eSewa failure callback error: ' . $e->getMessage());

            $agent = new Agent();
            $errorUrl = $agent->isMobile()
                ? env('MOBILE_APP_URL', env('FRONTEND_URL')) . '/payment-failed?message=' . urlencode('Failed to process payment failure')
                : env('FRONTEND_URL') . '/payment-failed?message=' . urlencode('Failed to process payment failure');

            return redirect()->away($errorUrl);
        }
    }

    /**
     * @OA\Post(
     *     path="/payment/esewa/verify",
     *     summary="Verify eSewa payment",
     *     description="Manually verify payment status with eSewa",
     *     operationId="verifyEsewaPayment",
     *     tags={"Payment - eSewa"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"transaction_uuid"},
     *             @OA\Property(property="transaction_uuid", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment verification result")
     * )
     */
    public function verify(Request $request)
    {
        $request->validate([
            'transaction_uuid' => 'required|string',
        ]);

        try {
            $verificationResult = $this->esewaService->verify($request->transaction_uuid);

            return $this->apiSuccess('Verification completed', $verificationResult);
        } catch (\Exception $e) {
            Log::error('eSewa verification error: ' . $e->getMessage());
            return $this->apiError('Verification failed: ' . $e->getMessage(), 500);
        }
    }
}
