<?php

/**
 * SSL Commerz Payment Gateway Configuration
 * 
 * This configuration file contains all settings required for SSL Commerz integration.
 * 
 * SANDBOX vs LIVE Mode:
 * - Set 'sandbox' to true for testing with sandbox credentials
 * - Set 'sandbox' to false for production with live credentials
 * 
 * Getting Credentials:
 * 1. Register at https://developer.sslcommerz.com/registration/
 * 2. Get sandbox credentials from your dashboard
 * 3. For live mode, contact SSL Commerz for merchant account
 * 
 * Security Notes:
 * - Never commit real credentials to version control
 * - Always use .env file for storing sensitive data
 * - Rotate credentials periodically
 * - Use different credentials for sandbox and live
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Store ID
    |--------------------------------------------------------------------------
    |
    | Your SSL Commerz Store ID. This identifies your merchant account.
    | For sandbox testing, you'll receive this from SSL Commerz after registration.
    |
    | Sandbox Example: testbox123456
    | Live Example: yourstore123456
    |
    */
    'store_id' => env('SSLCOMMERZ_STORE_ID', 'your_store_id_here'),

    /*
    |--------------------------------------------------------------------------
    | Store Password
    |--------------------------------------------------------------------------
    |
    | Your SSL Commerz Store Password. This is used for authentication.
    | Keep this secret and never share it publicly.
    |
    | Sandbox Example: testbox123@ssl
    | Live Example: your_secure_password
    |
    */
    'store_password' => env('SSLCOMMERZ_STORE_PASSWORD', 'your_store_password_here'),

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    |
    | When true, the integration will use SSL Commerz sandbox environment.
    | When false, it will use the live production environment.
    |
    | Always test thoroughly in sandbox mode before going live!
    |
    | Sandbox URL: https://sandbox.sslcommerz.com
    | Live URL: https://securepay.sslcommerz.com
    |
    */
    'sandbox' => env('SSLCOMMERZ_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | Success URL
    |--------------------------------------------------------------------------
    |
    | URL where customer will be redirected after successful payment.
    | SSL Commerz will POST transaction data to this URL.
    |
    | This is automatically set via routes, but you can override if needed.
    |
    */
    'success_url' => env('SSLCOMMERZ_SUCCESS_URL'),

    /*
    |--------------------------------------------------------------------------
    | Fail URL
    |--------------------------------------------------------------------------
    |
    | URL where customer will be redirected after failed payment.
    | SSL Commerz will POST transaction data to this URL.
    |
    */
    'fail_url' => env('SSLCOMMERZ_FAIL_URL'),

    /*
    |--------------------------------------------------------------------------
    | Cancel URL
    |--------------------------------------------------------------------------
    |
    | URL where customer will be redirected if they cancel the payment.
    | SSL Commerz will POST transaction data to this URL.
    |
    */
    'cancel_url' => env('SSLCOMMERZ_CANCEL_URL'),

    /*
    |--------------------------------------------------------------------------
    | IPN URL (Instant Payment Notification)
    |--------------------------------------------------------------------------
    |
    | Server-to-server notification URL for payment updates.
    | SSL Commerz will send POST request to this URL after payment processing.
    |
    | IPN is more reliable than browser redirects as it works even if:
    | - Customer closes browser
    | - Network connection is lost
    | - Browser redirect fails
    |
    | This URL must be publicly accessible for SSL Commerz to reach it.
    |
    */
    'ipn_url' => env('SSLCOMMERZ_IPN_URL'),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Default currency for transactions.
    | Supported: BDT, USD, EUR, GBP, MYR, SGD
    |
    | BDT is most commonly used for Bangladesh.
    |
    */
    'currency' => env('SSLCOMMERZ_CURRENCY', 'BDT'),

    /*
    |--------------------------------------------------------------------------
    | EMI Options
    |--------------------------------------------------------------------------
    |
    | Enable EMI (Equated Monthly Installment) payment options.
    | Set to true if you want to allow customers to pay in installments.
    |
    */
    'emi_option' => env('SSLCOMMERZ_EMI_OPTION', false),

    /*
    |--------------------------------------------------------------------------
    | Product Profile
    |--------------------------------------------------------------------------
    |
    | Default product profile type for your products.
    |
    | Options:
    | - general: General products/services
    | - physical-goods: Physical products that need shipping
    | - non-physical-goods: Digital products/services
    | - airline-tickets: Airline ticket booking
    | - travel-vertical: Travel related services
    |
    */
    'product_profile' => env('SSLCOMMERZ_PRODUCT_PROFILE', 'general'),

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | Available payment methods that customers can use.
    | You can enable/disable specific payment methods based on your business needs.
    |
    | Common payment methods in Bangladesh:
    | - Visa/Master Cards
    | - Mobile Banking: bKash, Nagad, Rocket
    | - Internet Banking
    |
    */
    'allowed_payment_methods' => [
        'bankcard' => true,      // Credit/Debit cards
        'mobile_banking' => true, // bKash, Nagad, Rocket, etc.
        'internet_banking' => true, // Online banking
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    |
    | Enable automatic transaction validation after payment.
    | When true, the system will validate each transaction with SSL Commerz
    | to ensure authenticity and prevent fraud.
    |
    | IMPORTANT: Keep this true for security!
    |
    */
    'validate_transactions' => true,

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable detailed logging of SSL Commerz API requests and responses.
    | Useful for debugging and monitoring transactions.
    |
    | Logs are stored in: storage/logs/laravel.log
    |
    */
    'logging' => [
        'enabled' => env('SSLCOMMERZ_LOGGING', true),
        'level' => env('SSLCOMMERZ_LOG_LEVEL', 'info'), // debug, info, warning, error
    ],
];
