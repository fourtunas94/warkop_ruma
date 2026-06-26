<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
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
        'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        default   => 'bg-slate-50 text-slate-700 border-slate-200',
    };
};

$statusLabel = static function ($status) {
    return match ($status) {
        'selesai' => 'Selesai',
        'pending' => 'Pending',
        default   => ucfirst((string) $status),
    };
};

$riwayatHariIni = $riwayat_hari_ini ?? [];
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold text-slate-900">Antrean Pesanan</h1>
        <p class="text-sm text-slate-500 mt-0.5">Pesanan masuk yang perlu diproses.</p>
    </div>

    <div class="flex items-center gap-2">
        <button type="button" id="btn-enable-sound" onclick="aktifkanSuara()" class="text-xs bg-sky-600 hover:bg-sky-700 text-white px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
            <i class="fa-solid fa-volume-high"></i>
            Aktifkan Suara
        </button>

        <span class="text-xs text-slate-500 bg-white border border-slate-200 px-3 py-1.5 rounded-lg">
            <i class="fa-regular fa-clock mr-1.5 text-slate-400"></i>
            <span id="waktu-sekarang" class="font-medium text-slate-700"></span>
        </span>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3 rounded-lg">
    <i class="fa-solid fa-circle-check"></i> <?= session()->getFlashdata('success') ?>
</div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<div class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
    <i class="fa-solid fa-circle-exclamation"></i> <?= session()->getFlashdata('error') ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-hourglass-half text-amber-500"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Menunggu</p>
            <p class="text-xl font-semibold text-slate-900" id="count-pending"><?= $total_pending ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-circle-check text-emerald-500"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Selesai Hari Ini</p>
            <p class="text-xl font-semibold text-slate-900" id="count-selesai"><?= $total_selesai ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-sky-50 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-sack-dollar text-sky-500"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Pendapatan (Hari Ini)</p>
            <p class="text-xl font-semibold text-slate-900" id="pendapatan-hari-ini" data-amount="<?= (int) $pendapatan ?>">
                Rp <?= number_format($pendapatan, 0, ',', '.') ?>
            </p>
        </div>
    </div>
</div>

<div class="mb-4 flex items-center justify-between">
    <div>
        <h2 class="text-base font-semibold text-slate-900">Pesanan Menunggu</h2>
        <p class="text-xs text-slate-500">Pesanan pending yang masih perlu diproses kasir.</p>
    </div>
</div>

<div id="order-container" class="space-y-3 <?= empty($pesanan_pending) ? 'hidden' : '' ?>">
    <?php foreach ($pesanan_pending as $p): ?>
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden" data-order-card="<?= esc($p['id']) ?>">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-5 py-3.5 bg-slate-50 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                <div>
                    <p class="text-sm font-semibold text-slate-800">
                        Meja <?= esc($p['nomor_meja']) ?>
                        <span class="ml-1.5 text-xs font-normal text-slate-400"><?= esc($p['kode_pesanan']) ?></span>
                    </p>
                    <p class="text-xs text-slate-500">
                        <i class="fa-solid fa-user mr-1"></i><?= esc($p['nama_pemesan']) ?>
                        <span class="mx-1.5 text-slate-300">·</span>
                        <i class="fa-regular fa-clock mr-1"></i><?= date('H:i', strtotime($p['created_at'])) ?>
                    </p>
                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                        <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded-full border bg-slate-50 text-slate-600 border-slate-200">
                            <i class="fa-solid fa-wallet mr-1"></i>
                            <?= esc($formatMetode($p['metode_pembayaran'] ?? '-')) ?>
                        </span>
                        <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded-full border <?= $statusBadgeClass($p['status'] ?? '-') ?>">
                            <?= esc($statusLabel($p['status'] ?? '-')) ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 justify-end">
                <span class="text-sm font-bold text-slate-800">Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></span>

                <?php if (($p['metode_pembayaran'] ?? '') === 'tunai'): ?>
                <form action="<?= base_url('kasir/selesai/' . $p['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="text-xs bg-emerald-500 hover:bg-emerald-600 text-white font-medium px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-check text-xs"></i> Selesai
                    </button>
                </form>
                <?php else: ?>
                <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 font-medium px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                    <i class="fa-solid fa-clock text-xs"></i> Menunggu Bayar
                </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="px-5 py-3">
            <ul class="space-y-1">
                <?php foreach ($p['items'] as $item): ?>
                <li class="flex items-center justify-between text-xs text-slate-600">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-dot text-slate-300 text-xs"></i>
                        <?= esc($item['nama_menu']) ?>
                        <span class="text-slate-400">x<?= $item['qty'] ?></span>
                    </span>

                    <?php if (!empty($item['catatan'])): ?>
                    <span class="text-amber-500 italic truncate max-w-[150px]"><?= esc($item['catatan']) ?></span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>

            <?php if (!empty($p['catatan'])): ?>
            <p class="mt-2 text-xs text-slate-500 bg-amber-50 border border-amber-100 rounded-lg px-3 py-1.5">
                <i class="fa-solid fa-note-sticky text-amber-400 mr-1.5"></i><?= esc($p['catatan']) ?>
            </p>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div id="empty-state" class="flex flex-col items-center justify-center py-16 text-center <?= !empty($pesanan_pending) ? 'hidden' : '' ?>">
    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-3">
        <i class="fa-solid fa-mug-hot text-slate-400 text-xl"></i>
    </div>
    <p class="text-sm font-medium text-slate-600">Tidak ada pesanan masuk</p>
    <p class="text-xs text-slate-400 mt-1">Sistem memantau pesanan baru dan pembayaran berhasil...</p>
</div>

<div class="mt-8">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Riwayat Pesanan Hari Ini</h2>
            <p class="text-xs text-slate-500">Urutan terbaru berada di atas, lengkap dengan status pembayaran.</p>
        </div>
        <span class="text-xs bg-white border border-slate-200 text-slate-500 px-3 py-1.5 rounded-lg">
            <i class="fa-solid fa-calendar-day mr-1.5"></i><?= date('d M Y') ?>
        </span>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Waktu</th>
                        <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Kode</th>
                        <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Meja</th>
                        <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Pemesan</th>
                        <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Metode</th>
                        <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Status</th>
                        <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Total</th>
                    </tr>
                </thead>
                <tbody id="riwayat-body" class="divide-y divide-slate-100">
                    <?php if (empty($riwayatHariIni)): ?>
                    <tr id="riwayat-empty">
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">
                            Belum ada riwayat pesanan hari ini.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($riwayatHariIni as $r): ?>
                        <tr id="riwayat-order-<?= esc($r['id']) ?>" class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                <?= date('H:i', strtotime($r['created_at'])) ?>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-800 whitespace-nowrap">
                                <?= esc($r['kode_pesanan']) ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                Meja <?= esc($r['nomor_meja']) ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                <?= esc($r['nama_pemesan']) ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded-full border bg-slate-50 text-slate-600 border-slate-200">
                                    <?= esc($formatMetode($r['metode_pembayaran'] ?? '-')) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded-full border <?= $statusBadgeClass($r['status'] ?? '-') ?>">
                                    <?= esc($statusLabel($r['status'] ?? '-')) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-800 whitespace-nowrap">
                                Rp <?= number_format($r['total_harga'], 0, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modal-notif" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-2xl p-6 md:p-8 max-w-sm w-full mx-4 shadow-2xl transform scale-95 transition-transform duration-300" id="modal-box">
        <div class="w-16 h-16 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce" id="modal-icon-wrap">
            <i class="fa-solid fa-bell text-sky-500 text-3xl" id="modal-icon"></i>
        </div>

        <h3 class="text-xl font-bold text-slate-800 text-center mb-1" id="modal-title">Pesanan Baru!</h3>

        <p class="text-slate-600 text-center mb-6" id="modal-message">
            Orderan masuk dari <span id="notif-meja" class="font-bold text-sky-600 uppercase"></span><br>
            atas nama <span id="notif-nama" class="font-bold"></span>.
        </p>

        <div class="bg-slate-50 rounded-xl p-4 text-center border border-slate-100 flex flex-col items-center">
            <div class="flex gap-1.5 mb-3 mt-1">
                <span class="w-2.5 h-2.5 bg-sky-400 rounded-full animate-bounce" style="animation-delay: 0s"></span>
                <span class="w-2.5 h-2.5 bg-sky-400 rounded-full animate-bounce" style="animation-delay: 0.15s"></span>
                <span class="w-2.5 h-2.5 bg-sky-400 rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
            </div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-widest" id="modal-subtitle">Sedang membacakan notifikasi...</p>
        </div>
    </div>
</div>

<script src="https://code.responsivevoice.org/responsivevoice.js"></script>

<script>
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
            btn.classList.remove('bg-sky-600', 'hover:bg-sky-700');
            btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
        } else {
            btn.innerHTML = '<i class="fa-solid fa-volume-high"></i> Aktifkan Suara';
            btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
            btn.classList.add('bg-sky-600', 'hover:bg-sky-700');
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
        if (status === 'selesai') return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        if (status === 'pending') return 'bg-amber-50 text-amber-700 border-amber-200';
        return 'bg-slate-50 text-slate-700 border-slate-200';
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

        iconWrap.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce';

        if (type === 'pembayaran') {
            iconWrap.classList.add('bg-emerald-100');
            icon.className = 'fa-solid fa-money-bill-wave text-emerald-500 text-3xl';
            title.textContent = 'Pembayaran Berhasil!';
            message.innerHTML = `
                Pembayaran dari <span class="font-bold text-emerald-600 uppercase">Meja ${escapeHTML(data.meja)}</span><br>
                atas nama <span class="font-bold">${escapeHTML(data.nama)}</span> sudah selesai.
            `;
            subtitle.textContent = 'Sedang membacakan pembayaran berhasil...';
        } else {
            iconWrap.classList.add('bg-sky-100');
            icon.className = 'fa-solid fa-bell text-sky-500 text-3xl';
            title.textContent = 'Pesanan Baru!';
            message.innerHTML = `
                Orderan masuk dari <span class="font-bold text-sky-600 uppercase">Meja ${escapeHTML(data.meja)}</span><br>
                atas nama <span class="font-bold">${escapeHTML(data.nama)}</span>.
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
                // kalimat = `Ting tong. Ada pesanan baru masuk dari meja ${current.meja}, atas nama ${current.nama}, menggunakan ${current.metode}. Pesanan menunggu pembayaran pelanggan. Jangan diproses sebelum pembayaran berhasil.`;
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

    function renderOrderHTML(p) {
        let itemsHTML = '';

        if (Array.isArray(p.items)) {
            p.items.forEach(item => {
                const note = item.catatan
                    ? `<span class="text-amber-500 italic truncate max-w-[150px]">${escapeHTML(item.catatan)}</span>`
                    : '';

                itemsHTML += `
                    <li class="flex items-center justify-between text-xs text-slate-600">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-dot text-slate-300 text-xs"></i>
                            ${escapeHTML(item.nama_menu)}
                            <span class="text-slate-400">x${escapeHTML(item.qty)}</span>
                        </span>
                        ${note}
                    </li>
                `;
            });
        }

        const catUmum = p.catatan
            ? `
                <p class="mt-2 text-xs text-slate-500 bg-amber-50 border border-amber-100 rounded-lg px-3 py-1.5">
                    <i class="fa-solid fa-note-sticky text-amber-400 mr-1.5"></i>${escapeHTML(p.catatan)}
                </p>
            `
            : '';

        const metode = p.metode_pembayaran || '';
        const metodeLabel = p.metode_label || formatMetodeLabel(metode);
        const status = p.status || p.status_pembayaran || 'pending';

        const actionHTML = metode === 'tunai'
            ? `
                <form action="<?= base_url('kasir/selesai/') ?>${escapeHTML(p.id)}" method="post">
                    <input type="hidden" name="<?= csrf_token() ?>" value="${csrfHash}">
                    <button type="submit" class="text-xs bg-emerald-500 hover:bg-emerald-600 text-white font-medium px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-check text-xs"></i> Selesai
                    </button>
                </form>
            `
            : `
                <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 font-medium px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                    <i class="fa-solid fa-clock text-xs"></i> Menunggu Bayar
                </span>
            `;

        return `
            <div class="bg-white rounded-xl border border-sky-300 shadow-md shadow-sky-100 overflow-hidden transform transition-all" data-order-card="${escapeHTML(p.id)}">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-5 py-3.5 bg-sky-50 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-sky-500 animate-ping"></span>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">
                                Meja ${escapeHTML(p.nomor_meja)}
                                <span class="ml-1.5 text-xs font-normal text-slate-400">${escapeHTML(p.kode_pesanan)}</span>
                            </p>
                            <p class="text-xs text-slate-500">
                                <i class="fa-solid fa-user mr-1"></i>${escapeHTML(p.nama_pemesan)}
                                <span class="mx-1.5 text-slate-300">·</span>
                                <i class="fa-regular fa-clock mr-1"></i>${escapeHTML(p.waktu)}
                            </p>
                            <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded-full border bg-slate-50 text-slate-600 border-slate-200">
                                    <i class="fa-solid fa-wallet mr-1"></i>${escapeHTML(metodeLabel)}
                                </span>
                                <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded-full border ${statusBadgeClass(status)}">
                                    ${escapeHTML(formatStatusLabel(status))}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 justify-end">
                        <span class="text-sm font-bold text-slate-800">Rp ${escapeHTML(p.total_format)}</span>
                        ${actionHTML}
                    </div>
                </div>

                <div class="px-5 py-3">
                    <ul class="space-y-1">${itemsHTML}</ul>
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

        return `
            <tr id="riwayat-order-${escapeHTML(p.id)}" class="hover:bg-slate-50 bg-emerald-50/40">
                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">${escapeHTML(waktu)}</td>
                <td class="px-4 py-3 font-medium text-slate-800 whitespace-nowrap">${escapeHTML(p.kode_pesanan)}</td>
                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">Meja ${escapeHTML(p.nomor_meja)}</td>
                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">${escapeHTML(p.nama_pemesan)}</td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded-full border bg-slate-50 text-slate-600 border-slate-200">
                        ${escapeHTML(metodeLabel)}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded-full border ${statusBadgeClass(status)}">
                        ${escapeHTML(formatStatusLabel(status))}
                    </span>
                </td>
                <td class="px-4 py-3 text-right font-semibold text-slate-800 whitespace-nowrap">
                    Rp ${escapeHTML(totalFormat)}
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