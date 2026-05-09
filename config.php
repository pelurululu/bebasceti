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

// App
define('APP_URL',          rtrim(getenv('APP_URL') ?: 'https://your-app.onrender.com', '/'));

// Email sender (must match your Brevo verified sender)
define('SENDER_EMAIL',     getenv('SENDER_EMAIL')            ?: 'noreply@bebasceti.my');
define('SENDER_NAME',      getenv('SENDER_NAME')             ?: 'BebasCeti.my');

// ── PACKAGES ──────────────────────────────────────────────
// Price in cents (RM29 = 2900, RM59 = 5900, RM97 = 9700)
// Set DOWNLOAD_URL_* per package in your Render env vars
define('PACKAGES', [
    'PAKEJ SEDAR' => [
        'price'        => (int)(getenv('PRICE_SEDAR')    ?: 2900),
        'bill_name'    => 'BebasCeti — Pakej Sedar',
        'bill_desc'    => 'BebasCeti.my Pakej Sedar (2 Dokumen)',
        'download_url' => getenv('DOWNLOAD_URL_SEDAR')   ?: 'https://bebasceti.my/download/sedar',
    ],
    'PAKEJ TINDAK' => [
        'price'        => (int)(getenv('PRICE_TINDAK')   ?: 5900),
        'bill_name'    => 'BebasCeti — Pakej Tindak',
        'bill_desc'    => 'BebasCeti.my Pakej Tindak (4 Dokumen)',
        'download_url' => getenv('DOWNLOAD_URL_TINDAK')  ?: 'https://bebasceti.my/download/tindak',
    ],
    'PAKEJ KOMANDO' => [
        'price'        => (int)(getenv('PRICE_KOMANDO')  ?: 9700),
        'bill_name'    => 'BebasCeti — Pakej Komando',
        'bill_desc'    => 'BebasCeti.my Pakej Komando (6 Dokumen Penuh)',
        'download_url' => getenv('DOWNLOAD_URL_KOMANDO') ?: 'https://bebasceti.my/download/komando',
    ],
]);
