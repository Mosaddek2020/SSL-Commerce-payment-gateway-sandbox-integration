{{--
    Transaction List View
    
    This view displays all transactions in the system.
    Useful for admin/testing purposes to monitor all payments.
    
    IMPORTANT: In production, protect this route with authentication!
--}}

@extends('layouts.app')

@section('title', 'All Transactions')

@section('content')
<div class="payment-card">
    <h3 class="mb-4"><i class="fas fa-list"></i> All Transactions</h3>
    
    @if($transactions->isEmpty())
        {{-- No Transactions --}}
        <div class="text-center py-5">
            <i class="fas fa-inbox" style="font-size: 60px; color: #ccc;"></i>
            <h5 class="mt-3 text-muted">No transactions found</h5>
            <p class="text-muted">Start by making your first payment</p>
            <a href="{{ route('payment.form') }}" class="btn btn-primary mt-3">
                <i class="fas fa-plus"></i> Make a Payment
            </a>
        </div>
    @else
        {{-- Transactions Table --}}
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Transaction ID</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                        <tr>
                            <td>
                                <small><strong>{{ $transaction->transaction_id }}</strong></small>
                            </td>
                            <td>
                                {{ $transaction->customer_name }}<br>
                                <small class="text-muted">{{ $transaction->customer_email }}</small>
                            </td>
                            <td>{{ Str::limit($transaction->product_name, 30) }}</td>
                            <td>
                                <strong>৳{{ number_format($transaction->amount, 2) }}</strong>
                            </td>
                            <td>
                                @if($transaction->isSuccess())
                                    <span class="status-badge status-success">
                                        <i class="fas fa-check"></i> Success
                                    </span>
                                @elseif($transaction->isPending())
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                @elseif($transaction->isProcessing())
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-spinner"></i> Processing
                                    </span>
                                @elseif($transaction->isFailed())
                                    <span class="status-badge status-failed">
                                        <i class="fas fa-times"></i> Failed
                                    </span>
                                @elseif($transaction->isCancelled())
                                    <span class="status-badge status-cancelled">
                                        <i class="fas fa-ban"></i> Cancelled
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $transaction->created_at->format('d M Y') }}<br>{{ $transaction->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <a href="{{ route('payment.status', $transaction->transaction_id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
        
        {{-- Summary Statistics --}}
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="text-muted">Total Transactions</h6>
                        <h3 class="text-primary">{{ $transactions->total() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="text-muted">Successful</h6>
                        <h3 class="text-success">{{ \App\Models\Transaction::successful()->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="text-muted">Pending</h6>
                        <h3 class="text-warning">{{ \App\Models\Transaction::pending()->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="text-muted">Failed</h6>
                        <h3 class="text-danger">{{ \App\Models\Transaction::failed()->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- New Payment Button --}}
<div class="text-center mt-3">
    <a href="{{ route('payment.form') }}" class="btn btn-primary btn-lg">
        <i class="fas fa-plus"></i> Create New Payment
    </a>
</div>
@endsection
