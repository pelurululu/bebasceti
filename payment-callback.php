<?php
// ============================================================
//  payment-callback.php  —  Server-to-server POST from ToyyibPay
//  ToyyibPay POSTs here after every payment attempt
//  Must return "OK" (plain text, no HTML)
// ============================================================

require_once __DIR__ . '/config.php';

$refNo    = $_POST['refno']    ?? '';
$status   = $_POST['status']   ?? '';   // 1=success 2=pending 3=fail
$billCode = $_POST['billcode'] ?? '';
$amount   = $_POST['amount']   ?? '';
$reason   = $_POST['reason']   ?? '';

// Log every callback for debugging
$logEntry = date('Y-m-d H:i:s') . " | status=$status | billcode=$billCode | refno=$refNo | amount=$amount | reason=$reason\n";
file_put_contents(__DIR__ . '/logs/callback.log', $logEntry, FILE_APPEND);

// Only proceed on successful payment
if ($status !== '1') {
    echo 'OK';
    exit;
}

// ---------- Load buyer info from saved file ----------
$dataFile = __DIR__ . '/data/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $billCode) . '.json';

$buyerName   = '';
$buyerEmail  = '';
$downloadUrl = '';

if (file_exists($dataFile)) {
    $saved       = json_decode(file_get_contents($dataFile), true);
    $buyerName   = $saved['name']         ?? '';
    $buyerEmail  = $saved['email']        ?? '';
    $downloadUrl = $saved['download_url'] ?? '';
}

// Fallback download URL — use most complete package if unknown
if (!$downloadUrl) {
    $packages    = PACKAGES;
    $downloadUrl = $packages['PAKEJ TINDAK']['download_url'];
}

// Fallback: query ToyyibPay transactions API if file missing
if (!$buyerEmail) {
    $ch = curl_init(TP_BASE_URL . '/index.php/api/getBillTransactions');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'userSecretKey'      => TP_SECRET_KEY,
            'billCode'           => $billCode,
            'billpaymentStatus'  => 1,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $txResponse   = curl_exec($ch);
    curl_close($ch);
    $transactions = json_decode($txResponse, true);

    if (is_array($transactions)) {
        foreach ($transactions as $tx) {
            if (($tx['billpaymentRefNo'] ?? '') === $refNo) {
                $buyerName  = $tx['billpaymentBuyerName']  ?? '';
                $buyerEmail = $tx['billpaymentBuyerEmail'] ?? '';
                break;
            }
        }
    }
}

// ---------- Send ebook email via Brevo ----------
if ($buyerEmail) {
    $sent = sendEbookEmail($buyerName, $buyerEmail, $downloadUrl);
    $logEntry2 = date('Y-m-d H:i:s') . " | email_sent=" . ($sent ? 'YES' : 'NO') . " | to=$buyerEmail\n";
    file_put_contents(__DIR__ . '/logs/email.log', $logEntry2, FILE_APPEND);

    // Clean up temp file after successful send
    if ($sent && file_exists($dataFile)) {
        @unlink($dataFile);
    }
}

echo 'OK';
exit;

// ============================================================
//  Helper: Send ebook download email via Brevo API
// ============================================================
function sendEbookEmail(string $name, string $email, string $downloadUrl): bool
{
    $displayName = $name ?: 'Pelanggan';

    $htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="ms">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);max-width:100%;">
        <!-- Header -->
        <tr><td style="background:#020812;padding:32px 40px;text-align:center;">
          <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:900;letter-spacing:1px;">Bebas<span style="color:#dc3545;">Ceti</span>.my</h1>
          <p style="margin:8px 0 0;color:#94a3b8;font-size:13px;">Sistem Pengurusan Krisis Ahlong</p>
        </td></tr>
        <!-- Body -->
        <tr><td style="padding:40px;">
          <h2 style="margin:0 0 16px;color:#020812;font-size:24px;">🎉 Pembayaran Berjaya!</h2>
          <p style="color:#475569;font-size:16px;line-height:1.6;">Hai <strong>{$displayName}</strong>,</p>
          <p style="color:#475569;font-size:16px;line-height:1.6;">Terima kasih kerana mempercayai BebasCeti.my. Dokumen anda telah sedia untuk dimuat turun. Klik butang di bawah sekarang:</p>
          
          <div style="text-align:center;margin:32px 0;">
            <a href="{$downloadUrl}" style="display:inline-block;background:#dc3545;color:#ffffff;text-decoration:none;font-weight:bold;font-size:18px;padding:16px 40px;border-radius:50px;box-shadow:0 4px 20px rgba(220,53,69,0.35);">
              📥 Muat Turun Pakej Sekarang
            </a>
          </div>

          <div style="background:#f8fafc;border-left:4px solid #dc3545;padding:16px 20px;border-radius:8px;margin:24px 0;">
            <p style="margin:0;color:#475569;font-size:14px;">⚠️ <strong>Simpan email ini.</strong> Link muat turun adalah eksklusif untuk anda. Jangan kongsikan dengan orang lain.</p>
          </div>

          <p style="color:#475569;font-size:14px;line-height:1.6;">Jika ada sebarang masalah atau pertanyaan, balas email ini atau hubungi <a href="mailto:support@bebasceti.my" style="color:#dc3545;">support@bebasceti.my</a>. Kami akan membantu anda.</p>
          <p style="color:#020812;font-size:15px;margin-top:24px;">Semoga bermanfaat,<br><strong>Team BebasCeti.my</strong></p>
        </td></tr>
        <!-- Footer -->
        <tr><td style="background:#f8fafc;padding:20px 40px;text-align:center;border-top:1px solid #e2e8f0;">
          <p style="margin:0;color:#94a3b8;font-size:12px;">© 2026 BebasCeti.my. Hak Cipta Terpelihara.<br>Email ini dihantar kerana anda membuat pembelian di laman kami.</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

    $payload = json_encode([
        'sender'      => ['name' => SENDER_NAME, 'email' => SENDER_EMAIL],
        'to'          => [['email' => $email, 'name' => $displayName]],
        'subject'     => '📥 Ebook Anda Sedia — Muat Turun Sekarang!',
        'htmlContent' => $htmlContent,
    ]);

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'api-key: ' . BREVO_API_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $resp    = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode >= 200 && $httpCode < 300;
}
