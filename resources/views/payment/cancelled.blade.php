{{--
    Payment Cancelled View
    
    This view is shown when a customer cancels the payment.
    It provides options to retry or return to the payment form.
--}}

@extends('layouts.app')

@section('title', 'Payment Cancelled')

@section('content')
<div class="payment-card text-center">
    {{-- Cancelled Icon --}}
    <div class="mb-4">
        <i class="fas fa-ban text-warning" style="font-size: 80px;"></i>
    </div>
    
    {{-- Cancelled Message --}}
    <h2 class="text-warning mb-3">Payment Cancelled</h2>
    <p class="lead">{{ $message ?? 'You have cancelled the payment process.' }}</p>
    
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
                            <span class="status-badge status-cancelled">
                                <i class="fas fa-ban"></i> {{ ucfirst($transaction->status) }}
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
    
    {{-- Information Box --}}
    <div class="mt-4 alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>No charges were made.</strong> You can return to the payment form to try again or choose a different payment method.
    </div>
    
    {{-- Action Buttons --}}
    <div class="mt-4 d-flex gap-2 justify-content-center">
        <a href="{{ route('payment.form') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Return to Payment Form
        </a>
        @isset($transaction)
        <a href="{{ route('payment.status', $transaction->transaction_id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-eye"></i> View Details
        </a>
        @endisset
    </div>
</div>
@endsection
