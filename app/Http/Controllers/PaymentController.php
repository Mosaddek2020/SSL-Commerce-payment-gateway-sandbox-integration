<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\SSLCommerzService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Payment Controller
 * 
 * This controller handles all payment-related operations including:
 * - Displaying payment form
 * - Initiating payment with SSL Commerz
 * - Handling success/fail/cancel callbacks
 * - Processing IPN (Instant Payment Notification)
 * - Displaying transaction status
 * 
 * Payment Flow:
 * 1. Customer fills payment form (showPaymentForm)
 * 2. Form submission creates transaction and redirects to SSL Commerz (initiatePayment)
 * 3. Customer completes payment on SSL Commerz gateway
 * 4. SSL Commerz redirects back based on payment result:
 *    - Success -> handleSuccess()
 *    - Failure -> handleFail()
 *    - Cancelled -> handleCancel()
 * 5. SSL Commerz also sends IPN notification (handleIPN)
 * 6. Customer sees transaction status (showTransactionStatus)
 * 
 * @package App\Http\Controllers
 */
class PaymentController extends Controller
{
    /**
     * SSL Commerz Service instance
     * 
     * @var SSLCommerzService
     */
    protected SSLCommerzService $sslCommerzService;

    /**
     * Constructor
     * 
     * Inject SSLCommerzService dependency for payment processing.
     */
    public function __construct(SSLCommerzService $sslCommerzService)
    {
        $this->sslCommerzService = $sslCommerzService;
    }

    /**
     * Display Payment Form
     * 
     * Shows the payment form where customers enter their details
     * and select products/services to purchase.
     * 
     * @return \Illuminate\View\View
     */
    public function showPaymentForm()
    {
        // In a real application, you might load product details from database
        // For this demo, we'll pass some sample data to the view
        
        return view('payment.form', [
            'title' => 'SSL Commerz Payment Gateway',
            'products' => $this->getSampleProducts(),
        ]);
    }

    /**
     * Initiate Payment
     * 
     * Processes the payment form submission and initiates payment session
     * with SSL Commerz. Redirects customer to SSL Commerz gateway page.
     * 
     * @param Request $request Payment form data
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initiatePayment(Request $request)
    {
        // Validate payment form data
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'nullable|string',
            'customer_city' => 'nullable|string|max:100',
            'customer_postcode' => 'nullable|string|max:20',
            'product_name' => 'required|string|max:255',
            'product_description' => 'nullable|string',
            'product_category' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:10', // Minimum 10 BDT
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Prepare payment data
            $paymentData = [
                // Customer information
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address ?? 'N/A',
                'customer_city' => $request->customer_city ?? 'Dhaka',
                'customer_postcode' => $request->customer_postcode ?? '1000',
                'customer_country' => $request->customer_country ?? 'Bangladesh',
                
                // Product/Service information
                'product_name' => $request->product_name,
                'product_description' => $request->product_description,
                'product_category' => $request->product_category ?? 'General',
                
                // Amount
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'BDT',
                
                // Optional custom values for your reference
                'value_a' => $request->value_a,
                'value_b' => $request->value_b,
                'value_c' => $request->value_c,
                'value_d' => $request->value_d,
            ];

            // Initiate payment with SSL Commerz
            $response = $this->sslCommerzService->initiatePayment($paymentData);

            // Check if payment initiation was successful
            if ($response['success']) {
                // Redirect customer to SSL Commerz payment gateway
                return redirect()->away($response['gateway_url']);
            } else {
                // Payment initiation failed, show error message
                return redirect()->back()
                    ->with('error', $response['message'])
                    ->withInput();
            }
        } catch (\Exception $e) {
            // Log error and show generic error message
            Log::error('Payment Initiation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to initiate payment. Please try again later.')
                ->withInput();
        }
    }

    /**
     * Handle Success Callback
     * 
     * SSL Commerz redirects customer here after successful payment.
     * Validates the transaction and displays success message.
     * 
     * Security: Always validate transaction with SSL Commerz API
     * to ensure the payment is legitimate and prevent fraud.
     * 
     * @param Request $request Callback data from SSL Commerz
     * @return \Illuminate\View\View
     */
    public function handleSuccess(Request $request)
    {
        try {
            // Get all callback data from SSL Commerz
            $callbackData = $request->all();

            // Log callback for debugging
            Log::info('SSL Commerz Success Callback', $callbackData);

            // Process success callback and validate transaction
            $transaction = $this->sslCommerzService->handleSuccessCallback($callbackData);

            // Check if transaction is successfully validated
            if ($transaction->isSuccess()) {
                return view('payment.success', [
                    'transaction' => $transaction,
                    'message' => 'Payment completed successfully!'
                ]);
            } else {
                // Transaction validation failed
                return view('payment.failed', [
                    'transaction' => $transaction,
                    'message' => 'Payment validation failed. Please contact support.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Success Callback Error', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return view('payment.failed', [
                'message' => 'An error occurred while processing your payment.'
            ]);
        }
    }

    /**
     * Handle Fail Callback
     * 
     * SSL Commerz redirects customer here if payment fails.
     * Updates transaction status and shows failure message.
     * 
     * @param Request $request Callback data from SSL Commerz
     * @return \Illuminate\View\View
     */
    public function handleFail(Request $request)
    {
        try {
            // Get callback data
            $callbackData = $request->all();

            // Log callback
            Log::info('SSL Commerz Fail Callback', $callbackData);

            // Process fail callback
            $transaction = $this->sslCommerzService->handleFailCallback($callbackData);

            return view('payment.failed', [
                'transaction' => $transaction,
                'message' => 'Payment failed. Please try again or contact support.'
            ]);
        } catch (\Exception $e) {
            Log::error('Fail Callback Error', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return view('payment.failed', [
                'message' => 'Payment failed. Please try again.'
            ]);
        }
    }

    /**
     * Handle Cancel Callback
     * 
     * SSL Commerz redirects customer here if they cancel the payment.
     * Updates transaction status and shows cancellation message.
     * 
     * @param Request $request Callback data from SSL Commerz
     * @return \Illuminate\View\View
     */
    public function handleCancel(Request $request)
    {
        try {
            // Get callback data
            $callbackData = $request->all();

            // Log callback
            Log::info('SSL Commerz Cancel Callback', $callbackData);

            // Process cancel callback
            $transaction = $this->sslCommerzService->handleCancelCallback($callbackData);

            return view('payment.cancelled', [
                'transaction' => $transaction,
                'message' => 'Payment was cancelled. You can try again if you wish.'
            ]);
        } catch (\Exception $e) {
            Log::error('Cancel Callback Error', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return view('payment.cancelled', [
                'message' => 'Payment was cancelled.'
            ]);
        }
    }

    /**
     * Handle IPN (Instant Payment Notification)
     * 
     * SSL Commerz sends server-to-server notification here after payment processing.
     * This is more reliable than browser redirects.
     * 
     * IPN Advantages:
     * - Works even if customer closes browser
     * - Not affected by network issues on customer side
     * - Guaranteed delivery from SSL Commerz
     * 
     * Important: This endpoint should be publicly accessible!
     * 
     * @param Request $request IPN data from SSL Commerz
     * @return \Illuminate\Http\Response
     */
    public function handleIPN(Request $request)
    {
        try {
            // Get IPN data
            $ipnData = $request->all();

            // Log IPN for debugging
            Log::info('SSL Commerz IPN Received', $ipnData);

            // Process IPN and update transaction
            $transaction = $this->sslCommerzService->handleIPN($ipnData);

            // Return success response to SSL Commerz
            return response()->json([
                'success' => true,
                'message' => 'IPN processed successfully'
            ], 200);
        } catch (\Exception $e) {
            // Log error
            Log::error('IPN Processing Error', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'IPN processing failed'
            ], 500);
        }
    }

    /**
     * Show Transaction Status
     * 
     * Displays detailed information about a specific transaction.
     * Customers can use this to check their payment status.
     * 
     * @param string $transactionId Transaction ID
     * @return \Illuminate\View\View
     */
    public function showTransactionStatus(string $transactionId)
    {
        try {
            // Find transaction
            $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();

            return view('payment.status', [
                'transaction' => $transaction
            ]);
        } catch (\Exception $e) {
            return view('payment.status', [
                'error' => 'Transaction not found'
            ]);
        }
    }

    /**
     * List All Transactions
     * 
     * Admin view to see all transactions.
     * In production, add authentication and authorization!
     * 
     * @return \Illuminate\View\View
     */
    public function listTransactions()
    {
        // Get all transactions with pagination
        $transactions = Transaction::orderBy('created_at', 'desc')
            ->paginate(20);

        return view('payment.list', [
            'transactions' => $transactions
        ]);
    }

    /**
     * Get Sample Products
     * 
     * Returns sample products for demonstration.
     * In a real application, this would query your products database.
     * 
     * @return array
     */
    private function getSampleProducts(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Premium Package',
                'description' => '1 Year Premium Subscription with all features',
                'price' => 1000,
                'category' => 'Subscription'
            ],
            [
                'id' => 2,
                'name' => 'Basic Package',
                'description' => '6 Months Basic Subscription',
                'price' => 500,
                'category' => 'Subscription'
            ],
            [
                'id' => 3,
                'name' => 'Digital Product',
                'description' => 'E-book and Video Course Bundle',
                'price' => 250,
                'category' => 'Digital Goods'
            ],
        ];
    }
}

