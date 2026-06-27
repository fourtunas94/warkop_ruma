<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
helper('app_setting');

$storeNameForReceipt = app_setting('store_name', 'ARZANA');

$formatMetode = static function ($metode) {
    return match ($metode) {
        'tunai'    => 'Tunai',
        'qris'     => 'QRIS / E-Wallet',
        'transfer' => 'Transfer Bank',
        default    => ucfirst((string) $metode),
    };
};

$statusBadgeClass = static function ($status) {
    return match ($status) {
        'selesai' => 'bg-emerald-500/10 text-emerald-300 border-emerald-400/20',
        'pending' => 'bg-amber-500/10 text-amber-300 border-amber-400/20',
        default   => 'bg-slate-500/10 text-slate-300 border-slate-400/20',
    };
};

$statusLabel = static function ($status) {
    return match ($status) {
        'selesai' => 'Selesai',
        'pending' => 'Pending',
        default   => ucfirst((string) $status),
    };
};

$formatRupiah = static function ($value): string {
    return 'Rp ' . number_format((int) $value, 0, ',', '.');
};

$receiptData = static function (array $p) use ($formatMetode): array {
    $items = [];
    $subtotal = 0;

    foreach (($p['items'] ?? []) as $item) {
        $harga = (int) ($item['harga'] ?? 0);
        $qty   = (int) ($item['qty'] ?? 0);
        $sub   = $harga * $qty;

        $subtotal += $sub;

        $items[] = [
            'nama_menu' => $item['nama_menu'] ?? '-',
            'harga'     => $harga,
            'qty'       => $qty,
            'subtotal'  => $sub,
            'catatan'   => $item['catatan'] ?? '',
        ];
    }

    $potongan = (int) ($p['potongan'] ?? 0);
    $ppn      = (int) ($p['ppn'] ?? 0);
    $total    = (int) ($p['total_harga'] ?? 0);

    if ($subtotal <= 0 && $total > 0) {
        $subtotal = max(0, $total - $ppn + $potongan);
    }

    return [
        'id'                => (int) ($p['id'] ?? 0),
        'kode_pesanan'      => $p['kode_pesanan'] ?? '-',
        'nomor_meja'        => $p['nomor_meja'] ?? '-',
        'nama_pemesan'      => $p['nama_pemesan'] ?? '-',
        'metode_pembayaran' => $p['metode_pembayaran'] ?? '-',
        'metode_label'      => $formatMetode($p['metode_pembayaran'] ?? '-'),
        'status'            => $p['status'] ?? 'pending',
        'catatan'           => $p['catatan'] ?? '',
        'created_at'        => $p['created_at'] ?? date('Y-m-d H:i:s'),
        'waktu'             => ! empty($p['created_at']) ? date('H:i', strtotime($p['created_at'])) : date('H:i'),
        'subtotal'          => $subtotal,
        'potongan'          => $potongan,
        'ppn'               => $ppn,
        'total_harga'       => $total,
        'items'             => $items,
    ];
};

$riwayatHariIni = $riwayat_hari_ini ?? [];
$jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
?>

<div class="space-y-6">

    <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft">
        <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-sky-500/20 blur-3xl"></div>
        <div class="absolute -bottom-28 -left-24 w-72 h-72 rounded-full bg-purple-600/20 blur-3xl"></div>

        <div class="relative p-5 lg:p-7">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-400/20 text-emerald-300 text-xs font-semibold mb-4">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Kasir Live Monitor
                    </div>

                    <h1 class="text-2xl lg:text-4xl font-extrabold tracking-tight text-white">
                        Antrean Pesanan
                    </h1>

                    <p class="text-sm lg:text-base text-slate-400 mt-2 max-w-2xl">
                        Pantau pesanan masuk, konfirmasi pesanan selesai, dan cetak struk untuk pelanggan.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            id="btn-enable-sound"
                            onclick="aktifkanSuara()"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-sky-500 hover:bg-sky-600 px-4 py-3 text-sm font-bold text-white transition">
                        <i class="fa-solid fa-volume-high"></i>
                        Aktifkan Suara
                    </button>

                    <div class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/[.04] border border-white/10 px-4 py-3 text-sm text-slate-300">
                        <i class="fa-regular fa-clock text-sky-300"></i>
                        <span id="waktu-sekarang" class="font-bold text-white"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
            <i class="fa-solid fa-circle-check"></i>
            <span><?= esc(session()->getFlashdata('success')) ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="flex items-center gap-3 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= esc(session()->getFlashdata('error')) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-[1.5rem] border border-white/10 bg-white/[.035] backdrop-blur p-5 shadow-soft">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-400/20 flex items-center justify-center text-amber-300">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-[0.16em]">Menunggu</p>
                    <p class="text-2xl font-extrabold text-white mt-1" id="count-pending"><?= (int) $total_pending ?></p>
                </div>
            </div>
        </div>

        <div class="rounded-[1.5rem] border border-white/10 bg-white/[.035] backdrop-blur p-5 shadow-soft">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center text-emerald-300">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-[0.16em]">Selesai Hari Ini</p>
                    <p class="text-2xl font-extrabold text-white mt-1" id="count-selesai"><?= (int) $total_selesai ?></p>
                </div>
            </div>
        </div>

        <div class="rounded-[1.5rem] border border-white/10 bg-white/[.035] backdrop-blur p-5 shadow-soft">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-sky-500/10 border border-sky-400/20 flex items-center justify-center text-sky-300">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-[0.16em]">Pendapatan Hari Ini</p>
                    <p class="text-2xl font-extrabold text-white mt-1" id="pendapatan-hari-ini" data-amount="<?= (int) $pendapatan ?>">
                        <?= $formatRupiah($pendapatan) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft overflow-hidden">
        <div class="p-5 lg:p-6 border-b border-white/10 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-lg font-extrabold text-white">Pesanan Menunggu</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Pesanan yang belum selesai dibuat dapur atau belum dikonfirmasi kasir.
                </p>
            </div>

            <div class="inline-flex items-center gap-2 text-xs text-slate-400 bg-white/[.04] border border-white/10 px-3 py-2 rounded-2xl">
                <i class="fa-solid fa-rotate text-sky-300"></i>
                Auto refresh setiap 3 detik
            </div>
        </div>

        <div class="p-4 lg:p-5">
            <div id="order-container" class="space-y-4 <?= empty($pesanan_pending) ? 'hidden' : '' ?>">
                <?php foreach (($pesanan_pending ?? []) as $p): ?>
                    <?php $orderJson = json_encode($receiptData($p), $jsonFlags); ?>

                    <div class="rounded-[1.5rem] border border-amber-400/20 bg-amber-500/[.04] overflow-hidden shadow-soft" data-order-card="<?= esc($p['id']) ?>">
                        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 px-5 py-4 border-b border-white/10 bg-white/[.025]">
                            <div class="flex items-start gap-4">
                                <div class="relative mt-1">
                                    <span class="absolute inline-flex h-3 w-3 rounded-full bg-amber-400 opacity-75 animate-ping"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-400"></span>
                                </div>

                                <div>
                                    <p class="text-base font-extrabold text-white">
                                        Meja <?= esc($p['nomor_meja']) ?>
                                        <span class="ml-2 text-xs font-semibold text-slate-500"><?= esc($p['kode_pesanan']) ?></span>
                                    </p>

                                    <p class="text-xs text-slate-400 mt-1">
                                        <i class="fa-solid fa-user mr-1"></i><?= esc($p['nama_pemesan']) ?>
                                        <span class="mx-1.5 text-slate-600">·</span>
                                        <i class="fa-regular fa-clock mr-1"></i><?= date('H:i', strtotime($p['created_at'])) ?>
                                    </p>

                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center text-[11px] px-2.5 py-1 rounded-full border bg-white/[.04] text-slate-300 border-white/10">
                                            <i class="fa-solid fa-wallet mr-1.5 text-sky-300"></i>
                                            <?= esc($formatMetode($p['metode_pembayaran'] ?? '-')) ?>
                                        </span>

                                        <span class="inline-flex items-center text-[11px] px-2.5 py-1 rounded-full border <?= $statusBadgeClass($p['status'] ?? '-') ?>">
                                            <?= esc($statusLabel($p['status'] ?? '-')) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 xl:justify-end">
                                <span class="text-lg font-extrabold text-white">
                                    <?= $formatRupiah($p['total_harga']) ?>
                                </span>

                                <button type="button"
                                        onclick='cetakStruk(<?= $orderJson ?>)'
                                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/[.05] hover:bg-white/[.09] border border-white/10 px-4 py-2.5 text-xs font-bold text-slate-300 hover:text-white transition">
                                    <i class="fa-solid fa-print"></i>
                                    Cetak
                                </button>

                                <?php if (($p['metode_pembayaran'] ?? '') === 'tunai'): ?>
                                    <form action="<?= base_url('kasir/selesai/' . $p['id']) ?>"
                                          method="post"
                                          onsubmit='return selesaiDanCetak(event, this, <?= $orderJson ?>)'>
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-500 hover:bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white transition">
                                            <i class="fa-solid fa-kitchen-set"></i>
                                            Siap / Selesai + Cetak
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500/10 text-amber-300 border border-amber-400/20 px-4 py-2.5 text-xs font-bold">
                                        <i class="fa-solid fa-clock"></i>
                                        Menunggu Bayar
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="px-5 py-4">
                            <ul class="space-y-2">
                                <?php foreach (($p['items'] ?? []) as $item): ?>
                                    <li class="flex items-start justify-between gap-4 text-sm">
                                        <span class="flex items-start gap-2 text-slate-300">
                                            <i class="fa-solid fa-circle-dot text-slate-600 text-[9px] mt-1.5"></i>
                                            <span>
                                                <span class="font-bold text-white"><?= esc($item['nama_menu']) ?></span>
                                                <span class="text-sky-300 font-bold">x<?= esc($item['qty']) ?></span>

                                                <?php if (! empty($item['catatan'])): ?>
                                                    <span class="block text-xs text-amber-300 italic mt-1">
                                                        Catatan: <?= esc($item['catatan']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </span>
                                        </span>

                                        <span class="text-xs text-slate-500 whitespace-nowrap">
                                            <?= $formatRupiah(($item['harga'] ?? 0) * ($item['qty'] ?? 0)) ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <?php if (! empty($p['catatan'])): ?>
                                <p class="mt-4 text-xs text-amber-300 bg-amber-500/10 border border-amber-400/20 rounded-2xl px-4 py-3">
                                    <i class="fa-solid fa-note-sticky mr-1.5"></i>
                                    <?= esc($p['catatan']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="empty-state" class="flex flex-col items-center justify-center py-16 text-center <?= ! empty($pesanan_pending) ? 'hidden' : '' ?>">
                <div class="w-16 h-16 rounded-3xl bg-white/[.04] border border-white/10 flex items-center justify-center mb-4">
                    <i class="fa-solid fa-mug-hot text-slate-500 text-2xl"></i>
                </div>
                <p class="text-sm font-bold text-white">Tidak ada pesanan masuk</p>
                <p class="text-xs text-slate-500 mt-1">Sistem sedang memantau pesanan baru dan pembayaran berhasil.</p>
            </div>
        </div>
    </div>

    <div class="rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft overflow-hidden">
        <div class="p-5 lg:p-6 border-b border-white/10 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-lg font-extrabold text-white">Riwayat Pesanan Hari Ini</h2>
                <p class="text-xs text-slate-500 mt-1">Pesanan yang sudah masuk hari ini. Struk bisa dicetak ulang dari sini.</p>
            </div>

            <span class="inline-flex items-center gap-2 text-xs bg-white/[.04] border border-white/10 text-slate-400 px-3 py-2 rounded-2xl">
                <i class="fa-solid fa-calendar-day text-sky-300"></i>
                <?= date('d M Y') ?>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-white/10 bg-white/[.025]">
                    <tr>
                        <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-[0.16em] px-5 py-4">Waktu</th>
                        <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-[0.16em] px-5 py-4">Kode</th>
                        <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-[0.16em] px-5 py-4">Meja</th>
                        <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-[0.16em] px-5 py-4">Pemesan</th>
                        <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-[0.16em] px-5 py-4">Metode</th>
                        <th class="text-left text-xs font-bold text-slate-500 uppercase tracking-[0.16em] px-5 py-4">Status</th>
                        <th class="text-right text-xs font-bold text-slate-500 uppercase tracking-[0.16em] px-5 py-4">Total</th>
                        <th class="text-right text-xs font-bold text-slate-500 uppercase tracking-[0.16em] px-5 py-4">Aksi</th>
                    </tr>
                </thead>

                <tbody id="riwayat-body" class="divide-y divide-white/10">
                    <?php if (empty($riwayatHariIni)): ?>
                        <tr id="riwayat-empty">
                            <td colspan="8" class="px-5 py-12 text-center text-sm text-slate-500">
                                Belum ada riwayat pesanan hari ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($riwayatHariIni as $r): ?>
                            <?php $riwayatJson = json_encode($receiptData($r), $jsonFlags); ?>

                            <tr id="riwayat-order-<?= esc($r['id']) ?>" class="hover:bg-white/[.025] transition">
                                <td class="px-5 py-4 text-slate-400 whitespace-nowrap">
                                    <?= date('H:i', strtotime($r['created_at'])) ?>
                                </td>

                                <td class="px-5 py-4 font-bold text-white whitespace-nowrap">
                                    <?= esc($r['kode_pesanan']) ?>
                                </td>

                                <td class="px-5 py-4 text-slate-300 whitespace-nowrap">
                                    Meja <?= esc($r['nomor_meja']) ?>
                                </td>

                                <td class="px-5 py-4 text-slate-300 whitespace-nowrap">
                                    <?= esc($r['nama_pemesan']) ?>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center text-[11px] px-2.5 py-1 rounded-full border bg-white/[.04] text-slate-300 border-white/10">
                                        <?= esc($formatMetode($r['metode_pembayaran'] ?? '-')) ?>
                                    </span>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center text-[11px] px-2.5 py-1 rounded-full border <?= $statusBadgeClass($r['status'] ?? '-') ?>">
                                        <?= esc($statusLabel($r['status'] ?? '-')) ?>
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-right font-extrabold text-white whitespace-nowrap">
                                    <?= $formatRupiah($r['total_harga']) ?>
                                </td>

                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <?php if (($r['status'] ?? '') === 'selesai'): ?>
                                        <button type="button"
                                                onclick='cetakStruk(<?= $riwayatJson ?>)'
                                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-sky-500/10 hover:bg-sky-500/20 border border-sky-400/20 px-3 py-2 text-xs font-bold text-sky-300 hover:text-white transition">
                                            <i class="fa-solid fa-print"></i>
                                            Cetak
                                        </button>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-600">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modal-notif" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm transition-opacity">
    <div class="bg-slate-950 border border-white/10 rounded-[2rem] p-6 md:p-8 max-w-sm w-full mx-4 shadow-2xl transform scale-95 transition-transform duration-300" id="modal-box">
        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce bg-sky-500/10 border border-sky-400/20" id="modal-icon-wrap">
            <i class="fa-solid fa-bell text-sky-300 text-3xl" id="modal-icon"></i>
        </div>

        <h3 class="text-xl font-extrabold text-white text-center mb-1" id="modal-title">Pesanan Baru!</h3>

        <p class="text-slate-400 text-center mb-6 text-sm" id="modal-message">
            Orderan masuk dari <span id="notif-meja" class="font-bold text-sky-300 uppercase"></span><br>
            atas nama <span id="notif-nama" class="font-bold text-white"></span>.
        </p>

        <div class="bg-white/[.04] rounded-2xl p-4 text-center border border-white/10 flex flex-col items-center">
            <div class="flex gap-1.5 mb-3 mt-1">
                <span class="w-2.5 h-2.5 bg-sky-400 rounded-full animate-bounce" style="animation-delay: 0s"></span>
                <span class="w-2.5 h-2.5 bg-sky-400 rounded-full animate-bounce" style="animation-delay: 0.15s"></span>
                <span class="w-2.5 h-2.5 bg-sky-400 rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
            </div>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest" id="modal-subtitle">Sedang membacakan notifikasi...</p>
        </div>
    </div>
</div>

<script src="https://code.responsivevoice.org/responsivevoice.js"></script>

<script>
    const STORE_NAME = <?= json_encode($storeNameForReceipt) ?>;

    function updateWaktu() {
        const timeEl = document.getElementById('waktu-sekarang');

        if (timeEl) {
            timeEl.textContent = new Date().toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
    }

    updateWaktu();
    setInterval(updateWaktu, 1000);

    let lastId = <?= (int) ($last_id ?? 0) ?>;
    let lastPaymentTime = '<?= esc($last_payment_time ?? date('Y-m-d H:i:s')) ?>';

    let isCheckingOrder = false;
    let isCheckingPayment = false;

    let csrfHash = '<?= csrf_hash() ?>';
    let antreanTTS = [];
    let sedangBicara = false;

    let soundEnabled = localStorage.getItem('arzana_kasir_sound_enabled') === '1';

    updateSoundButton();

    function updateSoundButton() {
        const btn = document.getElementById('btn-enable-sound');

        if (!btn) return;

        if (soundEnabled) {
            btn.innerHTML = '<i class="fa-solid fa-volume-high"></i> Suara Aktif';
            btn.classList.remove('bg-sky-500', 'hover:bg-sky-600');
            btn.classList.add('bg-emerald-500', 'hover:bg-emerald-600');
        } else {
            btn.innerHTML = '<i class="fa-solid fa-volume-high"></i> Aktifkan Suara';
            btn.classList.remove('bg-emerald-500', 'hover:bg-emerald-600');
            btn.classList.add('bg-sky-500', 'hover:bg-sky-600');
        }
    }

    function aktifkanSuara() {
        soundEnabled = true;
        localStorage.setItem('arzana_kasir_sound_enabled', '1');
        updateSoundButton();
        bunyiDing();

        try {
            if (typeof responsiveVoice !== 'undefined') {
                responsiveVoice.speak('Suara kasir aktif.', 'Indonesian Female', {
                    pitch: 1.05,
                    rate: 0.95,
                    volume: 1
                });
            }
        } catch (err) {
            console.error('Gagal mengaktifkan suara:', err);
        }
    }

    function bunyiDing() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            const ctx = new AudioContext();

            const oscillator = ctx.createOscillator();
            const gainNode = ctx.createGain();

            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(880, ctx.currentTime);
            oscillator.frequency.setValueAtTime(660, ctx.currentTime + 0.15);

            gainNode.gain.setValueAtTime(0.001, ctx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.25, ctx.currentTime + 0.02);
            gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);

            oscillator.connect(gainNode);
            gainNode.connect(ctx.destination);

            oscillator.start();
            oscillator.stop(ctx.currentTime + 0.4);
        } catch (err) {
            console.error('Gagal memutar ding:', err);
        }
    }

    function escapeHTML(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function formatRupiah(number) {
        return String(parseInt(number || 0)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatStatusLabel(status) {
        if (status === 'selesai') return 'Selesai';
        if (status === 'pending') return 'Pending';
        return status ? status.charAt(0).toUpperCase() + status.slice(1) : '-';
    }

    function statusBadgeClass(status) {
        if (status === 'selesai') return 'bg-emerald-500/10 text-emerald-300 border-emerald-400/20';
        if (status === 'pending') return 'bg-amber-500/10 text-amber-300 border-amber-400/20';
        return 'bg-slate-500/10 text-slate-300 border-slate-400/20';
    }

    function formatMetodeLabel(metode) {
        if (metode === 'tunai') return 'Tunai';
        if (metode === 'qris') return 'QRIS / E-Wallet';
        if (metode === 'transfer') return 'Transfer Bank';
        return metode || '-';
    }

    function tutupModal() {
        const modal = document.getElementById('modal-notif');
        const box = document.getElementById('modal-box');

        if (modal && !modal.classList.contains('hidden')) {
            box.classList.add('scale-95');

            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 300);
        }
    }

    function tampilkanModal(type, data) {
        const modal = document.getElementById('modal-notif');
        const box = document.getElementById('modal-box');
        const title = document.getElementById('modal-title');
        const message = document.getElementById('modal-message');
        const subtitle = document.getElementById('modal-subtitle');
        const icon = document.getElementById('modal-icon');
        const iconWrap = document.getElementById('modal-icon-wrap');

        if (!modal || !box || !title || !message || !subtitle || !icon || !iconWrap) return;

        iconWrap.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce border';

        if (type === 'pembayaran') {
            iconWrap.classList.add('bg-emerald-500/10', 'border-emerald-400/20');
            icon.className = 'fa-solid fa-money-bill-wave text-emerald-300 text-3xl';
            title.textContent = 'Pembayaran Berhasil!';
            message.innerHTML = `
                Pembayaran dari <span class="font-bold text-emerald-300 uppercase">Meja ${escapeHTML(data.meja)}</span><br>
                atas nama <span class="font-bold text-white">${escapeHTML(data.nama)}</span> sudah selesai.
            `;
            subtitle.textContent = 'Sedang membacakan pembayaran berhasil...';
        } else {
            iconWrap.classList.add('bg-sky-500/10', 'border-sky-400/20');
            icon.className = 'fa-solid fa-bell text-sky-300 text-3xl';
            title.textContent = 'Pesanan Baru!';
            message.innerHTML = `
                Orderan masuk dari <span class="font-bold text-sky-300 uppercase">Meja ${escapeHTML(data.meja)}</span><br>
                atas nama <span class="font-bold text-white">${escapeHTML(data.nama)}</span>.
            `;
            subtitle.textContent = 'Sedang membacakan pesanan...';
        }

        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            setTimeout(() => {
                box.classList.remove('scale-95');
            }, 50);
        }
    }

    function prosesAntreanSuara() {
        if (antreanTTS.length === 0) {
            sedangBicara = false;
            setTimeout(tutupModal, 1000);
            return;
        }

        sedangBicara = true;

        const current = antreanTTS.shift();

        tampilkanModal(current.type, current);

        let kalimat = '';

        if (current.type === 'pembayaran') {
            kalimat = `Ada pesanan baru masuk dari meja ${current.meja}, Pembayaran berhasil, atas nama ${current.nama}, menggunakan ${current.metode}. Silakan segera diproses.`;
        } else {
            const metodeLower = String(current.metode || '').toLowerCase();

            if (metodeLower.includes('tunai') || metodeLower.includes('cod')) {
                kalimat = `Ada pesanan baru masuk dari meja ${current.meja}, atas nama ${current.nama}, dengan pembayaran tunai. Silakan terima pembayaran dan segera proses.`;
            } else {
                kalimat = `Ada pesanan baru masuk dari meja ${current.meja}, atas nama ${current.nama}, menggunakan ${current.metode}. Pesanan menunggu pembayaran pelanggan.`;
            }
        }

        if (soundEnabled) {
            bunyiDing();
        }

        try {
            if (soundEnabled && typeof responsiveVoice !== 'undefined') {
                responsiveVoice.speak(kalimat, 'Indonesian Female', {
                    pitch: 1.05,
                    rate: 0.95,
                    volume: 1,
                    onend: function() {
                        setTimeout(prosesAntreanSuara, 600);
                    }
                });
            } else {
                setTimeout(prosesAntreanSuara, 2500);
            }
        } catch (err) {
            console.error('Gagal memutar suara:', err);
            setTimeout(prosesAntreanSuara, 2500);
        }
    }

    function normalizeReceiptData(p) {
        let items = Array.isArray(p.items) ? p.items : [];
        let subtotal = parseInt(p.subtotal || 0);

        if (subtotal <= 0 && items.length > 0) {
            subtotal = items.reduce((sum, item) => {
                return sum + (parseInt(item.harga || 0) * parseInt(item.qty || 0));
            }, 0);
        }

        const potongan = parseInt(p.potongan || 0);
        const ppn = parseInt(p.ppn || 0);
        const total = parseInt(p.total_harga || 0);

        if (subtotal <= 0 && total > 0) {
            subtotal = Math.max(0, total - ppn + potongan);
        }

        return {
            id: p.id || 0,
            kode_pesanan: p.kode_pesanan || '-',
            nomor_meja: p.nomor_meja || '-',
            nama_pemesan: p.nama_pemesan || '-',
            metode_label: p.metode_label || formatMetodeLabel(p.metode_pembayaran || '-'),
            status: p.status || 'pending',
            catatan: p.catatan || '',
            waktu: p.waktu || '-',
            subtotal: subtotal,
            potongan: potongan,
            ppn: ppn,
            total_harga: total,
            items: items
        };
    }

    function cetakStruk(rawData) {
        const data = normalizeReceiptData(rawData);

        const itemsHTML = data.items.length > 0
            ? data.items.map(item => {
                const nama = escapeHTML(item.nama_menu || '-');
                const qty = parseInt(item.qty || 0);
                const harga = parseInt(item.harga || 0);
                const sub = parseInt(item.subtotal || (harga * qty));
                const note = item.catatan
                    ? `<div class="note">Catatan: ${escapeHTML(item.catatan)}</div>`
                    : '';

                return `
                    <div class="item">
                        <div>
                            <div class="item-name">${nama}</div>
                            <div class="muted">${qty} x Rp ${formatRupiah(harga)}</div>
                            ${note}
                        </div>
                        <div class="item-price">Rp ${formatRupiah(sub)}</div>
                    </div>
                `;
            }).join('')
            : `<div class="empty-item">Detail item tidak tersedia.</div>`;

        const catatanHTML = data.catatan
            ? `<div class="catatan">Catatan: ${escapeHTML(data.catatan)}</div>`
            : '';

        const html = `
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <title>Struk ${escapeHTML(data.kode_pesanan)}</title>
                <style>
                    * { box-sizing: border-box; }

                    body {
                        margin: 0;
                        background: #fff;
                        color: #000;
                        font-family: Arial, sans-serif;
                        font-size: 12px;
                    }

                    .receipt {
                        width: 58mm;
                        padding: 10px;
                        margin: 0 auto;
                    }

                    .center { text-align: center; }

                    .store {
                        font-size: 15px;
                        font-weight: 800;
                        text-transform: uppercase;
                        margin-bottom: 3px;
                    }

                    .small {
                        font-size: 10px;
                        line-height: 1.4;
                    }

                    .line {
                        border-top: 1px dashed #000;
                        margin: 8px 0;
                    }

                    .row {
                        display: flex;
                        justify-content: space-between;
                        gap: 8px;
                        margin: 3px 0;
                    }

                    .label { color: #333; }

                    .value {
                        font-weight: 700;
                        text-align: right;
                    }

                    .item {
                        display: flex;
                        justify-content: space-between;
                        gap: 8px;
                        margin-bottom: 7px;
                    }

                    .item-name {
                        font-weight: 700;
                        max-width: 34mm;
                    }

                    .item-price {
                        font-weight: 700;
                        white-space: nowrap;
                    }

                    .muted {
                        color: #444;
                        font-size: 10px;
                        margin-top: 2px;
                    }

                    .note {
                        font-size: 10px;
                        margin-top: 2px;
                        color: #111;
                        font-style: italic;
                    }

                    .catatan {
                        border: 1px dashed #000;
                        padding: 5px;
                        margin-top: 7px;
                        font-size: 10px;
                    }

                    .total-row {
                        font-size: 14px;
                        font-weight: 800;
                    }

                    .empty-item {
                        font-size: 10px;
                        text-align: center;
                        color: #444;
                        padding: 6px 0;
                    }

                    .thanks {
                        margin-top: 10px;
                        text-align: center;
                        font-size: 10px;
                        line-height: 1.4;
                    }

                    @media print {
                        @page {
                            size: 58mm auto;
                            margin: 0;
                        }

                        body { width: 58mm; }
                        .receipt { width: 58mm; }
                    }
                </style>
            </head>
            <body>
                <div class="receipt">
                    <div class="center">
                        <div class="store">${escapeHTML(STORE_NAME)}</div>
                        <div class="small">Struk Pesanan</div>
                    </div>

                    <div class="line"></div>

                    <div class="row">
                        <span class="label">Kode</span>
                        <span class="value">${escapeHTML(data.kode_pesanan)}</span>
                    </div>
                    <div class="row">
                        <span class="label">Meja</span>
                        <span class="value">${escapeHTML(data.nomor_meja)}</span>
                    </div>
                    <div class="row">
                        <span class="label">Nama</span>
                        <span class="value">${escapeHTML(data.nama_pemesan)}</span>
                    </div>
                    <div class="row">
                        <span class="label">Metode</span>
                        <span class="value">${escapeHTML(data.metode_label)}</span>
                    </div>
                    <div class="row">
                        <span class="label">Waktu</span>
                        <span class="value">${escapeHTML(data.waktu)}</span>
                    </div>

                    <div class="line"></div>

                    ${itemsHTML}

                    ${catatanHTML}

                    <div class="line"></div>

                    <div class="row">
                        <span>Subtotal</span>
                        <span>Rp ${formatRupiah(data.subtotal)}</span>
                    </div>

                    ${data.potongan > 0 ? `
                        <div class="row">
                            <span>Promo</span>
                            <span>-Rp ${formatRupiah(data.potongan)}</span>
                        </div>
                    ` : ''}

                    <div class="row">
                        <span>PPN 10%</span>
                        <span>Rp ${formatRupiah(data.ppn)}</span>
                    </div>

                    <div class="line"></div>

                    <div class="row total-row">
                        <span>Total</span>
                        <span>Rp ${formatRupiah(data.total_harga)}</span>
                    </div>

                    <div class="line"></div>

                    <div class="thanks">
                        Terima kasih.<br>
                        Pesanan Anda telah selesai diproses.<br>
                        Struk ini menjadi bukti transaksi.
                    </div>
                </div>

                <script>
                    window.onload = function() {
                        setTimeout(function() {
                            window.print();
                            setTimeout(function() {
                                window.close();
                            }, 500);
                        }, 250);
                    };
                <\/script>
            </body>
            </html>
        `;

        const printWindow = window.open('', '_blank', 'width=380,height=650');

        if (!printWindow) {
            alert('Popup print diblokir browser. Izinkan popup untuk mencetak struk.');
            return;
        }

        printWindow.document.open();
        printWindow.document.write(html);
        printWindow.document.close();
    }

    function selesaiDanCetak(event, form, data) {
        event.preventDefault();

        cetakStruk(data);

        setTimeout(() => {
            form.submit();
        }, 700);

        return false;
    }

    function renderOrderHTML(p) {
        let itemsHTML = '';

        if (Array.isArray(p.items)) {
            p.items.forEach(item => {
                const note = item.catatan
                    ? `<span class="block text-xs text-amber-300 italic mt-1">Catatan: ${escapeHTML(item.catatan)}</span>`
                    : '';

                const subtotal = parseInt(item.harga || 0) * parseInt(item.qty || 0);

                itemsHTML += `
                    <li class="flex items-start justify-between gap-4 text-sm">
                        <span class="flex items-start gap-2 text-slate-300">
                            <i class="fa-solid fa-circle-dot text-slate-600 text-[9px] mt-1.5"></i>
                            <span>
                                <span class="font-bold text-white">${escapeHTML(item.nama_menu)}</span>
                                <span class="text-sky-300 font-bold">x${escapeHTML(item.qty)}</span>
                                ${note}
                            </span>
                        </span>
                        <span class="text-xs text-slate-500 whitespace-nowrap">Rp ${formatRupiah(subtotal)}</span>
                    </li>
                `;
            });
        }

        const catUmum = p.catatan
            ? `
                <p class="mt-4 text-xs text-amber-300 bg-amber-500/10 border border-amber-400/20 rounded-2xl px-4 py-3">
                    <i class="fa-solid fa-note-sticky mr-1.5"></i>${escapeHTML(p.catatan)}
                </p>
            `
            : '';

        const metode = p.metode_pembayaran || '';
        const metodeLabel = p.metode_label || formatMetodeLabel(metode);
        const status = p.status || p.status_pembayaran || 'pending';
        const receiptPayload = JSON.stringify(p).replaceAll("'", '&#039;');

        const actionHTML = metode === 'tunai'
            ? `
                <form action="<?= base_url('kasir/selesai/') ?>${escapeHTML(p.id)}" method="post" onsubmit='return selesaiDanCetak(event, this, ${receiptPayload})'>
                    <input type="hidden" name="<?= csrf_token() ?>" value="${csrfHash}">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-500 hover:bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white transition">
                        <i class="fa-solid fa-kitchen-set"></i> Siap / Selesai + Cetak
                    </button>
                </form>
            `
            : `
                <span class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500/10 text-amber-300 border border-amber-400/20 px-4 py-2.5 text-xs font-bold">
                    <i class="fa-solid fa-clock"></i> Menunggu Bayar
                </span>
            `;

        return `
            <div class="rounded-[1.5rem] border border-sky-400/20 bg-sky-500/[.05] overflow-hidden shadow-soft" data-order-card="${escapeHTML(p.id)}">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 px-5 py-4 border-b border-white/10 bg-white/[.025]">
                    <div class="flex items-start gap-4">
                        <div class="relative mt-1">
                            <span class="absolute inline-flex h-3 w-3 rounded-full bg-sky-400 opacity-75 animate-ping"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-sky-400"></span>
                        </div>

                        <div>
                            <p class="text-base font-extrabold text-white">
                                Meja ${escapeHTML(p.nomor_meja)}
                                <span class="ml-2 text-xs font-semibold text-slate-500">${escapeHTML(p.kode_pesanan)}</span>
                            </p>

                            <p class="text-xs text-slate-400 mt-1">
                                <i class="fa-solid fa-user mr-1"></i>${escapeHTML(p.nama_pemesan)}
                                <span class="mx-1.5 text-slate-600">·</span>
                                <i class="fa-regular fa-clock mr-1"></i>${escapeHTML(p.waktu)}
                            </p>

                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center text-[11px] px-2.5 py-1 rounded-full border bg-white/[.04] text-slate-300 border-white/10">
                                    <i class="fa-solid fa-wallet mr-1.5 text-sky-300"></i>${escapeHTML(metodeLabel)}
                                </span>
                                <span class="inline-flex items-center text-[11px] px-2.5 py-1 rounded-full border ${statusBadgeClass(status)}">
                                    ${escapeHTML(formatStatusLabel(status))}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 xl:justify-end">
                        <span class="text-lg font-extrabold text-white">Rp ${escapeHTML(p.total_format || formatRupiah(p.total_harga))}</span>

                        <button type="button"
                                onclick='cetakStruk(${receiptPayload})'
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/[.05] hover:bg-white/[.09] border border-white/10 px-4 py-2.5 text-xs font-bold text-slate-300 hover:text-white transition">
                            <i class="fa-solid fa-print"></i> Cetak
                        </button>

                        ${actionHTML}
                    </div>
                </div>

                <div class="px-5 py-4">
                    <ul class="space-y-2">${itemsHTML}</ul>
                    ${catUmum}
                </div>
            </div>
        `;
    }

    function renderRiwayatHTML(p) {
        const status = p.status || p.status_pembayaran || 'pending';
        const metode = p.metode_pembayaran || '';
        const metodeLabel = p.metode_label || formatMetodeLabel(metode);
        const waktu = p.created_waktu || p.waktu || '-';
        const totalFormat = p.total_format || formatRupiah(p.total_harga || 0);
        const receiptPayload = JSON.stringify(p).replaceAll("'", '&#039;');

        const printButton = status === 'selesai'
            ? `
                <button type="button"
                        onclick='cetakStruk(${receiptPayload})'
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-sky-500/10 hover:bg-sky-500/20 border border-sky-400/20 px-3 py-2 text-xs font-bold text-sky-300 hover:text-white transition">
                    <i class="fa-solid fa-print"></i> Cetak
                </button>
            `
            : `<span class="text-xs text-slate-600">-</span>`;

        return `
            <tr id="riwayat-order-${escapeHTML(p.id)}" class="hover:bg-white/[.025] bg-emerald-500/[.025] transition">
                <td class="px-5 py-4 text-slate-400 whitespace-nowrap">${escapeHTML(waktu)}</td>
                <td class="px-5 py-4 font-bold text-white whitespace-nowrap">${escapeHTML(p.kode_pesanan)}</td>
                <td class="px-5 py-4 text-slate-300 whitespace-nowrap">Meja ${escapeHTML(p.nomor_meja)}</td>
                <td class="px-5 py-4 text-slate-300 whitespace-nowrap">${escapeHTML(p.nama_pemesan)}</td>
                <td class="px-5 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center text-[11px] px-2.5 py-1 rounded-full border bg-white/[.04] text-slate-300 border-white/10">
                        ${escapeHTML(metodeLabel)}
                    </span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center text-[11px] px-2.5 py-1 rounded-full border ${statusBadgeClass(status)}">
                        ${escapeHTML(formatStatusLabel(status))}
                    </span>
                </td>
                <td class="px-5 py-4 text-right font-extrabold text-white whitespace-nowrap">
                    Rp ${escapeHTML(totalFormat)}
                </td>
                <td class="px-5 py-4 text-right whitespace-nowrap">
                    ${printButton}
                </td>
            </tr>
        `;
    }

    function updateEmptyState() {
        const container = document.getElementById('order-container');
        const emptyState = document.getElementById('empty-state');
        const countPending = document.getElementById('count-pending');

        const total = countPending ? parseInt(countPending.textContent || '0') : 0;

        if (total <= 0) {
            if (container) container.classList.add('hidden');
            if (emptyState) emptyState.classList.remove('hidden');
        } else {
            if (container) container.classList.remove('hidden');
            if (emptyState) emptyState.classList.add('hidden');
        }
    }

    function tambahRiwayat(p) {
        const tbody = document.getElementById('riwayat-body');
        const empty = document.getElementById('riwayat-empty');

        if (!tbody) return false;

        if (empty) empty.remove();

        const existing = document.getElementById('riwayat-order-' + p.id);

        if (existing) {
            existing.remove();
        }

        tbody.insertAdjacentHTML('afterbegin', renderRiwayatHTML(p));

        return !existing;
    }

    function tanganiPesananBaru(pesananList) {
        try {
            const container = document.getElementById('order-container');
            const emptyState = document.getElementById('empty-state');

            if (emptyState) emptyState.classList.add('hidden');
            if (container) container.classList.remove('hidden');

            pesananList.forEach(p => {
                if (container) {
                    container.insertAdjacentHTML('afterbegin', renderOrderHTML(p));
                }

                const counter = document.getElementById('count-pending');

                if (counter) {
                    counter.textContent = parseInt(counter.textContent || '0') + 1;
                }

                tambahRiwayat(p);

                antreanTTS.push({
                    type: 'pesanan',
                    meja: p.nomor_meja,
                    nama: p.nama_pemesan,
                    metode: p.metode_label || formatMetodeLabel(p.metode_pembayaran || ''),
                    total: p.total_harga || 0
                });
            });

            updateEmptyState();

            if (!sedangBicara) {
                prosesAntreanSuara();
            }

        } catch (err) {
            console.error('Terjadi kesalahan tampilan:', err);
            setTimeout(tutupModal, 3000);
        }
    }

    function tanganiPembayaranBerhasil(pesananList) {
        try {
            pesananList.forEach(p => {
                const card = document.querySelector(`[data-order-card="${p.id}"]`);

                if (card) {
                    card.remove();

                    const counter = document.getElementById('count-pending');

                    if (counter) {
                        counter.textContent = Math.max(0, parseInt(counter.textContent || '0') - 1);
                    }
                }

                const isNewHistory = tambahRiwayat(p);

                if (isNewHistory) {
                    const selesaiCounter = document.getElementById('count-selesai');

                    if (selesaiCounter) {
                        selesaiCounter.textContent = parseInt(selesaiCounter.textContent || '0') + 1;
                    }

                    const pendapatanEl = document.getElementById('pendapatan-hari-ini');

                    if (pendapatanEl) {
                        const oldAmount = parseInt(pendapatanEl.dataset.amount || '0');
                        const addAmount = parseInt(p.total_harga || '0');
                        const newAmount = oldAmount + addAmount;

                        pendapatanEl.dataset.amount = newAmount;
                        pendapatanEl.textContent = 'Rp ' + formatRupiah(newAmount);
                    }
                }

                antreanTTS.push({
                    type: 'pembayaran',
                    meja: p.nomor_meja,
                    nama: p.nama_pemesan,
                    metode: p.metode_label || formatMetodeLabel(p.metode_pembayaran || ''),
                    total: p.total_harga || 0
                });
            });

            updateEmptyState();

            if (!sedangBicara) {
                prosesAntreanSuara();
            }

        } catch (err) {
            console.error('Terjadi kesalahan saat menangani pembayaran berhasil:', err);
            setTimeout(tutupModal, 3000);
        }
    }

    function cekPesananBaru() {
        if (isCheckingOrder) return;
        isCheckingOrder = true;

        const url = '<?= base_url('kasir/cek_baru') ?>/' + lastId + '?t=' + Date.now();

        fetch(url, {
            method: 'GET',
            cache: 'no-store',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Gagal terhubung ke server');
            return res.json();
        })
        .then(data => {
            if (data.ada && data.data && data.data.length > 0) {
                lastId = data.last_id;
                tanganiPesananBaru(data.data);
            }

            isCheckingOrder = false;
        })
        .catch(err => {
            console.error('Cek pesanan baru gagal:', err);
            isCheckingOrder = false;
        });
    }

    function cekPembayaranBerhasil() {
        if (isCheckingPayment) return;
        isCheckingPayment = true;

        const url = '<?= base_url('kasir/cek_pembayaran_berhasil') ?>'
            + '?last_time=' + encodeURIComponent(lastPaymentTime)
            + '&t=' + Date.now();

        fetch(url, {
            method: 'GET',
            cache: 'no-store',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Gagal cek pembayaran');
            return res.json();
        })
        .then(data => {
            if (data.last_time) {
                lastPaymentTime = data.last_time;
            }

            if (data.ada && data.data && data.data.length > 0) {
                tanganiPembayaranBerhasil(data.data);
            }

            isCheckingPayment = false;
        })
        .catch(err => {
            console.error('Cek pembayaran berhasil gagal:', err);
            isCheckingPayment = false;
        });
    }

    setInterval(cekPesananBaru, 3000);
    setInterval(cekPembayaranBerhasil, 3000);
</script>

<?= $this->endSection() ?>