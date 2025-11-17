<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * SSL Commerz Payment Gateway Service
 * 
 * This service handles all interactions with the SSL Commerz payment gateway API.
 * It provides methods to initiate payments, validate transactions, and handle callbacks.
 * 
 * SSL Commerz Documentation: https://developer.sslcommerz.com/
 * 
 * Sandbox Testing Credentials:
 * - Store ID: Use your sandbox store ID from SSL Commerz dashboard
 * - Store Password: Use your sandbox store password
 * 
 * Integration Flow:
 * 1. Initiate Payment Session - Send payment details to SSL Commerz
 * 2. Redirect Customer - Redirect to SSL Commerz gateway page
 * 3. Customer Pays - Customer completes payment on gateway
 * 4. Callback Handling - Handle success/fail/cancel callbacks
 * 5. IPN Validation - Validate transaction with SSL Commerz API
 * 6. Update Transaction Status - Update database with final status
 * 
 * @package App\Services
 */
class SSLCommerzService
{
    /**
     * SSL Commerz API base URLs
     */
    private const SANDBOX_URL = 'https://sandbox.sslcommerz.com';
    private const LIVE_URL = 'https://securepay.sslcommerz.com';

    /**
     * API endpoints
     */
    private const INIT_ENDPOINT = '/gwprocess/v4/api.php';
    private const VALIDATION_ENDPOINT = '/validator/api/validationserverAPI.php';
    private const REFUND_ENDPOINT = '/validator/api/merchantTransIDvalidationAPI.php';

    /**
     * Store credentials
     */
    private string $storeId;
    private string $storePassword;
    private bool $isSandbox;
    private string $baseUrl;

    /**
     * Constructor
     * 
     * Initializes the service with SSL Commerz credentials from config.
     * In sandbox mode, uses sandbox API URL, otherwise uses live URL.
     */
    public function __construct()
    {
        // Load credentials from environment configuration
        $this->storeId = config('sslcommerz.store_id');
        $this->storePassword = config('sslcommerz.store_password');
        $this->isSandbox = config('sslcommerz.sandbox', true);
        
        // Set appropriate API base URL
        $this->baseUrl = $this->isSandbox ? self::SANDBOX_URL : self::LIVE_URL;
    }

    /**
     * Initiate Payment Session
     * 
     * Creates a payment session with SSL Commerz and returns the gateway URL
     * where customer should be redirected to complete payment.
     * 
     * @param array $paymentData Payment details including customer info, amount, product details
     * @return array Contains status, gateway URL, transaction ID, and session key
     * @throws \Exception If payment initiation fails
     */
    public function initiatePayment(array $paymentData): array
    {
        // Generate unique transaction ID
        $transactionId = 'TXN' . time() . rand(1000, 9999);

        // Prepare SSL Commerz API request data
        $requestData = [
            // Store credentials
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            
            // Transaction details
            'total_amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'BDT',
            'tran_id' => $transactionId,
            
            // Product information
            'product_name' => $paymentData['product_name'],
            'product_category' => $paymentData['product_category'] ?? 'General',
            'product_profile' => 'general', // general, physical-goods, non-physical-goods, airline-tickets, travel-vertical
            
            // Customer information
            'cus_name' => $paymentData['customer_name'],
            'cus_email' => $paymentData['customer_email'],
            'cus_add1' => $paymentData['customer_address'] ?? 'N/A',
            'cus_city' => $paymentData['customer_city'] ?? 'Dhaka',
            'cus_postcode' => $paymentData['customer_postcode'] ?? '1000',
            'cus_country' => $paymentData['customer_country'] ?? 'Bangladesh',
            'cus_phone' => $paymentData['customer_phone'] ?? '01700000000',
            
            // Shipping information (required even if not applicable)
            'shipping_method' => 'NO',
            'ship_name' => $paymentData['customer_name'],
            'ship_add1' => $paymentData['customer_address'] ?? 'N/A',
            'ship_city' => $paymentData['customer_city'] ?? 'Dhaka',
            'ship_postcode' => $paymentData['customer_postcode'] ?? '1000',
            'ship_country' => $paymentData['customer_country'] ?? 'Bangladesh',
            
            // Callback URLs - where SSL Commerz will send customer after payment
            'success_url' => route('payment.success'),
            'fail_url' => route('payment.fail'),
            'cancel_url' => route('payment.cancel'),
            'ipn_url' => route('payment.ipn'), // IPN = Instant Payment Notification (server-to-server)
            
            // Additional custom values (optional, for your reference)
            'value_a' => $paymentData['value_a'] ?? null,
            'value_b' => $paymentData['value_b'] ?? null,
            'value_c' => $paymentData['value_c'] ?? null,
            'value_d' => $paymentData['value_d'] ?? null,
        ];

        // Save transaction to database with pending status
        $transaction = Transaction::create([
            'transaction_id' => $transactionId,
            'customer_name' => $paymentData['customer_name'],
            'customer_email' => $paymentData['customer_email'],
            'customer_phone' => $paymentData['customer_phone'] ?? null,
            'customer_address' => $paymentData['customer_address'] ?? null,
            'customer_city' => $paymentData['customer_city'] ?? 'Dhaka',
            'customer_postcode' => $paymentData['customer_postcode'] ?? '1000',
            'customer_country' => $paymentData['customer_country'] ?? 'Bangladesh',
            'product_name' => $paymentData['product_name'],
            'product_description' => $paymentData['product_description'] ?? null,
            'product_category' => $paymentData['product_category'] ?? 'General',
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'BDT',
            'status' => Transaction::STATUS_PENDING,
            'value_a' => $paymentData['value_a'] ?? null,
            'value_b' => $paymentData['value_b'] ?? null,
            'value_c' => $paymentData['value_c'] ?? null,
            'value_d' => $paymentData['value_d'] ?? null,
        ]);

        try {
            // Send HTTP POST request to SSL Commerz API
            $response = Http::asForm()->post($this->baseUrl . self::INIT_ENDPOINT, $requestData);
            
            // Parse JSON response
            $responseData = $response->json();
            
            // Log API response for debugging
            Log::info('SSL Commerz Initiation Response', [
                'transaction_id' => $transactionId,
                'response' => $responseData
            ]);

            // Check if payment session was created successfully
            if (isset($responseData['status']) && $responseData['status'] === 'SUCCESS') {
                // Update transaction with gateway information
                $transaction->update([
                    'order_id' => $responseData['tran_id'] ?? $transactionId,
                    'sessionkey' => $responseData['sessionkey'] ?? null,
                    'gateway_page_url' => $responseData['GatewayPageURL'] ?? null,
                    'api_response' => json_encode($responseData),
                    'status' => Transaction::STATUS_PROCESSING,
                ]);

                return [
                    'success' => true,
                    'gateway_url' => $responseData['GatewayPageURL'],
                    'transaction_id' => $transactionId,
                    'sessionkey' => $responseData['sessionkey'],
                    'message' => 'Payment session created successfully'
                ];
            } else {
                // Payment initiation failed
                $transaction->update([
                    'status' => Transaction::STATUS_FAILED,
                    'api_response' => json_encode($responseData),
                ]);

                return [
                    'success' => false,
                    'message' => $responseData['failedreason'] ?? 'Failed to initiate payment',
                    'error' => $responseData
                ];
            }
        } catch (\Exception $e) {
            // Handle exceptions (network errors, etc.)
            Log::error('SSL Commerz Payment Initiation Error', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            $transaction->update([
                'status' => Transaction::STATUS_FAILED,
                'api_response' => json_encode(['error' => $e->getMessage()]),
            ]);

            throw $e;
        }
    }

    /**
     * Validate Transaction
     * 
     * Validates a transaction with SSL Commerz to ensure it's legitimate.
     * This should be called after receiving success/IPN callback to verify
     * that the payment was actually processed by SSL Commerz.
     * 
     * Important: Always validate transactions to prevent fraud!
     * 
     * @param string $valId Validation ID from SSL Commerz callback
     * @param string $transactionId Your transaction ID
     * @param float $amount Transaction amount
     * @return array Validation result with status and details
     */
    public function validateTransaction(string $valId, string $transactionId, float $amount): array
    {
        try {
            // Prepare validation request
            $validationData = [
                'val_id' => $valId,
                'store_id' => $this->storeId,
                'store_passwd' => $this->storePassword,
                'format' => 'json'
            ];

            // Send validation request to SSL Commerz
            $response = Http::asForm()->get($this->baseUrl . self::VALIDATION_ENDPOINT, $validationData);
            $responseData = $response->json();

            // Log validation response
            Log::info('SSL Commerz Validation Response', [
                'transaction_id' => $transactionId,
                'val_id' => $valId,
                'response' => $responseData
            ]);

            // Check validation status
            if (isset($responseData['status']) && $responseData['status'] === 'VALID') {
                // Additional checks for security
                $isAmountMatched = floatval($responseData['amount']) == floatval($amount);
                $isTransactionMatched = $responseData['tran_id'] === $transactionId;

                if ($isAmountMatched && $isTransactionMatched) {
                    return [
                        'success' => true,
                        'validated' => true,
                        'message' => 'Transaction validated successfully',
                        'data' => $responseData
                    ];
                } else {
                    // Amount or transaction ID mismatch - possible fraud attempt!
                    Log::warning('Transaction Validation Mismatch', [
                        'transaction_id' => $transactionId,
                        'expected_amount' => $amount,
                        'received_amount' => $responseData['amount'],
                        'amount_matched' => $isAmountMatched,
                        'transaction_matched' => $isTransactionMatched
                    ]);

                    return [
                        'success' => false,
                        'validated' => false,
                        'message' => 'Transaction validation failed - data mismatch',
                        'data' => $responseData
                    ];
                }
            } else if (isset($responseData['status']) && in_array($responseData['status'], ['FAILED', 'CANCELLED'])) {
                return [
                    'success' => false,
                    'validated' => false,
                    'message' => 'Transaction ' . strtolower($responseData['status']),
                    'data' => $responseData
                ];
            } else {
                return [
                    'success' => false,
                    'validated' => false,
                    'message' => 'Invalid validation response',
                    'data' => $responseData
                ];
            }
        } catch (\Exception $e) {
            Log::error('SSL Commerz Validation Error', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'validated' => false,
                'message' => 'Validation request failed: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle Success Callback
     * 
     * Processes the success callback from SSL Commerz after successful payment.
     * Updates transaction status and validates the payment.
     * 
     * @param array $callbackData POST data from SSL Commerz
     * @return Transaction Updated transaction model
     */
    public function handleSuccessCallback(array $callbackData): Transaction
    {
        $transactionId = $callbackData['tran_id'];
        $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();

        // Validate transaction with SSL Commerz
        if (isset($callbackData['val_id'])) {
            $validation = $this->validateTransaction(
                $callbackData['val_id'],
                $transactionId,
                $transaction->amount
            );

            if ($validation['validated']) {
                // Update transaction with success status
                $transaction->update([
                    'status' => Transaction::STATUS_SUCCESS,
                    'gateway_transaction_id' => $callbackData['tran_id'] ?? null,
                    'bank_transaction_id' => $callbackData['bank_tran_id'] ?? null,
                    'card_type' => $callbackData['card_type'] ?? null,
                    'card_no' => $callbackData['card_no'] ?? null,
                    'card_issuer' => $callbackData['card_issuer'] ?? null,
                    'card_brand' => $callbackData['card_brand'] ?? null,
                    'card_issuer_country' => $callbackData['card_issuer_country'] ?? null,
                    'payment_method' => $callbackData['card_type'] ?? null,
                    'paid_amount' => $callbackData['amount'] ?? $transaction->amount,
                    'validation_status' => $callbackData['status'] ?? null,
                    'risk_level' => $callbackData['risk_level'] ?? null,
                    'risk_title' => $callbackData['risk_title'] ?? null,
                    'api_response' => json_encode($callbackData),
                    'paid_at' => now(),
                ]);
            } else {
                // Validation failed - mark as failed
                $transaction->markAsFailed();
                $transaction->update([
                    'api_response' => json_encode($callbackData),
                ]);
            }
        } else {
            // No validation ID provided - suspicious
            $transaction->markAsFailed();
        }

        return $transaction;
    }

    /**
     * Handle Failed Callback
     * 
     * Processes the fail callback from SSL Commerz when payment fails.
     * 
     * @param array $callbackData POST data from SSL Commerz
     * @return Transaction Updated transaction model
     */
    public function handleFailCallback(array $callbackData): Transaction
    {
        $transactionId = $callbackData['tran_id'];
        $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();

        $transaction->update([
            'status' => Transaction::STATUS_FAILED,
            'api_response' => json_encode($callbackData),
        ]);

        return $transaction;
    }

    /**
     * Handle Cancel Callback
     * 
     * Processes the cancel callback from SSL Commerz when customer cancels payment.
     * 
     * @param array $callbackData POST data from SSL Commerz
     * @return Transaction Updated transaction model
     */
    public function handleCancelCallback(array $callbackData): Transaction
    {
        $transactionId = $callbackData['tran_id'];
        $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();

        $transaction->update([
            'status' => Transaction::STATUS_CANCELLED,
            'api_response' => json_encode($callbackData),
        ]);

        return $transaction;
    }

    /**
     * Handle IPN (Instant Payment Notification)
     * 
     * Processes IPN callback from SSL Commerz. This is a server-to-server
     * notification that should be processed even if browser callbacks fail.
     * 
     * IPN is more reliable than browser redirects as it's not affected by
     * customer closing the browser or network issues.
     * 
     * @param array $ipnData POST data from SSL Commerz IPN
     * @return Transaction Updated transaction model
     */
    public function handleIPN(array $ipnData): Transaction
    {
        $transactionId = $ipnData['tran_id'];
        $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();

        // Store IPN data
        $transaction->ipn_response = json_encode($ipnData);

        // Validate and update transaction
        if (isset($ipnData['val_id'])) {
            $validation = $this->validateTransaction(
                $ipnData['val_id'],
                $transactionId,
                $transaction->amount
            );

            if ($validation['validated'] && $ipnData['status'] === 'VALID') {
                $transaction->update([
                    'status' => Transaction::STATUS_SUCCESS,
                    'gateway_transaction_id' => $ipnData['tran_id'] ?? null,
                    'bank_transaction_id' => $ipnData['bank_tran_id'] ?? null,
                    'card_type' => $ipnData['card_type'] ?? null,
                    'card_no' => $ipnData['card_no'] ?? null,
                    'card_issuer' => $ipnData['card_issuer'] ?? null,
                    'card_brand' => $ipnData['card_brand'] ?? null,
                    'payment_method' => $ipnData['card_type'] ?? null,
                    'paid_amount' => $ipnData['amount'] ?? $transaction->amount,
                    'validation_status' => $ipnData['status'] ?? null,
                    'ipn_response' => json_encode($ipnData),
                    'paid_at' => now(),
                ]);
            }
        }

        $transaction->save();
        return $transaction;
    }

    /**
     * Get Transaction Status
     * 
     * Retrieves the current status of a transaction from database.
     * 
     * @param string $transactionId Transaction ID
     * @return Transaction|null Transaction model or null if not found
     */
    public function getTransactionStatus(string $transactionId): ?Transaction
    {
        return Transaction::where('transaction_id', $transactionId)->first();
    }
}
