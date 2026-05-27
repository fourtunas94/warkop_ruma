<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — ARZANA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-slate-50 min-h-screen">

<header class="sticky top-0 z-30 bg-white border-b border-slate-200">
    <div class="max-w-xl mx-auto px-4 py-3 flex items-center gap-3">
        <a href="javascript:history.back()" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-600">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <div>
            <p class="font-bold text-slate-900 text-sm">Konfirmasi Pesanan</p>
            <p class="text-xs text-slate-500">Meja <span class="font-semibold text-sky-600"><?= esc($meja['nomor_meja']) ?></span></p>
        </div>
    </div>
</header>

<main class="max-w-xl mx-auto px-4 pt-5 pb-32">

    <div id="empty-state" class="hidden py-20 text-center">
        <i class="fa-solid fa-bag-shopping text-slate-300 text-4xl mb-3"></i>
        <p class="text-slate-500 font-medium">Keranjang kamu kosong</p>
        <a href="javascript:history.back()" class="mt-3 inline-block text-sky-500 text-sm font-medium">Kembali ke menu</a>
    </div>

    <div id="checkout-content">
        <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
            <h3 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-bag-shopping text-sky-500"></i> Pesanan Kamu
            </h3>
            <div id="order-list" class="divide-y divide-slate-100"></div>
            <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-200">
                <span class="text-sm font-semibold text-slate-700">Total</span>
                <span id="order-total" class="text-base font-bold text-sky-600">Rp 0</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
            <h3 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-user text-sky-500"></i> Data Pemesan
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                        Nama Kamu <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="nama_pemesan" placeholder="Masukkan nama kamu"
                        class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Catatan (opsional)</label>
                    <textarea id="catatan" rows="2" placeholder="Misal: tidak pakai pedas, dll"
                        class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all resize-none"></textarea>
                </div>
            </div>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-2 text-xs text-amber-700">
            <i class="fa-solid fa-circle-info mt-0.5 flex-shrink-0"></i>
            <span>Pembayaran dilakukan secara <strong>tunai ke kasir</strong> setelah pesanan selesai.</span>
        </div>
    </div>
</main>

<div id="error-msg" class="hidden fixed bottom-24 left-0 right-0 mx-auto max-w-xl px-4">
    <div class="bg-rose-500 text-white text-sm px-4 py-3 rounded-xl text-center font-medium"></div>
</div>

<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 px-4 py-3 max-w-xl mx-auto">
    <button id="btn-pesan" onclick="submitOrder()"
        class="w-full bg-sky-500 hover:bg-sky-600 text-white font-semibold py-3.5 rounded-xl transition-colors text-sm flex items-center justify-center gap-2">
        <i class="fa-solid fa-paper-plane"></i> Kirim Pesanan
    </button>
</div>

<script>
    const KODE_MEJA = '<?= $meja['kode_unik'] ?>';
    const SUBMIT_URL = '<?= base_url('order/submit/' . $meja['kode_unik']) ?>';
    const cart = JSON.parse(localStorage.getItem('arzana_cart_' + KODE_MEJA) || '[]');

    function renderOrder() {
        const list = document.getElementById('order-list');
        if (cart.length === 0) {
            document.getElementById('empty-state').classList.remove('hidden');
            document.getElementById('checkout-content').classList.add('hidden');
            return;
        }
        let total = 0;
        list.innerHTML = cart.map(item => {
            const sub = item.harga * item.qty;
            total += sub;
            return `<div class="py-2.5 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-800">${item.nama_menu} <span class="text-slate-400">x${item.qty}</span></p>
                </div>
                <p class="text-sm font-semibold text-slate-700">Rp ${sub.toLocaleString('id-ID')}</p>
            </div>`;
        }).join('');
        document.getElementById('order-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    function showError(msg) {
        const el = document.getElementById('error-msg');
        el.querySelector('div').textContent = msg;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    async function submitOrder() {
        const nama = document.getElementById('nama_pemesan').value.trim();
        if (!nama) { showError('Nama pemesan wajib diisi.'); return; }
        if (cart.length === 0) { showError('Keranjang kosong.'); return; }

        const btn = document.getElementById('btn-pesan');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Mengirim...';

        const formData = new FormData();
        formData.append('nama_pemesan', nama);
        formData.append('catatan', document.getElementById('catatan').value);
        formData.append('items', JSON.stringify(cart));

        try {
            const res = await fetch(SUBMIT_URL, { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                localStorage.removeItem('arzana_cart_' + KODE_MEJA);
                window.location.href = data.redirect;
            } else {
                showError(data.message || 'Terjadi kesalahan.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> Kirim Pesanan';
            }
        } catch {
            showError('Gagal menghubungi server.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> Kirim Pesanan';
        }
    }

    renderOrder();
</script>

</body>
</html>