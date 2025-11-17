<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Transaction Model
 * 
 * This model represents a payment transaction processed through SSL Commerz payment gateway.
 * It stores all relevant information about a payment including customer details, transaction status,
 * payment method, and gateway responses.
 * 
 * Status Flow:
 * 1. pending    -> Initial status when transaction is created
 * 2. processing -> Payment is being processed by SSL Commerz
 * 3. success    -> Payment completed successfully
 * 4. failed     -> Payment failed
 * 5. cancelled  -> Payment cancelled by user
 * 6. refunded   -> Payment refunded to customer
 * 
 * @property int $id Primary key
 * @property string $transaction_id Unique transaction identifier
 * @property string $order_id SSL Commerz order ID
 * @property string $customer_name Customer full name
 * @property string $customer_email Customer email
 * @property string $customer_phone Customer phone number
 * @property string $customer_address Customer address
 * @property string $product_name Product name
 * @property float $amount Transaction amount
 * @property string $status Transaction status
 * @property string $gateway_transaction_id SSL Commerz transaction ID
 * @property \Carbon\Carbon $paid_at Payment completion timestamp
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon $updated_at Record last update timestamp
 * @property \Carbon\Carbon $deleted_at Soft delete timestamp
 */
class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     * 
     * These fields can be filled using mass assignment methods like create() or fill().
     * This helps protect against mass assignment vulnerabilities.
     *
     * @var array<string>
     */
    protected $fillable = [
        // Transaction identifiers
        'transaction_id',
        'order_id',
        
        // Customer information
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'customer_city',
        'customer_postcode',
        'customer_country',
        
        // Product details
        'product_name',
        'product_description',
        'product_category',
        
        // Payment amounts
        'amount',
        'paid_amount',
        'currency',
        
        // Gateway response data
        'gateway_transaction_id',
        'bank_transaction_id',
        'card_type',
        'card_no',
        'card_issuer',
        'card_brand',
        'card_issuer_country',
        'payment_method',
        
        // Status tracking
        'status',
        'validation_status',
        'risk_level',
        'risk_title',
        
        // SSL Commerz session data
        'sessionkey',
        'gateway_page_url',
        
        // Custom values
        'value_a',
        'value_b',
        'value_c',
        'value_d',
        
        // API responses
        'api_response',
        'ipn_response',
        
        // Timestamps
        'paid_at',
    ];

    /**
     * The attributes that should be cast to native types.
     * 
     * This ensures that database values are automatically converted to appropriate PHP types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Transaction status constants
     * 
     * Use these constants instead of hardcoded strings for consistency.
     * Example: $transaction->status = Transaction::STATUS_SUCCESS;
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Check if transaction is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if transaction is processing
     *
     * @return bool
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if transaction is successful
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Check if transaction failed
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if transaction was cancelled
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if transaction was refunded
     *
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Mark transaction as successful
     * 
     * Updates the status to success and records the payment timestamp.
     *
     * @return bool
     */
    public function markAsSuccess(): bool
    {
        $this->status = self::STATUS_SUCCESS;
        $this->paid_at = now();
        return $this->save();
    }

    /**
     * Mark transaction as failed
     *
     * @return bool
     */
    public function markAsFailed(): bool
    {
        $this->status = self::STATUS_FAILED;
        return $this->save();
    }

    /**
     * Mark transaction as cancelled
     *
     * @return bool
     */
    public function markAsCancelled(): bool
    {
        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }

    /**
     * Scope query to only successful transactions
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Scope query to only pending transactions
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope query to only failed transactions
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Get transactions by customer email
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $email
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCustomerEmail($query, $email)
    {
        return $query->where('customer_email', $email);
    }
}
