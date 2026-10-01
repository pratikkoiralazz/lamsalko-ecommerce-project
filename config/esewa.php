<?php
// Official eSewa v2 API Integration Settings
// Test Merchant Code: EPAYTEST
define('ESEWA_MERCHANT_CODE', 'EPAYTEST');
define('ESEWA_SECRET_KEY', '8gBmIt3$asdf'); // Official eSewa Sandbox HMAC Secret Key
define('ESEWA_MODE', 'sandbox'); // 'sandbox' or 'production'

if (ESEWA_MODE === 'sandbox') {
    define('ESEWA_PAYMENT_URL', 'https://rc-epay.esewa.com.np/api/epay/main/v2/form');
    define('ESEWA_VERIFY_URL', 'https://kms.esewa.com.np/api/epay/transaction/status/');
} else {
    define('ESEWA_PAYMENT_URL', 'https://epay.esewa.com.np/api/epay/main/v2/form');
    define('ESEWA_VERIFY_URL', 'https://epay.esewa.com.np/api/epay/transaction/status/');
}