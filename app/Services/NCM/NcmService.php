<?php

namespace App\Services\NCM;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NcmService
{
    protected $baseUrl;
    protected $token;
    /**
     * Create a new NcmOrderService instance.
     */
    public function __construct()
    {
        $this->baseUrl = env('NCM_API_URL');
        $this->token = env('NCM_API_TOKEN');
    }
    /**
     * Get all branches with details
     */
    public function getBranches()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Token {$this->token}",
            ])->get("{$this->baseUrl}/api/v2/branches");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch branches',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('NCM Get Branches Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    /**
     * Calculate delivery charge between branches
     */
    public function getShippingRate($fromBranch, $toBranch, $deliveryType = 'Door2Door')
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Token {$this->token}",
            ])->get("{$this->baseUrl}/api/v1/shipping-rate", [
                'creation' => $fromBranch,
                'destination' => $toBranch,
                'type' => $deliveryType
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to calculate shipping rate',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('NCM Shipping Rate Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create order in NCM
     */
    public function createOrder($orderData)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Token {$this->token}",
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/v1/order/create", $orderData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create order in NCM',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('NCM Create Order Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    /**
     * Get order details
     */
    public function getOrderDetails($ncmOrderId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Token {$this->token}",
            ])->get("{$this->baseUrl}/api/v1/order", [
                'id' => $ncmOrderId
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Order not found',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('NCM Get Order Details Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get order status
     */
    public function getOrderStatus($ncmOrderId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Token {$this->token}",
            ])->get("{$this->baseUrl}/api/v1/order/status", [
                'id' => $ncmOrderId
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch order status',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('NCM Get Order Status Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get bulk order statuses
     */
    public function getBulkOrderStatuses(array $orderIds)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Token {$this->token}",
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/v1/orders/statuses", [
                'orders' => $orderIds
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch bulk statuses',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('NCM Bulk Status Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get order comments
     */
    public function getOrderComments($ncmOrderId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Token {$this->token}",
            ])->get("{$this->baseUrl}/api/v1/order/comment", [
                'id' => $ncmOrderId
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch comments',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('NCM Get Comments Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get last 25 order comments
     */
    public function getBulkComments()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Token {$this->token}",
            ])->get("{$this->baseUrl}/api/v1/order/getbulkcomments");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch bulk comments',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('NCM Bulk Comments Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create order comment
     */
    public function createComment($ncmOrderId, $comment)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Token {$this->token}",
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/v1/comment", [
                'orderid' => $ncmOrderId,
                'comments' => $comment
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create comment',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('NCM Create Comment Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Return order
     */
    public function returnOrder($ncmOrderId, $comment = null)
    {
        try {
            $data = ['pk' => $ncmOrderId];
            if ($comment) {
                $data['comment'] = $comment;
            }

            $response = Http::withHeaders([
                'Authorization' => "Token {$this->token}",
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/v2/vendor/order/return", $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to return order',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('NCM Return Order Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
