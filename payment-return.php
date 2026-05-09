<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran — BebasCeti.my</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;700;900&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 font-[Poppins]">
<?php
// ToyyibPay redirects here with GET params after user pays (or cancels)
// status_id: 1=success, 2=pending, 3=fail
$statusId = $_GET['status_id'] ?? $_POST['status_id'] ?? '3';
$billCode = htmlspecialchars($_GET['billcode'] ?? '');

$config = [
    '1' => [
        'icon'    => '🎉',
        'title'   => 'Pembayaran Berjaya!',
        'sub'     => 'Terima kasih! E-mel dengan link muat turun ebook sedang dalam perjalanan ke peti masuk anda. Sila semak folder Spam jika tidak muncul dalam 5 minit.',
        'color'   => 'emerald',
        'border'  => 'border-emerald-500',
        'badge'   => 'bg-emerald-500',
        'badge_text' => 'Pembayaran Diterima',
    ],
    '2' => [
        'icon'    => '⏳',
        'title'   => 'Pembayaran Dalam Proses',
        'sub'     => 'Pembayaran anda sedang diproses. Kami akan hantar e-mel sebaik sahaja pembayaran disahkan. Proses ini biasanya mengambil masa beberapa minit.',
        'color'   => 'amber',
        'border'  => 'border-amber-400',
        'badge'   => 'bg-amber-400',
        'badge_text' => 'Sedang Diproses',
    ],
    '3' => [
        'icon'    => '❌',
        'title'   => 'Pembayaran Tidak Berjaya',
        'sub'     => 'Pembayaran anda tidak berjaya atau telah dibatalkan. Tiada caj dikenakan. Sila cuba semula.',
        'color'   => 'red',
        'border'  => 'border-red-500',
        'badge'   => 'bg-red-500',
        'badge_text' => 'Pembayaran Gagal',
    ],
];

$s = $config[$statusId] ?? $config['3'];
?>
    <div class="bg-slate-800 rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border <?= $s['border'] ?> border-2">
        <!-- Header -->
        <div class="bg-slate-900 px-8 pt-10 pb-6 text-center border-b border-slate-700">
            <p class="font-black text-xl tracking-wider text-white mb-1">Bebas<span class="text-red-500">Ceti</span>.my</p>
            <span class="<?= $s['badge'] ?> text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest">
                <?= $s['badge_text'] ?>
            </span>
        </div>

        <!-- Body -->
        <div class="px-8 py-10 text-center">
            <div class="text-7xl mb-6"><?= $s['icon'] ?></div>
            <h1 class="text-2xl font-black text-white mb-4"><?= $s['title'] ?></h1>
            <p class="text-slate-400 text-base leading-relaxed"><?= $s['sub'] ?></p>

            <?php if ($statusId === '1'): ?>
            <div class="mt-8 bg-slate-700/60 rounded-xl p-4 text-sm text-slate-300 text-left space-y-2 border border-slate-600">
                <p class="flex items-center gap-2"><span class="text-emerald-400">✓</span> E-mel dihantar secara automatik</p>
                <p class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Semak folder <strong>Inbox</strong> dan <strong>Spam</strong></p>
                <p class="flex items-center gap-2"><span class="text-emerald-400">✓</span> Simpan e-mel tersebut untuk rujukan</p>
            </div>
            <?php endif; ?>

            <div class="mt-8 space-y-3">
                <?php if ($statusId === '3'): ?>
                <a href="/" class="block w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-full transition-colors">
                    Cuba Semula
                </a>
                <?php endif; ?>
                <a href="/" class="block w-full bg-slate-700 hover:bg-slate-600 text-slate-300 font-semibold py-3 px-6 rounded-full transition-colors text-sm">
                    Kembali ke Laman Utama
                </a>
            </div>
        </div>
    </div>
</body>
</html>
