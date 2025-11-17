# Quick Reference - SSL Commerz Integration

## File Structure

```
├── app/
│   ├── Http/Controllers/
│   │   └── PaymentController.php        # Main payment controller
│   ├── Models/
│   │   └── Transaction.php              # Transaction model
│   └── Services/
│       └── SSLCommerzService.php        # SSL Commerz API service
├── config/
│   └── sslcommerz.php                   # SSL Commerz configuration
├── database/migrations/
│   └── *_create_transactions_table.php  # Database schema
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php               # Base layout
│   └── payment/
│       ├── form.blade.php              # Payment form
│       ├── success.blade.php           # Success page
│       ├── failed.blade.php            # Failed page
│       ├── cancelled.blade.php         # Cancelled page
│       ├── status.blade.php            # Status page
│       └── list.blade.php              # Transactions list
└── routes/
    └── web.php                         # Web routes
```

## Quick Commands

### Setup
```bash
# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate

# Start development server
php artisan serve
```

### Development
```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# List routes
php artisan route:list

# Check logs
tail -f storage/logs/laravel.log
```

## Environment Variables

```env
# Database
DB_DATABASE=sslcommerz_db
DB_USERNAME=root
DB_PASSWORD=your_password

# SSL Commerz Sandbox
SSLCOMMERZ_STORE_ID=testbox123456
SSLCOMMERZ_STORE_PASSWORD=testbox123@ssl
SSLCOMMERZ_SANDBOX=true
```

## Test Cards

| Card | Number | CVV | Expiry |
|------|--------|-----|--------|
| Visa | 4111 1111 1111 1111 | 123 | 12/25 |
| MasterCard | 5500 0000 0000 0004 | 123 | 12/25 |
| Amex | 3400 0000 0000 009 | 1234 | 12/25 |

## Routes

```
GET  /                          # Home (redirects to payment form)
GET  /payment                   # Payment form
POST /payment/initiate          # Initiate payment
POST /payment/success           # Success callback
POST /payment/fail              # Fail callback
POST /payment/cancel            # Cancel callback
POST /payment/ipn               # IPN notification
GET  /payment/status/{id}       # Transaction status
GET  /payment/transactions      # List all transactions
```

## Transaction Model Methods

```php
// Status checks
$transaction->isSuccess()
$transaction->isPending()
$transaction->isProcessing()
$transaction->isFailed()
$transaction->isCancelled()

// Status updates
$transaction->markAsSuccess()
$transaction->markAsFailed()
$transaction->markAsCancelled()

// Scopes
Transaction::successful()->get()
Transaction::pending()->get()
Transaction::failed()->get()
Transaction::byCustomerEmail('email@example.com')->get()
```

## SSL Commerz Service Methods

```php
// Initiate payment
$response = $sslCommerzService->initiatePayment($paymentData);

// Validate transaction
$validation = $sslCommerzService->validateTransaction($valId, $transactionId, $amount);

// Handle callbacks
$transaction = $sslCommerzService->handleSuccessCallback($callbackData);
$transaction = $sslCommerzService->handleFailCallback($callbackData);
$transaction = $sslCommerzService->handleCancelCallback($callbackData);
$transaction = $sslCommerzService->handleIPN($ipnData);

// Get transaction status
$transaction = $sslCommerzService->getTransactionStatus($transactionId);
```

## Database Schema

### transactions table

```sql
id                          BIGINT AUTO_INCREMENT PRIMARY KEY
transaction_id              VARCHAR(255) UNIQUE
order_id                    VARCHAR(255)
customer_name               VARCHAR(255)
customer_email              VARCHAR(255)
customer_phone              VARCHAR(20)
customer_address            TEXT
customer_city               VARCHAR(255)
customer_postcode           VARCHAR(20)
customer_country            VARCHAR(255)
product_name                VARCHAR(255)
product_description         TEXT
product_category            VARCHAR(255)
amount                      DECIMAL(10,2)
paid_amount                 DECIMAL(10,2)
currency                    VARCHAR(3)
gateway_transaction_id      VARCHAR(255)
bank_transaction_id         VARCHAR(255)
card_type                   VARCHAR(255)
card_no                     VARCHAR(255)
card_issuer                 VARCHAR(255)
card_brand                  VARCHAR(255)
card_issuer_country         VARCHAR(255)
payment_method              VARCHAR(255)
status                      ENUM(pending, processing, success, failed, cancelled, refunded)
validation_status           VARCHAR(255)
risk_level                  VARCHAR(255)
risk_title                  VARCHAR(255)
sessionkey                  VARCHAR(255)
gateway_page_url            VARCHAR(255)
value_a                     VARCHAR(255)
value_b                     VARCHAR(255)
value_c                     VARCHAR(255)
value_d                     VARCHAR(255)
api_response                TEXT
ipn_response                TEXT
paid_at                     TIMESTAMP
created_at                  TIMESTAMP
updated_at                  TIMESTAMP
deleted_at                  TIMESTAMP

Indexes: transaction_id, order_id, gateway_transaction_id, status, customer_email
```

## Common Issues

### 1. Store credentials error
```
Check .env file:
SSLCOMMERZ_STORE_ID=testbox123456
SSLCOMMERZ_STORE_PASSWORD=testbox123@ssl
```

### 2. Database connection error
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE sslcommerz_db;"

# Run migrations
php artisan migrate
```

### 3. Routes not working
```bash
# Clear route cache
php artisan route:clear
php artisan cache:clear
```

### 4. IPN not receiving
```bash
# Use ngrok for local testing
ngrok http 8000
# Update APP_URL in .env with ngrok URL
```

## Testing Workflow

1. **Start server**: `php artisan serve`
2. **Open browser**: `http://localhost:8000`
3. **Fill payment form**:
   - Name: John Doe
   - Email: john@example.com
   - Phone: 01700000000
   - Select product or enter amount
4. **Submit form**
5. **On SSL Commerz page**:
   - Use test card: 4111 1111 1111 1111
   - CVV: 123
   - Expiry: 12/25
6. **Verify success page**
7. **Check transaction list**: `http://localhost:8000/payment/transactions`

## Production Checklist

- [ ] Set `SSLCOMMERZ_SANDBOX=false`
- [ ] Use live SSL Commerz credentials
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure HTTPS
- [ ] Update callback URLs
- [ ] Set up database backups
- [ ] Configure monitoring
- [ ] Add authentication for admin routes
- [ ] Test with small amounts first

## Support Resources

- **SSL Commerz Docs**: https://developer.sslcommerz.com/
- **Laravel Docs**: https://laravel.com/docs
- **Project README**: README.md
- **Setup Guide**: SETUP_GUIDE.md
- **API Docs**: API_DOCUMENTATION.md

## Key Concepts

### Payment Flow
```
Customer → Form → Initiate → SSL Commerz Gateway → Payment
    ↓
Callback → Validate → Update DB → Show Result
    ↓
IPN → Validate → Update DB (backup notification)
```

### Transaction Statuses
```
pending → processing → success
                    → failed
                    → cancelled
```

### Validation Process
```
1. Receive callback with val_id
2. Call SSL Commerz validation API
3. Check status = "VALID"
4. Verify amount matches
5. Verify transaction ID matches
6. Update transaction status
```

## Configuration Files

### config/sslcommerz.php
```php
'store_id' => env('SSLCOMMERZ_STORE_ID')
'store_password' => env('SSLCOMMERZ_STORE_PASSWORD')
'sandbox' => env('SSLCOMMERZ_SANDBOX', true)
'currency' => env('SSLCOMMERZ_CURRENCY', 'BDT')
```

### .env
```env
SSLCOMMERZ_STORE_ID=testbox123456
SSLCOMMERZ_STORE_PASSWORD=testbox123@ssl
SSLCOMMERZ_SANDBOX=true
SSLCOMMERZ_CURRENCY=BDT
```

## Customization Tips

### Add email notifications
```php
// In PaymentController@handleSuccess
Mail::to($transaction->customer_email)->send(new PaymentConfirmation($transaction));
```

### Add authentication
```php
// In routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/payment/transactions', [PaymentController::class, 'listTransactions']);
});
```

### Custom product model
```php
// Create Product model
php artisan make:model Product -m

// Update payment form to load from database
$products = Product::active()->get();
```

## Debugging

### Enable debug logging
```env
APP_DEBUG=true
LOG_LEVEL=debug
SSLCOMMERZ_LOGGING=true
```

### View logs
```bash
tail -f storage/logs/laravel.log
```

### Check database
```bash
mysql -u root -p sslcommerz_db
SELECT * FROM transactions ORDER BY created_at DESC LIMIT 10;
```

### Test API endpoints
```bash
# Check if routes are accessible
curl http://localhost:8000/payment
```

---

**Quick Start**: Follow SETUP_GUIDE.md for detailed instructions!
