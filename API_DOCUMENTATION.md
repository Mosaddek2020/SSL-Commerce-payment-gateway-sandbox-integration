# SSL Commerz API Integration Documentation

This document provides detailed information about how the SSL Commerz payment gateway API is integrated into this Laravel application.

## Table of Contents

1. [API Overview](#api-overview)
2. [Authentication](#authentication)
3. [Payment Initiation API](#payment-initiation-api)
4. [Transaction Validation API](#transaction-validation-api)
5. [Callback Handling](#callback-handling)
6. [IPN (Instant Payment Notification)](#ipn-instant-payment-notification)
7. [Error Handling](#error-handling)
8. [Code Examples](#code-examples)

---

## API Overview

### Base URLs

**Sandbox (Testing):**
```
https://sandbox.sslcommerz.com
```

**Live (Production):**
```
https://securepay.sslcommerz.com
```

### API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/gwprocess/v4/api.php` | POST | Initiate payment session |
| `/validator/api/validationserverAPI.php` | GET | Validate transaction |
| `/validator/api/merchantTransIDvalidationAPI.php` | GET | Transaction inquiry/refund |

---

## Authentication

SSL Commerz uses Store ID and Store Password for authentication.

### Credentials

```php
$storeId = config('sslcommerz.store_id');
$storePassword = config('sslcommerz.store_password');
```

### Security Notes

- Never expose credentials in client-side code
- Store credentials in `.env` file
- Use different credentials for sandbox and live environments
- Rotate credentials periodically

---

## Payment Initiation API

### Purpose

Creates a payment session with SSL Commerz and returns a gateway URL where the customer will complete the payment.

### Endpoint

```
POST /gwprocess/v4/api.php
```

### Request Parameters

#### Required Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `store_id` | string | Your store ID | testbox123456 |
| `store_passwd` | string | Your store password | testbox123@ssl |
| `total_amount` | decimal | Transaction amount | 1000.00 |
| `currency` | string | Currency code | BDT |
| `tran_id` | string | Unique transaction ID | TXN123456789 |
| `success_url` | url | Success callback URL | https://yourdomain.com/payment/success |
| `fail_url` | url | Failure callback URL | https://yourdomain.com/payment/fail |
| `cancel_url` | url | Cancel callback URL | https://yourdomain.com/payment/cancel |
| `ipn_url` | url | IPN callback URL | https://yourdomain.com/payment/ipn |

#### Customer Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `cus_name` | string | Customer name |
| `cus_email` | string | Customer email |
| `cus_add1` | string | Customer address line 1 |
| `cus_city` | string | Customer city |
| `cus_postcode` | string | Postal code |
| `cus_country` | string | Country name |
| `cus_phone` | string | Phone number |

#### Product Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `product_name` | string | Product/Service name |
| `product_category` | string | Product category |
| `product_profile` | string | Product profile type |

#### Product Profile Types

- `general` - General products/services
- `physical-goods` - Physical products
- `non-physical-goods` - Digital products
- `airline-tickets` - Airline tickets
- `travel-vertical` - Travel services

### Response

#### Success Response

```json
{
    "status": "SUCCESS",
    "failedreason": "",
    "sessionkey": "ABC123DEF456",
    "gw": {
        "visa": "https://...",
        "master": "https://...",
        "amex": "https://..."
    },
    "redirectGatewayURL": "https://sandbox.sslcommerz.com/...",
    "GatewayPageURL": "https://sandbox.sslcommerz.com/...",
    "storeBanner": "https://...",
    "storeLogo": "https://...",
    "desc": [
        {
            "name": "Product Name",
            "type": "general",
            "logo": "https://..."
        }
    ],
    "is_direct_pay_enable": "1"
}
```

#### Error Response

```json
{
    "status": "FAILED",
    "failedreason": "Store ID and Store Password are required"
}
```

### Implementation

```php
public function initiatePayment(array $paymentData): array
{
    $requestData = [
        'store_id' => $this->storeId,
        'store_passwd' => $this->storePassword,
        'total_amount' => $paymentData['amount'],
        'currency' => 'BDT',
        'tran_id' => 'TXN' . time() . rand(1000, 9999),
        // ... other parameters
    ];

    $response = Http::asForm()->post(
        $this->baseUrl . '/gwprocess/v4/api.php',
        $requestData
    );

    return $response->json();
}
```

---

## Transaction Validation API

### Purpose

Validates a transaction with SSL Commerz to ensure it's legitimate and prevent fraud.

### Endpoint

```
GET /validator/api/validationserverAPI.php
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `val_id` | string | Yes | Validation ID from callback |
| `store_id` | string | Yes | Your store ID |
| `store_passwd` | string | Yes | Your store password |
| `format` | string | No | Response format (json/xml) |

### Response

#### Valid Transaction

```json
{
    "status": "VALID",
    "tran_date": "2024-11-17 12:30:45",
    "tran_id": "TXN123456789",
    "val_id": "190817165500IqQisqLFoODDfJ",
    "amount": "1000.00",
    "store_amount": "975.00",
    "currency": "BDT",
    "bank_tran_id": "190817165500IqQisqLFoODDfJ",
    "card_type": "VISA-Dutch Bangla",
    "card_no": "432149XXXXXX1234",
    "card_issuer": "TRUST BANK, LTD.",
    "card_brand": "VISA",
    "card_issuer_country": "Bangladesh",
    "currency_type": "BDT",
    "currency_amount": "1000.00",
    "currency_rate": "1.0000",
    "base_fair": "0.00",
    "value_a": "",
    "value_b": "",
    "value_c": "",
    "value_d": "",
    "risk_level": "0",
    "risk_title": "Safe"
}
```

#### Invalid Transaction

```json
{
    "status": "INVALID_TRANSACTION",
    "error": "Transaction not found"
}
```

### Implementation

```php
public function validateTransaction(string $valId, string $transactionId, float $amount): array
{
    $validationData = [
        'val_id' => $valId,
        'store_id' => $this->storeId,
        'store_passwd' => $this->storePassword,
        'format' => 'json'
    ];

    $response = Http::asForm()->get(
        $this->baseUrl . '/validator/api/validationserverAPI.php',
        $validationData
    );

    $responseData = $response->json();

    // Verify amount and transaction ID match
    if ($responseData['status'] === 'VALID') {
        $isAmountMatched = floatval($responseData['amount']) == floatval($amount);
        $isTransactionMatched = $responseData['tran_id'] === $transactionId;

        if ($isAmountMatched && $isTransactionMatched) {
            return ['validated' => true, 'data' => $responseData];
        }
    }

    return ['validated' => false, 'data' => $responseData];
}
```

### Validation Checks

Always perform these checks when validating:

1. **Status Check**: Verify `status` is `VALID`
2. **Amount Check**: Ensure `amount` matches your original amount
3. **Transaction ID Check**: Verify `tran_id` matches your transaction
4. **Timestamp Check**: Check transaction is recent (within acceptable timeframe)

---

## Callback Handling

### Success Callback

SSL Commerz redirects the customer to your success URL with payment details.

#### POST Parameters

| Parameter | Description |
|-----------|-------------|
| `tran_id` | Transaction ID |
| `val_id` | Validation ID |
| `amount` | Transaction amount |
| `card_type` | Payment method used |
| `store_amount` | Amount after gateway charges |
| `card_no` | Masked card number |
| `bank_tran_id` | Bank transaction ID |
| `status` | Transaction status |
| `tran_date` | Transaction timestamp |
| `currency` | Currency code |
| `card_issuer` | Card issuing bank |
| `card_brand` | Card brand |
| `card_issuer_country` | Issuer country |
| `currency_amount` | Amount in currency |
| `verify_sign` | Verification signature |
| `verify_key` | Verification key |
| `risk_level` | Risk assessment (0-2) |
| `risk_title` | Risk description |

#### Implementation

```php
public function handleSuccess(Request $request)
{
    $callbackData = $request->all();
    
    // Find transaction
    $transaction = Transaction::where('transaction_id', $callbackData['tran_id'])->first();
    
    // Validate with SSL Commerz
    $validation = $this->sslCommerzService->validateTransaction(
        $callbackData['val_id'],
        $callbackData['tran_id'],
        $transaction->amount
    );
    
    if ($validation['validated']) {
        // Update transaction as successful
        $transaction->update([
            'status' => 'success',
            'gateway_transaction_id' => $callbackData['tran_id'],
            'bank_transaction_id' => $callbackData['bank_tran_id'],
            'card_type' => $callbackData['card_type'],
            'paid_at' => now(),
        ]);
    }
    
    return view('payment.success', compact('transaction'));
}
```

### Fail Callback

When payment fails, SSL Commerz redirects to fail URL.

#### POST Parameters

Similar to success callback, but with `status` indicating failure.

### Cancel Callback

When customer cancels, SSL Commerz redirects to cancel URL.

---

## IPN (Instant Payment Notification)

### What is IPN?

IPN is a server-to-server notification from SSL Commerz to your server. It's more reliable than browser redirects.

### Advantages

- Works even if customer closes browser
- Not affected by network issues
- Guaranteed delivery
- Server-to-server communication

### Implementation

```php
public function handleIPN(Request $request)
{
    $ipnData = $request->all();
    
    // Log IPN data
    Log::info('SSL Commerz IPN', $ipnData);
    
    // Find transaction
    $transaction = Transaction::where('transaction_id', $ipnData['tran_id'])->first();
    
    if ($transaction) {
        // Validate transaction
        $validation = $this->sslCommerzService->validateTransaction(
            $ipnData['val_id'],
            $ipnData['tran_id'],
            $transaction->amount
        );
        
        if ($validation['validated']) {
            $transaction->update([
                'status' => 'success',
                'ipn_response' => json_encode($ipnData),
                'paid_at' => now(),
            ]);
        }
    }
    
    // Return success response to SSL Commerz
    return response()->json(['success' => true], 200);
}
```

### IPN URL Requirements

1. Must be publicly accessible
2. Should respond with HTTP 200
3. Should process quickly (< 30 seconds)
4. Should handle duplicate notifications

---

## Error Handling

### Common Error Codes

| Error | Description | Solution |
|-------|-------------|----------|
| `FAILED` | General failure | Check request parameters |
| `INVALID_TRANSACTION` | Transaction not found | Verify transaction ID |
| `AMOUNT_MISMATCH` | Amount doesn't match | Check amount calculation |
| `EXPIRED` | Session expired | Initiate new session |
| `CANCELLED` | User cancelled | Normal flow |

### Error Response Structure

```json
{
    "status": "FAILED",
    "failedreason": "Description of error",
    "error": "Technical error message"
}
```

### Implementation

```php
try {
    $response = $this->sslCommerzService->initiatePayment($paymentData);
    
    if ($response['success']) {
        return redirect()->away($response['gateway_url']);
    } else {
        return redirect()->back()->with('error', $response['message']);
    }
} catch (\Exception $e) {
    Log::error('Payment Error', ['error' => $e->getMessage()]);
    return redirect()->back()->with('error', 'Payment initiation failed');
}
```

---

## Code Examples

### Complete Payment Flow

```php
// 1. Initiate Payment
$paymentData = [
    'customer_name' => 'John Doe',
    'customer_email' => 'john@example.com',
    'customer_phone' => '01700000000',
    'product_name' => 'Premium Package',
    'amount' => 1000.00,
    'currency' => 'BDT',
];

$response = $sslCommerzService->initiatePayment($paymentData);

// 2. Redirect to Gateway
if ($response['success']) {
    return redirect()->away($response['gateway_url']);
}

// 3. Handle Success Callback
public function handleSuccess(Request $request)
{
    $validation = $sslCommerzService->validateTransaction(
        $request->val_id,
        $request->tran_id,
        $transaction->amount
    );
    
    if ($validation['validated']) {
        // Payment successful
        $transaction->markAsSuccess();
        return view('payment.success');
    }
}

// 4. Handle IPN
public function handleIPN(Request $request)
{
    $transaction = Transaction::where('transaction_id', $request->tran_id)->first();
    $sslCommerzService->handleIPN($request->all());
    return response()->json(['success' => true]);
}
```

### Testing in Sandbox

```php
// Set sandbox mode
config(['sslcommerz.sandbox' => true]);

// Use test credentials
config([
    'sslcommerz.store_id' => 'testbox123456',
    'sslcommerz.store_password' => 'testbox123@ssl'
]);

// Use test card: 4111 1111 1111 1111
```

---

## Security Best Practices

1. **Always Validate Transactions**
   - Never trust callback data alone
   - Always call validation API
   - Verify amount and transaction ID

2. **Use HTTPS**
   - SSL certificate required in production
   - Secure all callback URLs

3. **Validate Request Origin**
   - Check IPN comes from SSL Commerz IPs
   - Implement signature verification

4. **Rate Limiting**
   - Limit payment initiation attempts
   - Prevent abuse

5. **Logging**
   - Log all API requests/responses
   - Monitor for suspicious activity

6. **Error Handling**
   - Don't expose sensitive errors
   - Log errors server-side

---

## Additional Resources

- [SSL Commerz Official Documentation](https://developer.sslcommerz.com/)
- [API Changelog](https://developer.sslcommerz.com/documentation/changelog/)
- [Support](mailto:support@sslcommerz.com)

---

**Last Updated**: November 2024
