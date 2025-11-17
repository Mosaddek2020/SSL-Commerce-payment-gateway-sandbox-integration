# SSL Commerz Payment Gateway - Complete Setup Guide

This guide will walk you through setting up the SSL Commerz payment gateway integration step-by-step.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [SSL Commerz Account Setup](#ssl-commerz-account-setup)
3. [Project Installation](#project-installation)
4. [Database Configuration](#database-configuration)
5. [SSL Commerz Configuration](#ssl-commerz-configuration)
6. [Testing the Integration](#testing-the-integration)
7. [Going Live](#going-live)

---

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- **PHP** >= 8.1
- **Composer** (PHP package manager)
- **MySQL** >= 5.7 or **MariaDB** >= 10.3
- **Web Server** (Apache/Nginx) or use Laravel's built-in server
- **Git** (for cloning the repository)

### Verify PHP Installation

```bash
php --version
# Should show PHP 8.1 or higher
```

### Verify Composer Installation

```bash
composer --version
# Should show Composer version 2.x
```

### Verify MySQL Installation

```bash
mysql --version
# Should show MySQL 5.7 or higher
```

---

## SSL Commerz Account Setup

### Step 1: Register for Sandbox Account

1. Visit [SSL Commerz Developer Portal](https://developer.sslcommerz.com/registration/)
2. Fill in the registration form:
   - Company/Business Name
   - Contact Person Name
   - Email Address
   - Phone Number
   - Address
3. Submit the form
4. Check your email for verification link
5. Click the verification link to activate your account

### Step 2: Access Your Dashboard

1. Log in to [SSL Commerz Dashboard](https://developer.sslcommerz.com/)
2. Navigate to **Sandbox** section
3. You'll find your credentials:
   - **Store ID** (e.g., `testbox123456`)
   - **Store Password** (e.g., `testbox123@ssl`)
4. **Save these credentials** - you'll need them later

### Step 3: Configure Your Store

1. In the dashboard, go to **Store Settings**
2. Set your store name and other details
3. Configure callback URLs (we'll set these up later)
4. Save the settings

---

## Project Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/Mosaddek2020/SSL-Commerce-payment-gateway-sandbox-integration.git
cd SSL-Commerce-payment-gateway-sandbox-integration
```

### Step 2: Install Dependencies

```bash
composer install
```

This will install all required PHP packages including Laravel framework and dependencies.

**Note**: If you encounter memory issues, try:
```bash
composer install --no-scripts
```

### Step 3: Set Up Environment File

Copy the example environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

---

## Database Configuration

### Step 1: Create Database

Open MySQL command line:

```bash
mysql -u root -p
# Enter your MySQL root password
```

Create the database:

```sql
CREATE DATABASE sslcommerz_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 2: Configure Database Connection

Edit the `.env` file and update database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sslcommerz_db
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

Replace `your_mysql_password` with your actual MySQL password.

### Step 3: Run Migrations

Create the transactions table:

```bash
php artisan migrate
```

You should see output like:
```
Migration table created successfully.
Migrating: xxxx_create_transactions_table
Migrated:  xxxx_create_transactions_table
```

### Verify Migration

Check if the table was created:

```bash
mysql -u root -p sslcommerz_db -e "SHOW TABLES;"
```

You should see the `transactions` table listed.

---

## SSL Commerz Configuration

### Step 1: Update .env File

Edit `.env` file and add your SSL Commerz credentials:

```env
# SSL Commerz Sandbox Credentials
SSLCOMMERZ_STORE_ID=testbox123456
SSLCOMMERZ_STORE_PASSWORD=testbox123@ssl
SSLCOMMERZ_SANDBOX=true

# Other SSL Commerz Settings
SSLCOMMERZ_CURRENCY=BDT
SSLCOMMERZ_EMI_OPTION=false
SSLCOMMERZ_PRODUCT_PROFILE=general
SSLCOMMERZ_VALIDATE_TRANSACTIONS=true
SSLCOMMERZ_LOGGING=true
SSLCOMMERZ_LOG_LEVEL=info
```

**Important**: Replace the credentials with your actual sandbox credentials from Step 2 of SSL Commerz Account Setup.

### Step 2: Configure Callback URLs (for Production)

If you're deploying to a live server, update callback URLs in `.env`:

```env
SSLCOMMERZ_SUCCESS_URL=https://yourdomain.com/payment/success
SSLCOMMERZ_FAIL_URL=https://yourdomain.com/payment/fail
SSLCOMMERZ_CANCEL_URL=https://yourdomain.com/payment/cancel
SSLCOMMERZ_IPN_URL=https://yourdomain.com/payment/ipn
```

**Note**: For local development, you can skip this step as the URLs are auto-generated.

### Step 3: Local Development with ngrok (Optional)

For testing IPN (Instant Payment Notification) on localhost:

1. Install [ngrok](https://ngrok.com/)
2. Start your Laravel server:
   ```bash
   php artisan serve
   ```
3. In a new terminal, start ngrok:
   ```bash
   ngrok http 8000
   ```
4. Copy the HTTPS URL (e.g., `https://abc123.ngrok.io`)
5. Update `.env`:
   ```env
   APP_URL=https://abc123.ngrok.io
   ```

---

## Testing the Integration

### Step 1: Start the Development Server

```bash
php artisan serve
```

The application will be available at: `http://localhost:8000`

### Step 2: Access the Payment Form

Open your browser and visit: `http://localhost:8000`

You'll be redirected to the payment form.

### Step 3: Fill in Test Data

Enter the following test information:

**Customer Information:**
- Name: John Doe
- Email: john@example.com
- Phone: 01700000000
- City: Dhaka
- Address: 123 Test Street

**Product Selection:**
- Select "Premium Package" (৳1000)
- Or choose "Custom Amount" and enter any amount >= ৳10

### Step 4: Submit and Pay

1. Click "Proceed to Payment"
2. You'll be redirected to SSL Commerz sandbox payment page
3. Use one of the following test cards:

**Test Card Numbers:**

| Card Type | Card Number | CVV | Expiry |
|-----------|-------------|-----|--------|
| Visa | 4111 1111 1111 1111 | 123 | 12/25 |
| MasterCard | 5500 0000 0000 0004 | 123 | 12/25 |
| American Express | 3400 0000 0000 009 | 1234 | 12/25 |

4. Enter any CVV (e.g., 123)
5. Enter any future expiry date (e.g., 12/25)
6. Click "Pay Now"

### Step 5: Verify Transaction

After successful payment:

1. You'll be redirected to the success page
2. Transaction details will be displayed
3. Check transaction list: `http://localhost:8000/payment/transactions`
4. Verify database:
   ```bash
   mysql -u root -p sslcommerz_db -e "SELECT * FROM transactions;"
   ```

### Test Different Scenarios

#### Test Failed Payment:
- On SSL Commerz page, click "Cancel" or use invalid card data
- Verify you're redirected to the fail page

#### Test Cancelled Payment:
- On SSL Commerz page, click "Cancel Payment"
- Verify you're redirected to the cancel page

---

## Going Live

### Step 1: Get Live Credentials

1. Contact SSL Commerz support
2. Complete merchant verification process
3. Provide business documents
4. Receive live credentials:
   - Live Store ID
   - Live Store Password

### Step 2: Update Configuration

Edit `.env` file:

```env
# Switch to live mode
SSLCOMMERZ_SANDBOX=false

# Use live credentials
SSLCOMMERZ_STORE_ID=your_live_store_id
SSLCOMMERZ_STORE_PASSWORD=your_live_store_password

# Production settings
APP_ENV=production
APP_DEBUG=false
```

### Step 3: Production Checklist

- [ ] Use HTTPS (SSL certificate) for your website
- [ ] Update callback URLs to production domain
- [ ] Test all payment scenarios in production
- [ ] Set up database backups
- [ ] Configure error logging and monitoring
- [ ] Set up email notifications for transactions
- [ ] Add authentication for admin routes
- [ ] Review security settings
- [ ] Test with small amounts first
- [ ] Monitor transactions closely

### Step 4: Security Hardening

1. **Environment Variables**: Never commit `.env` to version control
2. **HTTPS Only**: Enforce HTTPS in production
3. **Rate Limiting**: Add rate limiting to payment routes
4. **Logging**: Monitor all transactions
5. **Validation**: Always validate transactions with SSL Commerz API
6. **Backups**: Regular database backups

---

## Common Issues and Solutions

### Issue 1: "SQLSTATE[HY000] [1049] Unknown database"

**Solution**: Database doesn't exist. Create it:
```bash
mysql -u root -p -e "CREATE DATABASE sslcommerz_db;"
```

### Issue 2: "Store ID/Password incorrect"

**Solution**: 
- Verify credentials in `.env` match your SSL Commerz dashboard
- Ensure no extra spaces in credentials
- Check if you're using sandbox credentials in sandbox mode

### Issue 3: "Class 'App\Services\SSLCommerzService' not found"

**Solution**: Run composer autoload:
```bash
composer dump-autoload
```

### Issue 4: IPN not receiving callbacks

**Solution**:
- Ensure your server is publicly accessible
- Use ngrok for local testing
- Check firewall settings
- Verify IPN URL in SSL Commerz dashboard

### Issue 5: Transaction not saving to database

**Solution**:
- Check database connection in `.env`
- Verify migrations ran successfully
- Check Laravel logs: `storage/logs/laravel.log`

---

## Support and Documentation

### SSL Commerz Resources
- [Official Documentation](https://developer.sslcommerz.com/)
- [API Reference](https://developer.sslcommerz.com/documentation/)
- [Support Email](mailto:support@sslcommerz.com)

### Laravel Resources
- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Forums](https://laracasts.com/discuss)

### Project Repository
- [GitHub Issues](https://github.com/Mosaddek2020/SSL-Commerce-payment-gateway-sandbox-integration/issues)

---

## Next Steps

After completing the setup:

1. **Explore the Code**: Check the extensively documented code
2. **Customize**: Modify views and logic for your needs
3. **Add Features**: Implement additional features like:
   - Email notifications
   - Order management
   - User accounts
   - Invoice generation
4. **Deploy**: Deploy to production server

---

**Happy Coding! 🚀**

If you find this project helpful, please give it a star on GitHub!
