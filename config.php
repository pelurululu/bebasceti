<?php
// ============================================================
//  CONFIG — set these as Environment Variables in Render
//  NEVER hardcode secrets here in production
// ============================================================

// ToyyibPay
define('TP_SECRET_KEY',    getenv('TOYYIBPAY_SECRET_KEY')    ?: 'YOUR_SECRET_KEY');
define('TP_CATEGORY_CODE', getenv('TOYYIBPAY_CATEGORY_CODE') ?: 'YOUR_CATEGORY_CODE');
define('TP_BASE_URL',      'https://toyyibpay.com');

// Brevo (Sendinblue)
define('BREVO_API_KEY',    getenv('BREVO_API_KEY')           ?: 'YOUR_BREVO_API_KEY');

// Ebook
define('EBOOK_PRICE',      getenv('EBOOK_PRICE')             ?: '1990');  // in cents → RM 19.90
define('EBOOK_DOWNLOAD_URL', getenv('EBOOK_DOWNLOAD_URL')   ?: 'https://your-domain.com/ebook.pdf');
define('BILL_NAME', 'Survival Guide Ahlong');
define('BILL_DESC',        'Ebook Survival Guide 2026');

// App
define('APP_URL',          rtrim(getenv('APP_URL') ?: 'https://your-app.onrender.com', '/'));

// Email sender (must match your Brevo verified sender)
define('SENDER_EMAIL',     getenv('SENDER_EMAIL')            ?: 'noreply@yourdomain.com');
define('SENDER_NAME',      getenv('SENDER_NAME')             ?: 'Survival Guide');
