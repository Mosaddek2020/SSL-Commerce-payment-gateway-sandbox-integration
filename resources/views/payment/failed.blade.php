{{--
    Payment Failed View
    
    This view is shown when a payment fails.
    It displays error message and transaction details if available.
--}}

@extends('layouts.app')

@section('title', 'Payment Failed')

@section('content')
<div class="payment-card text-center">
    {{-- Failed Icon --}}
    <div class="mb-4">
        <i class="fas fa-times-circle text-danger" style="font-size: 80px;"></i>
    </div>
    
    {{-- Failed Message --}}
    <h2 class="text-danger mb-3">Payment Failed</h2>
    <p class="lead">{{ $message ?? 'Unfortunately, your payment could not be processed.' }}</p>
    
    @isset($transaction)
        {{-- Transaction Details --}}
        <div class="mt-4 text-start">
            <h5 class="mb-3"><i class="fas fa-receipt"></i> Transaction Details</h5>
            
            <div class="transaction-detail">
                <div class="row">
                    <div class="col-md-6">
                        <label>Transaction ID:</label>
                    </div>
                    <div class="col-md-6">
                        <span class="float-end"><strong>{{ $transaction->transaction_id }}</strong></span>
                    </div>
                </div>
            </div>
            
            <div class="transaction-detail">
                <div class="row">
                    <div class="col-md-6">
                        <label>Product:</label>
                    </div>
                    <div class="col-md-6">
                        <span class="float-end">{{ $transaction->product_name }}</span>
                    </div>
                </div>
            </div>
            
            <div class="transaction-detail">
                <div class="row">
                    <div class="col-md-6">
                        <label>Amount:</label>
                    </div>
                    <div class="col-md-6">
                        <span class="float-end"><strong>৳{{ number_format($transaction->amount, 2) }}</strong></span>
                    </div>
                </div>
            </div>
            
            <div class="transaction-detail">
                <div class="row">
                    <div class="col-md-6">
                        <label>Status:</label>
                    </div>
                    <div class="col-md-6">
                        <span class="float-end">
                            <span class="status-badge status-failed">
                                <i class="fas fa-times"></i> {{ ucfirst($transaction->status) }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="transaction-detail">
                <div class="row">
                    <div class="col-md-6">
                        <label>Date & Time:</label>
                    </div>
                    <div class="col-md-6">
                        <span class="float-end">{{ $transaction->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endisset
    
    {{-- Reason for Failure --}}
    <div class="mt-4 alert alert-warning">
        <h6><i class="fas fa-info-circle"></i> Common Reasons for Payment Failure:</h6>
        <ul class="text-start mb-0">
            <li>Insufficient balance in your account</li>
            <li>Incorrect card details or expired card</li>
            <li>Transaction declined by your bank</li>
            <li>Network connectivity issues</li>
            <li>Daily transaction limit exceeded</li>
        </ul>
    </div>
    
    {{-- Action Buttons --}}
    <div class="mt-4 d-flex gap-2 justify-content-center">
        <a href="{{ route('payment.form') }}" class="btn btn-primary">
            <i class="fas fa-redo"></i> Try Again
        </a>
        @isset($transaction)
        <a href="{{ route('payment.status', $transaction->transaction_id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-eye"></i> View Details
        </a>
        @endisset
    </div>
    
    {{-- Support Information --}}
    <div class="mt-4">
        <p class="text-muted">
            <i class="fas fa-headset"></i> 
            Need help? Contact our support team or try a different payment method.
        </p>
    </div>
</div>
@endsection
