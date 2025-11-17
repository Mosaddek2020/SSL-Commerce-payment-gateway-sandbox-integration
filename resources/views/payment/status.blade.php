{{--
    Transaction Status View
    
    This view displays detailed status information for a specific transaction.
    Customers can use this to check their payment status at any time.
--}}

@extends('layouts.app')

@section('title', 'Transaction Status')

@section('content')
<div class="payment-card">
    @isset($error)
        {{-- Error Message --}}
        <div class="text-center">
            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 60px;"></i>
            <h3 class="mt-3 text-danger">{{ $error }}</h3>
            <p class="mt-3">
                <a href="{{ route('payment.form') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Payment Form
                </a>
            </p>
        </div>
    @else
        {{-- Transaction Found --}}
        <h3 class="mb-4"><i class="fas fa-search"></i> Transaction Status</h3>
        
        {{-- Status Badge --}}
        <div class="text-center mb-4">
            @if($transaction->isSuccess())
                <i class="fas fa-check-circle text-success" style="font-size: 60px;"></i>
                <h4 class="mt-3 text-success">Payment Successful</h4>
            @elseif($transaction->isPending())
                <i class="fas fa-clock text-warning" style="font-size: 60px;"></i>
                <h4 class="mt-3 text-warning">Payment Pending</h4>
            @elseif($transaction->isProcessing())
                <i class="fas fa-spinner fa-spin text-info" style="font-size: 60px;"></i>
                <h4 class="mt-3 text-info">Payment Processing</h4>
            @elseif($transaction->isFailed())
                <i class="fas fa-times-circle text-danger" style="font-size: 60px;"></i>
                <h4 class="mt-3 text-danger">Payment Failed</h4>
            @elseif($transaction->isCancelled())
                <i class="fas fa-ban text-warning" style="font-size: 60px;"></i>
                <h4 class="mt-3 text-warning">Payment Cancelled</h4>
            @endif
        </div>
        
        {{-- Transaction Details --}}
        <h5 class="mb-3"><i class="fas fa-receipt"></i> Transaction Information</h5>
        
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Transaction ID:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end"><strong>{{ $transaction->transaction_id }}</strong></span>
                </div>
            </div>
        </div>
        
        @if($transaction->order_id)
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Order ID:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->order_id }}</span>
                </div>
            </div>
        </div>
        @endif
        
        @if($transaction->gateway_transaction_id)
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Gateway Transaction ID:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->gateway_transaction_id }}</span>
                </div>
            </div>
        </div>
        @endif
        
        @if($transaction->bank_transaction_id)
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Bank Transaction ID:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->bank_transaction_id }}</span>
                </div>
            </div>
        </div>
        @endif
        
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Status:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">
                        <span class="status-badge status-{{ $transaction->status }}">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </span>
                </div>
            </div>
        </div>
        
        <hr>
        
        <h5 class="mb-3"><i class="fas fa-box"></i> Product Details</h5>
        
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Product Name:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->product_name }}</span>
                </div>
            </div>
        </div>
        
        @if($transaction->product_description)
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Description:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->product_description }}</span>
                </div>
            </div>
        </div>
        @endif
        
        @if($transaction->product_category)
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Category:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->product_category }}</span>
                </div>
            </div>
        </div>
        @endif
        
        <hr>
        
        <h5 class="mb-3"><i class="fas fa-money-bill-wave"></i> Payment Details</h5>
        
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Amount:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end"><strong class="text-primary">৳{{ number_format($transaction->amount, 2) }}</strong></span>
                </div>
            </div>
        </div>
        
        @if($transaction->paid_amount)
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Paid Amount:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end"><strong class="text-success">৳{{ number_format($transaction->paid_amount, 2) }}</strong></span>
                </div>
            </div>
        </div>
        @endif
        
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Currency:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->currency }}</span>
                </div>
            </div>
        </div>
        
        @if($transaction->payment_method)
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Payment Method:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->payment_method }}</span>
                </div>
            </div>
        </div>
        @endif
        
        @if($transaction->card_type)
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Card Type:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->card_type }}</span>
                </div>
            </div>
        </div>
        @endif
        
        @if($transaction->card_issuer)
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Card Issuer:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->card_issuer }}</span>
                </div>
            </div>
        </div>
        @endif
        
        <hr>
        
        <h5 class="mb-3"><i class="fas fa-user"></i> Customer Information</h5>
        
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Name:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->customer_name }}</span>
                </div>
            </div>
        </div>
        
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Email:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->customer_email }}</span>
                </div>
            </div>
        </div>
        
        @if($transaction->customer_phone)
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Phone:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->customer_phone }}</span>
                </div>
            </div>
        </div>
        @endif
        
        @if($transaction->customer_address)
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Address:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->customer_address }}</span>
                </div>
            </div>
        </div>
        @endif
        
        @if($transaction->customer_city)
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>City:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->customer_city }}</span>
                </div>
            </div>
        </div>
        @endif
        
        <hr>
        
        <h5 class="mb-3"><i class="fas fa-calendar"></i> Timeline</h5>
        
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Created At:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>
        
        @if($transaction->paid_at)
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Paid At:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->paid_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>
        @endif
        
        <div class="transaction-detail">
            <div class="row">
                <div class="col-md-5">
                    <label>Last Updated:</label>
                </div>
                <div class="col-md-7">
                    <span class="float-end">{{ $transaction->updated_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>
        
        {{-- Action Buttons --}}
        <div class="mt-4 d-flex gap-2 justify-content-center">
            <a href="{{ route('payment.form') }}" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i> Make Another Payment
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    @endisset
</div>
@endsection
