{{--
    Payment Success View
    
    This view is shown when a payment is completed successfully.
    It displays transaction details and confirmation message.
--}}

@extends('layouts.app')

@section('title', 'Payment Successful')

@section('content')
<div class="payment-card text-center">
    {{-- Success Icon --}}
    <div class="mb-4">
        <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
    </div>
    
    {{-- Success Message --}}
    <h2 class="text-success mb-3">Payment Successful!</h2>
    <p class="lead">{{ $message ?? 'Your payment has been processed successfully.' }}</p>
    
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
            
            @if($transaction->gateway_transaction_id)
            <div class="transaction-detail">
                <div class="row">
                    <div class="col-md-6">
                        <label>Gateway Transaction ID:</label>
                    </div>
                    <div class="col-md-6">
                        <span class="float-end">{{ $transaction->gateway_transaction_id }}</span>
                    </div>
                </div>
            </div>
            @endif
            
            @if($transaction->bank_transaction_id)
            <div class="transaction-detail">
                <div class="row">
                    <div class="col-md-6">
                        <label>Bank Transaction ID:</label>
                    </div>
                    <div class="col-md-6">
                        <span class="float-end">{{ $transaction->bank_transaction_id }}</span>
                    </div>
                </div>
            </div>
            @endif
            
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
                        <label>Amount Paid:</label>
                    </div>
                    <div class="col-md-6">
                        <span class="float-end"><strong class="text-success">৳{{ number_format($transaction->paid_amount ?? $transaction->amount, 2) }}</strong></span>
                    </div>
                </div>
            </div>
            
            @if($transaction->payment_method)
            <div class="transaction-detail">
                <div class="row">
                    <div class="col-md-6">
                        <label>Payment Method:</label>
                    </div>
                    <div class="col-md-6">
                        <span class="float-end">{{ $transaction->payment_method }}</span>
                    </div>
                </div>
            </div>
            @endif
            
            @if($transaction->card_type)
            <div class="transaction-detail">
                <div class="row">
                    <div class="col-md-6">
                        <label>Card Type:</label>
                    </div>
                    <div class="col-md-6">
                        <span class="float-end">{{ $transaction->card_type }}</span>
                    </div>
                </div>
            </div>
            @endif
            
            @if($transaction->card_issuer)
            <div class="transaction-detail">
                <div class="row">
                    <div class="col-md-6">
                        <label>Card Issuer:</label>
                    </div>
                    <div class="col-md-6">
                        <span class="float-end">{{ $transaction->card_issuer }}</span>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="transaction-detail">
                <div class="row">
                    <div class="col-md-6">
                        <label>Customer Name:</label>
                    </div>
                    <div class="col-md-6">
                        <span class="float-end">{{ $transaction->customer_name }}</span>
                    </div>
                </div>
            </div>
            
            <div class="transaction-detail">
                <div class="row">
                    <div class="col-md-6">
                        <label>Customer Email:</label>
                    </div>
                    <div class="col-md-6">
                        <span class="float-end">{{ $transaction->customer_email }}</span>
                    </div>
                </div>
            </div>
            
            <div class="transaction-detail">
                <div class="row">
                    <div class="col-md-6">
                        <label>Date & Time:</label>
                    </div>
                    <div class="col-md-6">
                        <span class="float-end">{{ $transaction->paid_at ? $transaction->paid_at->format('d M Y, h:i A') : $transaction->created_at->format('d M Y, h:i A') }}</span>
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
                            <span class="status-badge status-success">
                                <i class="fas fa-check"></i> {{ ucfirst($transaction->status) }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Action Buttons --}}
        <div class="mt-4 d-flex gap-2 justify-content-center">
            <a href="{{ route('payment.form') }}" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i> Make Another Payment
            </a>
            <a href="{{ route('payment.status', $transaction->transaction_id) }}" class="btn btn-outline-primary">
                <i class="fas fa-eye"></i> View Details
            </a>
        </div>
        
        {{-- Receipt Note --}}
        <div class="mt-4 alert alert-info">
            <i class="fas fa-envelope"></i> A confirmation email will be sent to <strong>{{ $transaction->customer_email }}</strong>
        </div>
    @endisset
</div>
@endsection
