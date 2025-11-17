<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/**
 * Home/Welcome Route
 * 
 * Redirects to payment form as the main entry point
 */
Route::get('/', function () {
    return redirect()->route('payment.form');
});

/*
|--------------------------------------------------------------------------
| Payment Routes
|--------------------------------------------------------------------------
|
| These routes handle the complete payment flow with SSL Commerz:
| 1. Display payment form
| 2. Initiate payment (redirect to SSL Commerz)
| 3. Handle callbacks (success, fail, cancel)
| 4. Process IPN (Instant Payment Notification)
| 5. Display transaction status
|
*/

/**
 * Payment Form Route
 * 
 * GET /payment
 * Displays the payment form where customers enter their details
 */
Route::get('/payment', [PaymentController::class, 'showPaymentForm'])
    ->name('payment.form');

/**
 * Payment Initiation Route
 * 
 * POST /payment/initiate
 * Processes payment form submission and initiates SSL Commerz session
 */
Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment'])
    ->name('payment.initiate');

/**
 * Payment Success Callback Route
 * 
 * POST /payment/success
 * SSL Commerz redirects here after successful payment
 * This route receives POST data with transaction details
 */
Route::post('/payment/success', [PaymentController::class, 'handleSuccess'])
    ->name('payment.success');

/**
 * Payment Fail Callback Route
 * 
 * POST /payment/fail
 * SSL Commerz redirects here if payment fails
 */
Route::post('/payment/fail', [PaymentController::class, 'handleFail'])
    ->name('payment.fail');

/**
 * Payment Cancel Callback Route
 * 
 * POST /payment/cancel
 * SSL Commerz redirects here if customer cancels payment
 */
Route::post('/payment/cancel', [PaymentController::class, 'handleCancel'])
    ->name('payment.cancel');

/**
 * IPN (Instant Payment Notification) Route
 * 
 * POST /payment/ipn
 * Server-to-server notification from SSL Commerz
 * More reliable than browser redirects
 * 
 * Important: This URL must be publicly accessible!
 * Make sure your server can receive POST requests from SSL Commerz IPs.
 */
Route::post('/payment/ipn', [PaymentController::class, 'handleIPN'])
    ->name('payment.ipn');

/**
 * Transaction Status Route
 * 
 * GET /payment/status/{transactionId}
 * Shows detailed status of a specific transaction
 */
Route::get('/payment/status/{transactionId}', [PaymentController::class, 'showTransactionStatus'])
    ->name('payment.status');

/**
 * Transaction List Route
 * 
 * GET /payment/transactions
 * Lists all transactions (for admin/testing purposes)
 * In production, protect this route with authentication!
 */
Route::get('/payment/transactions', [PaymentController::class, 'listTransactions'])
    ->name('payment.list');

