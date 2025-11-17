# SSL Commerz Payment Gateway - Laravel Integration

<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

A complete, production-ready Laravel application with **SSL Commerz Payment Gateway** sandbox integration. This project demonstrates how to integrate Bangladesh's leading payment gateway with extensive documentation and comments.

## 🚀 Features

- ✅ **Complete SSL Commerz Integration** - Fully functional payment gateway integration
- ✅ **Sandbox Testing Ready** - Pre-configured for sandbox environment
- ✅ **Database Migrations** - Comprehensive transactions table with all required fields
- ✅ **Payment Flow Management** - Handle success, failure, and cancellation scenarios
- ✅ **IPN Support** - Instant Payment Notification (server-to-server) implementation
- ✅ **Transaction Management** - Track and manage all payment transactions
- ✅ **Responsive UI** - Beautiful Bootstrap-based payment forms and status pages
- ✅ **Extensive Documentation** - Detailed comments and documentation throughout the codebase
- ✅ **Security Best Practices** - Transaction validation, CSRF protection, and secure callbacks
- ✅ **Multiple Payment Methods** - Support for cards, mobile banking, and internet banking

## 📋 Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [SSL Commerz Setup](#ssl-commerz-setup)
- [Usage](#usage)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [API Flow](#api-flow)
- [Troubleshooting](#troubleshooting)
- [Security](#security)
- [Contributing](#contributing)
- [License](#license)

## 🔧 Requirements

- PHP >= 8.1
- Composer
- MySQL >= 5.7 or MariaDB >= 10.3
- Laravel 10.x
- SSL Commerz Sandbox Account ([Register here](https://developer.sslcommerz.com/registration/))

## 📦 Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/Mosaddek2020/SSL-Commerce-payment-gateway-sandbox-integration.git
cd SSL-Commerce-payment-gateway-sandbox-integration
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### Step 4: Configure Environment Variables

Edit `.env` file and update the following:

```env
# Application Settings
APP_NAME="SSL Commerz Payment"
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sslcommerz_db
DB_USERNAME=root
DB_PASSWORD=your_password

# SSL Commerz Configuration
SSLCOMMERZ_STORE_ID=your_store_id_here
SSLCOMMERZ_STORE_PASSWORD=your_store_password_here
SSLCOMMERZ_SANDBOX=true
```

## 🗄️ Database Setup

### Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE sslcommerz_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Run Migrations

```bash
php artisan migrate
```

This will create the `transactions` table with the following structure:

- **Transaction identifiers**: transaction_id, order_id
- **Customer information**: name, email, phone, address, city, postcode, country
- **Product details**: name, description, category
- **Payment information**: amount, paid_amount, currency
- **Gateway data**: gateway_transaction_id, bank_transaction_id, card details
- **Status tracking**: status, validation_status, risk assessment
- **Metadata**: API responses, IPN data, custom values
- **Timestamps**: created_at, updated_at, paid_at, deleted_at

## 🔐 SSL Commerz Setup

### 1. Register for Sandbox Account

Visit [SSL Commerz Developer Portal](https://developer.sslcommerz.com/registration/) and create an account.

### 2. Get Sandbox Credentials

After registration, you'll receive:
- **Store ID** (e.g., `testbox123456`)
- **Store Password** (e.g., `testbox123@ssl`)

### 3. Update .env File

```env
SSLCOMMERZ_STORE_ID=testbox123456
SSLCOMMERZ_STORE_PASSWORD=testbox123@ssl
SSLCOMMERZ_SANDBOX=true
```

### 4. Configure Callback URLs

The application automatically generates callback URLs, but you can verify them in your SSL Commerz dashboard:

- **Success URL**: `http://your-domain.com/payment/success`
- **Fail URL**: `http://your-domain.com/payment/fail`
- **Cancel URL**: `http://your-domain.com/payment/cancel`
- **IPN URL**: `http://your-domain.com/payment/ipn`

**Note**: For local development, use tools like [ngrok](https://ngrok.com/) to expose your local server for IPN callbacks.

## 🚀 Usage

### Start Development Server

```bash
php artisan serve
```

Visit: `http://localhost:8000`

### Payment Flow

1. **Fill Payment Form** → Enter customer and product details
2. **Initiate Payment** → System creates transaction and redirects to SSL Commerz
3. **Complete Payment** → Customer pays on SSL Commerz gateway
4. **Callback Handling** → System receives callback and validates transaction
5. **View Status** → Customer sees payment confirmation

### Available Routes

```
GET  /                          → Redirects to payment form
GET  /payment                   → Display payment form
POST /payment/initiate          → Initiate payment session
POST /payment/success           → Success callback from SSL Commerz
POST /payment/fail              → Failure callback from SSL Commerz
POST /payment/cancel            → Cancel callback from SSL Commerz
POST /payment/ipn               → IPN (Instant Payment Notification)
GET  /payment/status/{id}       → View transaction status
GET  /payment/transactions      → List all transactions (admin)
```

## 🧪 Testing

### Test Card Numbers (Sandbox)

SSL Commerz provides test cards for sandbox testing:

| Card Type | Card Number | CVV | Expiry Date |
|-----------|-------------|-----|-------------|
| Visa | 4111 1111 1111 1111 | Any | Any future date |
| MasterCard | 5500 0000 0000 0004 | Any | Any future date |
| American Express | 3400 0000 0000 009 | Any | Any future date |

### Mobile Banking Testing

Use any valid mobile number for testing mobile banking payments (bKash, Nagad, Rocket, etc.)

### Test Payment Flow

1. Navigate to payment form
2. Fill in customer details
3. Select a product or enter custom amount (min: ৳10)
4. Submit form
5. Use test card on SSL Commerz gateway
6. Verify transaction status

## 📁 Project Structure

```
app/
├── Http/Controllers/
│   └── PaymentController.php       # Main payment controller
├── Models/
│   └── Transaction.php             # Transaction model with helpers
└── Services/
    └── SSLCommerzService.php       # SSL Commerz API service

config/
└── sslcommerz.php                  # SSL Commerz configuration

database/migrations/
└── xxxx_create_transactions_table.php  # Transactions table migration

resources/views/
├── layouts/
│   └── app.blade.php               # Main layout
└── payment/
    ├── form.blade.php              # Payment form
    ├── success.blade.php           # Success page
    ├── failed.blade.php            # Failed page
    ├── cancelled.blade.php         # Cancelled page
    ├── status.blade.php            # Transaction status
    └── list.blade.php              # Transactions list

routes/
└── web.php                         # Web routes with documentation
```

## 🔄 API Flow

### 1. Payment Initiation

```
Customer → Payment Form → PaymentController@initiatePayment
    ↓
Transaction Created (Status: Pending)
    ↓
API Request to SSL Commerz
    ↓
Receive Gateway URL
    ↓
Redirect Customer to SSL Commerz
```

### 2. Payment Processing

```
Customer on SSL Commerz Gateway
    ↓
Enters Card Details
    ↓
SSL Commerz Processes Payment
    ↓
Success/Fail/Cancel Decision
```

### 3. Callback Handling

```
SSL Commerz → Callback URL (Success/Fail/Cancel)
    ↓
PaymentController receives callback
    ↓
Validate transaction with SSL Commerz API
    ↓
Update transaction status in database
    ↓
Show appropriate page to customer
```

### 4. IPN (Instant Payment Notification)

```
SSL Commerz → IPN URL (Server-to-Server)
    ↓
PaymentController@handleIPN
    ↓
Validate and update transaction
    ↓
Return success response to SSL Commerz
```

## 🐛 Troubleshooting

### Common Issues

#### 1. SSL Commerz API Error: "Store ID/Password incorrect"

**Solution**: Double-check your credentials in `.env` file. Make sure you're using sandbox credentials for testing.

#### 2. IPN Not Receiving Callbacks

**Solution**: 
- For local development, use [ngrok](https://ngrok.com/) to expose your server
- Update IPN URL in `.env` with public URL
- Check your firewall settings

#### 3. Transaction Validation Fails

**Solution**:
- Ensure you're validating with correct `val_id` from callback
- Check that amount matches original transaction
- Verify transaction ID matches

#### 4. Database Connection Error

**Solution**:
- Verify database credentials in `.env`
- Ensure MySQL service is running
- Check database exists: `mysql -u root -p` then `SHOW DATABASES;`

#### 5. Page Not Found (404)

**Solution**:
- Run `php artisan route:list` to see all routes
- Clear route cache: `php artisan route:clear`
- Ensure you're accessing correct URL

### Debug Mode

Enable detailed logging in `.env`:

```env
APP_DEBUG=true
LOG_LEVEL=debug
SSLCOMMERZ_LOGGING=true
```

Check logs at: `storage/logs/laravel.log`

## 🔒 Security

### Best Practices Implemented

1. **Transaction Validation** - Always validate with SSL Commerz API
2. **CSRF Protection** - Laravel's built-in CSRF tokens on all forms
3. **SQL Injection Prevention** - Using Eloquent ORM
4. **XSS Protection** - Blade template escaping
5. **Secure Credentials** - Environment variables for sensitive data
6. **HTTPS Required** - Use HTTPS in production
7. **Input Validation** - Server-side validation on all inputs
8. **Soft Deletes** - Transaction records are never permanently deleted

### Production Checklist

- [ ] Change `SSLCOMMERZ_SANDBOX` to `false`
- [ ] Use live SSL Commerz credentials
- [ ] Set `APP_ENV` to `production`
- [ ] Set `APP_DEBUG` to `false`
- [ ] Use HTTPS (SSL certificate)
- [ ] Configure proper database backups
- [ ] Set up monitoring and alerts
- [ ] Implement rate limiting
- [ ] Add authentication for admin routes
- [ ] Review and update CORS settings

## 📚 Additional Resources

- [SSL Commerz Official Documentation](https://developer.sslcommerz.com/)
- [Laravel Documentation](https://laravel.com/docs)
- [SSL Commerz Sandbox Testing Guide](https://developer.sslcommerz.com/documentation/integration-steps/)
- [Payment Gateway Best Practices](https://developer.sslcommerz.com/documentation/security/)

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👨‍💻 Author

**Mosaddek**

For questions or support, please open an issue on GitHub.

---

**⭐ If you find this project helpful, please give it a star!**

## 🙏 Acknowledgments

- Laravel Framework
- SSL Commerz Payment Gateway
- Bootstrap CSS Framework
- Font Awesome Icons
