{{--
    Payment Form View
    
    This view displays the payment form where customers enter their information
    and initiate payment through SSL Commerz gateway.
    
    Form Fields:
    - Customer Name: Full name of the customer
    - Customer Email: Email for transaction receipt
    - Customer Phone: Contact number (required by SSL Commerz)
    - Customer Address: Shipping/billing address
    - Customer City: City name
    - Customer Postcode: Postal/ZIP code
    - Product Selection: Choose from available products
    - Amount: Payment amount in BDT
    
    On submission, the form data is sent to PaymentController@initiatePayment
    which creates a transaction and redirects to SSL Commerz gateway.
--}}

@extends('layouts.app')

@section('title', 'Payment Form - SSL Commerz')

@section('content')
<div class="payment-card">
    <h3 class="mb-4"><i class="fas fa-shopping-cart"></i> Make a Payment</h3>
    
    {{-- Display error messages if any --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    {{-- Display validation errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    {{-- Payment Form --}}
    <form action="{{ route('payment.initiate') }}" method="POST" id="paymentForm">
        @csrf
        
        {{-- Customer Information Section --}}
        <div class="mb-4">
            <h5 class="text-primary mb-3"><i class="fas fa-user"></i> Customer Information</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="customer_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('customer_name') is-invalid @enderror" 
                           id="customer_name" 
                           name="customer_name" 
                           value="{{ old('customer_name') }}"
                           placeholder="Enter your full name"
                           required>
                    @error('customer_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="customer_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" 
                           class="form-control @error('customer_email') is-invalid @enderror" 
                           id="customer_email" 
                           name="customer_email" 
                           value="{{ old('customer_email') }}"
                           placeholder="example@email.com"
                           required>
                    @error('customer_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="customer_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                    <input type="tel" 
                           class="form-control @error('customer_phone') is-invalid @enderror" 
                           id="customer_phone" 
                           name="customer_phone" 
                           value="{{ old('customer_phone') }}"
                           placeholder="01700000000"
                           required>
                    @error('customer_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="customer_city" class="form-label">City</label>
                    <input type="text" 
                           class="form-control @error('customer_city') is-invalid @enderror" 
                           id="customer_city" 
                           name="customer_city" 
                           value="{{ old('customer_city', 'Dhaka') }}"
                           placeholder="Dhaka">
                    @error('customer_city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="customer_address" class="form-label">Address</label>
                    <input type="text" 
                           class="form-control @error('customer_address') is-invalid @enderror" 
                           id="customer_address" 
                           name="customer_address" 
                           value="{{ old('customer_address') }}"
                           placeholder="Street address">
                    @error('customer_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="customer_postcode" class="form-label">Postcode</label>
                    <input type="text" 
                           class="form-control @error('customer_postcode') is-invalid @enderror" 
                           id="customer_postcode" 
                           name="customer_postcode" 
                           value="{{ old('customer_postcode', '1000') }}"
                           placeholder="1000">
                    @error('customer_postcode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <hr>
        
        {{-- Product/Service Information Section --}}
        <div class="mb-4">
            <h5 class="text-primary mb-3"><i class="fas fa-box"></i> Product/Service Details</h5>
            
            <div class="mb-3">
                <label for="product_select" class="form-label">Select Product <span class="text-danger">*</span></label>
                <select class="form-select" id="product_select" onchange="updateProductDetails()">
                    <option value="">-- Choose a product --</option>
                    @foreach($products as $product)
                        <option value="{{ $product['id'] }}" 
                                data-name="{{ $product['name'] }}"
                                data-description="{{ $product['description'] }}"
                                data-price="{{ $product['price'] }}"
                                data-category="{{ $product['category'] }}">
                            {{ $product['name'] }} - ৳{{ number_format($product['price'], 2) }}
                        </option>
                    @endforeach
                    <option value="custom">Custom Amount</option>
                </select>
            </div>
            
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('product_name') is-invalid @enderror" 
                           id="product_name" 
                           name="product_name" 
                           value="{{ old('product_name') }}"
                           placeholder="Enter product or service name"
                           required>
                    @error('product_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="product_category" class="form-label">Category</label>
                    <input type="text" 
                           class="form-control @error('product_category') is-invalid @enderror" 
                           id="product_category" 
                           name="product_category" 
                           value="{{ old('product_category', 'General') }}"
                           placeholder="General">
                    @error('product_category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label for="product_description" class="form-label">Description</label>
                <textarea class="form-control @error('product_description') is-invalid @enderror" 
                          id="product_description" 
                          name="product_description" 
                          rows="2"
                          placeholder="Brief description of the product or service">{{ old('product_description') }}</textarea>
                @error('product_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="amount" class="form-label">Amount (BDT) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">৳</span>
                    <input type="number" 
                           class="form-control @error('amount') is-invalid @enderror" 
                           id="amount" 
                           name="amount" 
                           value="{{ old('amount') }}"
                           min="10"
                           step="0.01"
                           placeholder="0.00"
                           required>
                    @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <small class="text-muted">Minimum amount: ৳10.00</small>
            </div>
        </div>
        
        <hr>
        
        {{-- Payment Summary --}}
        <div class="mb-4 p-3" style="background: #f8f9fa; border-radius: 10px;">
            <h6 class="mb-3"><i class="fas fa-receipt"></i> Payment Summary</h6>
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <strong id="summary-amount">৳0.00</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Processing Fee:</span>
                <strong>৳0.00</strong>
            </div>
            <hr>
            <div class="d-flex justify-content-between">
                <strong>Total Amount:</strong>
                <strong class="text-primary" style="font-size: 1.2em;" id="summary-total">৳0.00</strong>
            </div>
        </div>
        
        {{-- Submit Button --}}
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-lock"></i> Proceed to Payment
            </button>
        </div>
        
        {{-- Security Note --}}
        <div class="mt-3 text-center">
            <small class="text-muted">
                <i class="fas fa-shield-alt"></i> 
                Your payment is secured by SSL Commerz - 256-bit SSL encryption
            </small>
        </div>
    </form>
</div>

{{-- Testing Information Card --}}
<div class="payment-card mt-3">
    <h6 class="text-success"><i class="fas fa-info-circle"></i> Sandbox Testing Information</h6>
    <p class="mb-2"><small>This is a sandbox environment for testing. Use the following test cards:</small></p>
    <ul class="small mb-0">
        <li><strong>Visa:</strong> 4111 1111 1111 1111 (Any CVV, Any future expiry date)</li>
        <li><strong>MasterCard:</strong> 5500 0000 0000 0004 (Any CVV, Any future expiry date)</li>
        <li><strong>American Express:</strong> 3400 0000 0000 009 (Any CVV, Any future expiry date)</li>
        <li><strong>Mobile Banking:</strong> Use any valid phone number</li>
    </ul>
</div>
@endsection

@section('scripts')
<script>
/**
 * Update Product Details
 * 
 * Automatically fills in product details when a product is selected
 * from the dropdown menu.
 */
function updateProductDetails() {
    const select = document.getElementById('product_select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value && selectedOption.value !== 'custom') {
        // Get product data from option attributes
        const productName = selectedOption.getAttribute('data-name');
        const productDescription = selectedOption.getAttribute('data-description');
        const productPrice = selectedOption.getAttribute('data-price');
        const productCategory = selectedOption.getAttribute('data-category');
        
        // Fill form fields
        document.getElementById('product_name').value = productName;
        document.getElementById('product_description').value = productDescription;
        document.getElementById('amount').value = productPrice;
        document.getElementById('product_category').value = productCategory;
        
        // Update summary
        updatePaymentSummary();
    } else if (selectedOption.value === 'custom') {
        // Clear fields for custom entry
        document.getElementById('product_name').value = '';
        document.getElementById('product_description').value = '';
        document.getElementById('amount').value = '';
        document.getElementById('product_category').value = 'General';
    }
}

/**
 * Update Payment Summary
 * 
 * Updates the payment summary section when amount changes.
 */
function updatePaymentSummary() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    
    // Format amount as currency
    const formattedAmount = '৳' + amount.toFixed(2);
    
    // Update summary display
    document.getElementById('summary-amount').textContent = formattedAmount;
    document.getElementById('summary-total').textContent = formattedAmount;
}

// Update summary when amount input changes
document.getElementById('amount').addEventListener('input', updatePaymentSummary);

// Initialize summary on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePaymentSummary();
});
</script>
@endsection
