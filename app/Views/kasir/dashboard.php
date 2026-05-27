<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-semibold text-slate-900">Antrean Pesanan</h1>
        <p class="text-sm text-slate-500 mt-0.5">Pesanan masuk yang perlu diproses.</p>
    </div>
    <span class="text-xs text-slate-500 bg-white border border-slate-200 px-3 py-1.5 rounded-lg">
        <i class="fa-regular fa-clock mr-1.5 text-slate-400"></i>
        <span id="waktu-sekarang" class="font-medium text-slate-700"></span>
    </span>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3 rounded-lg">
    <i class="fa-solid fa-circle-check"></i> <?= session()->getFlashdata('success') ?>
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
            <p class="text-xl font-semibold text-slate-900"><?= $total_selesai ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-sky-50 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-sack-dollar text-sky-500"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Pendapatan (Hari Ini)</p>
            <p class="text-xl font-semibold text-slate-900">Rp <?= number_format($pendapatan, 0, ',', '.') ?></p>
        </div>
    </div>
</div>

<div id="order-container" class="space-y-3 <?= empty($pesanan_pending) ? 'hidden' : '' ?>">
    <?php foreach ($pesanan_pending as $p): ?>
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 bg-slate-50 border-b border-slate-100">
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
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-800">Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></span>
                <form action="<?= base_url('kasir/selesai/' . $p['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="text-xs bg-emerald-500 hover:bg-emerald-600 text-white font-medium px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-check text-xs"></i> Selesai
                    </button>
                </form>
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
                    <?php if ($item['catatan']): ?>
                    <span class="text-amber-500 italic truncate max-w-[150px]"><?= esc($item['catatan']) ?></span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($p['catatan']): ?>
            <p class="mt-2 text-xs text-slate-500 bg-amber-50 border border-amber-100 rounded-lg px-3 py-1.5">
                <i class="fa-solid fa-note-sticky text-amber-400 mr-1.5"></i><?= esc($p['catatan']) ?>
            </p>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div id="empty-state" class="flex flex-col items-center justify-center py-20 text-center <?= !empty($pesanan_pending) ? 'hidden' : '' ?>">
    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-3">
        <i class="fa-solid fa-mug-hot text-slate-400 text-xl"></i>
    </div>
    <p class="text-sm font-medium text-slate-600">Tidak ada pesanan masuk</p>
    <p class="text-xs text-slate-400 mt-1">Sistem memantau pesanan baru...</p>
</div>

<div id="modal-notif" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-2xl p-6 md:p-8 max-w-sm w-full mx-4 shadow-2xl transform scale-95 transition-transform duration-300" id="modal-box">
        <div class="w-16 h-16 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
            <i class="fa-solid fa-bell text-sky-500 text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-800 text-center mb-1">Pesanan Baru!</h3>
        <p class="text-slate-600 text-center mb-6">Orderan masuk dari <span id="notif-meja" class="font-bold text-sky-600 uppercase"></span><br>atas nama <span id="notif-nama" class="font-bold"></span>.</p>
        
        <div class="bg-slate-50 rounded-xl p-4 text-center border border-slate-100 flex flex-col items-center">
            <div class="flex gap-1.5 mb-3 mt-1">
                <span class="w-2.5 h-2.5 bg-sky-400 rounded-full animate-bounce" style="animation-delay: 0s"></span>
                <span class="w-2.5 h-2.5 bg-sky-400 rounded-full animate-bounce" style="animation-delay: 0.15s"></span>
                <span class="w-2.5 h-2.5 bg-sky-400 rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
            </div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-widest">Sedang membacakan pesanan...</p>
        </div>
    </div>
</div>

<script src="https://code.responsivevoice.org/responsivevoice.js"></script>

<script>
    function updateWaktu() {
        const timeEl = document.getElementById('waktu-sekarang');
        if (timeEl) {
            timeEl.textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    }
    updateWaktu();
    setInterval(updateWaktu, 1000);
    let lastId = <?= $last_id ?? 0 ?>;
    let isChecking = false;
    let csrfHash = '<?= csrf_hash() ?>'; 
    let antreanTTS = [];
    let sedangBicara = false;
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

    function prosesAntreanSuara() {
        if (antreanTTS.length === 0) {
            sedangBicara = false;
            setTimeout(tutupModal, 1000);
            return;
        }

        sedangBicara = true;
        
        const currentOrder = antreanTTS.shift();

        const modal = document.getElementById('modal-notif');
        if (modal && modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => document.getElementById('modal-box').classList.remove('scale-95'), 50);
        }
        document.getElementById('notif-meja').textContent = currentOrder.meja;
        document.getElementById('notif-nama').textContent = currentOrder.nama;

        const kalimat = `Ting tong. Ada pesanan baru masuk dari meja ${currentOrder.meja}, atas nama ${currentOrder.nama}. Silakan segera diproses.`;

        try {
            if (typeof responsiveVoice !== 'undefined') {
                responsiveVoice.speak(kalimat, "Indonesian Female", {
                    pitch: 1.05, 
                    rate: 0.95,
                    volume: 1,
                    onend: function() {
                        setTimeout(prosesAntreanSuara, 600);
                    }
                });
            } else {
                setTimeout(prosesAntreanSuara, 2000);
            }
        } catch (err) {
            console.error("Gagal memutar suara:", err);
            setTimeout(prosesAntreanSuara, 2000);
        }
    }

    function renderOrderHTML(p) {
        let itemsHTML = '';
        p.items.forEach(item => {
            let note = item.catatan ? `<span class="text-amber-500 italic truncate max-w-[150px]">${item.catatan}</span>` : '';
            itemsHTML += `
            <li class="flex items-center justify-between text-xs text-slate-600">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-dot text-slate-300 text-xs"></i>
                    ${item.nama_menu} <span class="text-slate-400">x${item.qty}</span>
                </span>
                ${note}
            </li>`;
        });

        let catUmum = p.catatan ? `
        <p class="mt-2 text-xs text-slate-500 bg-amber-50 border border-amber-100 rounded-lg px-3 py-1.5">
            <i class="fa-solid fa-note-sticky text-amber-400 mr-1.5"></i>${p.catatan}
        </p>` : '';

        return `
        <div class="bg-white rounded-xl border border-sky-300 shadow-md shadow-sky-100 overflow-hidden transform transition-all animate-slide-down">
            <div class="flex items-center justify-between px-5 py-3.5 bg-sky-50 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-sky-500 animate-ping"></span>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">
                            Meja ${p.nomor_meja}
                            <span class="ml-1.5 text-xs font-normal text-slate-400">${p.kode_pesanan}</span>
                        </p>
                        <p class="text-xs text-slate-500">
                            <i class="fa-solid fa-user mr-1"></i>${p.nama_pemesan}
                            <span class="mx-1.5 text-slate-300">·</span>
                            <i class="fa-regular fa-clock mr-1"></i>${p.waktu}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-800">Rp ${p.total_format}</span>
                    <form action="<?= base_url('kasir/selesai/') ?>${p.id}" method="post">
                        <input type="hidden" name="<?= csrf_token() ?>" value="${csrfHash}">
                        <button type="submit" class="text-xs bg-emerald-500 hover:bg-emerald-600 text-white font-medium px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-check text-xs"></i> Selesai
                        </button>
                    </form>
                </div>
            </div>
            <div class="px-5 py-3">
                <ul class="space-y-1">${itemsHTML}</ul>
                ${catUmum}
            </div>
        </div>`;
    }

    function tanganiPesananBaru(pesananList) {
        try {
            const container = document.getElementById('order-container');
            const emptyState = document.getElementById('empty-state');
            
            if (emptyState) emptyState.classList.add('hidden');
            if (container) container.classList.remove('hidden');

            pesananList.forEach(p => {
                if (container) container.insertAdjacentHTML('afterbegin', renderOrderHTML(p));
                let counter = document.getElementById('count-pending');
                if (counter) counter.textContent = parseInt(counter.textContent) + 1;

                antreanTTS.push({
                    meja: p.nomor_meja,
                    nama: p.nama_pemesan
                });
            });

            if (!sedangBicara) {
                prosesAntreanSuara();
            }

        } catch (err) {
            console.error("Terjadi kesalahan Tampilan:", err);
            setTimeout(tutupModal, 3000);
        }
    }

    function cekPesananBaru() {
        if (isChecking) return;
        isChecking = true;

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
            if (!res.ok) throw new Error("Gagal terhubung ke server");
            return res.json();
        })
        .then(data => {
            if (data.ada && data.data && data.data.length > 0) {
                lastId = data.last_id; 
                tanganiPesananBaru(data.data); 
            }
            isChecking = false;
        })
        .catch(err => {
            isChecking = false;
        });
    }

    setInterval(cekPesananBaru, 3000);
</script>

<?= $this->endSection() ?>