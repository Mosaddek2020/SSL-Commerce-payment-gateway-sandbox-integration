<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SSL Commerz Payment Gateway')</title>
    
    <!-- Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .main-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .payment-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header-section h1 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .header-section .subtitle {
            color: #6c757d;
            font-size: 14px;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            border-radius: 10px;
        }
        
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-cancelled {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .transaction-detail {
            border-bottom: 1px solid #e9ecef;
            padding: 10px 0;
        }
        
        .transaction-detail:last-child {
            border-bottom: none;
        }
        
        .transaction-detail label {
            font-weight: 600;
            color: #495057;
        }
        
        .transaction-detail span {
            color: #6c757d;
        }
        
        .footer-note {
            text-align: center;
            color: white;
            margin-top: 30px;
            font-size: 14px;
        }
        
        .footer-note a {
            color: white;
            text-decoration: underline;
        }
        
        .ssl-logo {
            max-width: 200px;
            margin: 20px auto;
            display: block;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <div class="container main-container">
        <!-- Header -->
        <div class="payment-card">
            <div class="header-section">
                <h1><i class="fas fa-lock"></i> SSL Commerz Payment</h1>
                <p class="subtitle">Secure Payment Gateway Integration</p>
            </div>
        </div>
        
        <!-- Main Content -->
        @yield('content')
        
        <!-- Footer -->
        <div class="footer-note">
            <p>
                <i class="fas fa-shield-alt"></i> Powered by SSL Commerz - Secure Payment Gateway
                <br>
                <small>
                    For testing: Use sandbox credentials from 
                    <a href="https://developer.sslcommerz.com/" target="_blank">SSL Commerz Developer Portal</a>
                </small>
            </p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('scripts')
</body>
</html>
