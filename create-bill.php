<?php
// ============================================================
//  create-bill.php  —  Called by frontend form submission
//  Returns JSON: { success, payment_url } or { success, error }
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { http_response_code(405); exit; }

require_once __DIR__ . '/config.php';

// ---------- Sanitise input ----------
$body  = json_decode(file_get_contents('php://input'), true);
$name  = trim(strip_tags($body['name']  ?? ''));
$email = trim(strip_tags($body['email'] ?? ''));
$phone = trim(strip_tags($body['phone'] ?? ''));

if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$phone) {
    echo json_encode(['success' => false, 'error' => 'Sila isi semua maklumat dengan betul.']);
    exit;
}

// ---------- Create ToyyibPay bill ----------
$externalRef = 'ebook_' . time() . '_' . bin2hex(random_bytes(4));

$billData = [
    'userSecretKey'          => TP_SECRET_KEY,
    'categoryCode'           => TP_CATEGORY_CODE,
    'billName'               => BILL_NAME,
    'billDescription'        => BILL_DESC,
    'billPriceSetting'       => 1,              // 1 = fixed price
    'billPayorInfo'          => 1,              // 1 = collect payor info
    'billAmount'             => EBOOK_PRICE,    // in cents
    'billReturnUrl'          => APP_URL . '/payment-return.php',
    'billCallbackUrl'        => APP_URL . '/payment-callback.php',
    'billExternalReferenceNo'=> $externalRef,
    'billTo'                 => $name,
    'billEmail'              => $email,
    'billPhone'              => $phone,
    'billSplitPayment'       => 0,
    'billSplitPaymentArgs'   => '',
    'billPaymentChannel'     => 0,              // 0 = all channels (FPX + card)
    'billContentEmail'       => 'Terima kasih kerana membeli Survival Guide kami! Link muat turun akan dihantar ke e-mel anda.',
    'billChargeToCustomer'   => 1,              // 1 = buyer absorbs processing fee
    'billExpiryDays'         => 1,
];

$ch = curl_init(TP_BASE_URL . '/index.php/api/createBill');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($billData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => true,
]);
$response = curl_exec($ch);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    error_log("ToyyibPay cURL error: $curlErr");
    echo json_encode(['success' => false, 'error' => 'Masalah sambungan. Cuba sebentar lagi.']);
    exit;
}

$result = json_decode($response, true);

if ($result && isset($result[0]['BillCode'])) {
    $billCode    = $result[0]['BillCode'];
    $paymentUrl  = TP_BASE_URL . '/' . $billCode;

    // Save buyer info keyed by billCode so callback can retrieve it
    $dataFile = __DIR__ . '/data/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $billCode) . '.json';
    file_put_contents($dataFile, json_encode([
        'name'  => $name,
        'email' => $email,
        'phone' => $phone,
        'ref'   => $externalRef,
        'ts'    => time(),
    ]));

    echo json_encode(['success' => true, 'payment_url' => $paymentUrl]);
} else {
    error_log("ToyyibPay error response: $response");
    echo json_encode(['success' => false, 'error' => 'Gagal mencipta bil. Cuba sebentar lagi.']);
}
