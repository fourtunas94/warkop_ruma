<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — ARZANA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        gold: '#e2b938',
                        darkbg: '#121212',
                        cardbg: '#1e1e1e',
                        btnbg: '#2a2a2a'
                    }
                }
            }
        }
    </script>
    <style> 
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #121212; color: #ffffff; } 
    </style>
</head>
<body class="min-h-screen">

<header class="sticky top-0 z-30 bg-darkbg border-b border-gray-800">
    <div class="max-w-xl mx-auto px-4 py-4 flex items-center gap-4">
        <a href="javascript:history.back()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-btnbg text-gray-300 transition-colors hover:bg-gray-700">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div>
            <p class="font-bold text-white text-base">Konfirmasi Pesanan</p>
            <p class="text-xs text-gray-400 mt-0.5">Meja <span class="font-bold text-gold"><?= esc($meja['nomor_meja']) ?></span></p>
        </div>
    </div>
</header>

<main class="max-w-xl mx-auto px-4 pt-5 pb-32">

    <div id="empty-state" class="hidden py-20 text-center">
        <i class="fa-solid fa-cart-arrow-down text-gray-600 text-5xl mb-4"></i>
        <p class="text-gray-400 font-medium text-lg">Keranjang kamu kosong</p>
        <a href="javascript:history.back()" class="mt-4 inline-block text-gold text-sm font-bold bg-btnbg px-6 py-2.5 rounded-xl">Kembali ke menu</a>
    </div>

    <div id="checkout-content">
        <div class="bg-cardbg rounded-2xl border border-gray-800 p-5 mb-5 shadow-lg">
            <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-list text-gold"></i> Pesanan Kamu
            </h3>
            <div id="order-list" class="divide-y divide-gray-800"></div>
            
            <div id="row-promo" class="hidden flex items-center justify-between pt-3 mt-3 border-t border-gray-800 text-emerald-400">
                <span class="text-sm font-semibold">Potongan (Promo)</span>
                <span id="promo-val" class="text-sm font-bold">-Rp 0</span>
            </div>
            
            <div class="flex items-center justify-between pt-4 mt-3 border-t border-gray-800">
                <span class="text-sm font-bold text-gray-300">Total Pembayaran</span>
                <span id="order-total" class="text-xl font-bold text-gold">Rp 0</span>
            </div>
        </div>

        <div class="bg-cardbg rounded-2xl border border-gray-800 p-5 mb-5 shadow-lg">
            <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-gold"></i> Data Pemesan
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">
                        Nama Kamu <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama_pemesan" placeholder="Masukkan nama kamu"
                        class="w-full bg-darkbg border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gold/50 focus:border-gold transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Catatan (opsional)</label>
                    <textarea id="catatan" rows="2" placeholder="Misal: tidak pakai pedas, dll"
                        class="w-full bg-darkbg border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gold/50 focus:border-gold transition-all resize-none"></textarea>
                </div>
                
                <div class="pt-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Kode Promo</label>
                    <div class="flex gap-2">
                        <input type="text" id="kode_promo" placeholder="Masukkan kode promo"
                            class="flex-1 bg-darkbg border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gold/50 focus:border-gold transition-all uppercase">
                        <button type="button" onclick="cekPromo()" class="bg-btnbg hover:bg-gray-700 text-white px-5 rounded-xl text-xs font-bold border border-gray-700 transition-colors">Periksa</button>
                    </div>
                    <p id="promo-info" class="text-xs mt-2 font-semibold hidden"></p>
                </div>
            </div>
        </div>

        <div class="bg-btnbg border border-gray-700 rounded-xl p-4 flex items-start gap-3 text-xs text-gray-300">
            <i class="fa-solid fa-circle-info mt-0.5 text-gold text-base"></i>
            <span class="leading-relaxed">Pembayaran dilakukan secara <strong class="text-white">tunai ke kasir</strong> setelah pesanan kamu selesai dikonfirmasi.</span>
        </div>
    </div>
</main>

<div id="error-msg" class="hidden fixed bottom-24 left-0 right-0 mx-auto max-w-xl px-4 z-50">
    <div class="bg-red-500 text-white text-sm px-4 py-3 rounded-xl text-center font-bold shadow-xl"></div>
</div>

<div class="fixed bottom-0 left-0 right-0 bg-cardbg border-t border-gray-800 px-4 py-4 max-w-xl mx-auto z-40">
    <button id="btn-pesan" onclick="submitOrder()"
        class="w-full bg-gold hover:bg-yellow-500 text-black font-bold py-3.5 rounded-xl transition-colors text-sm flex items-center justify-center gap-2">
        <i class="fa-solid fa-paper-plane"></i> Kirim Pesanan Sekarang
    </button>
</div>

<script>
    const KODE_MEJA = '<?= $meja['kode_unik'] ?>';
    const SUBMIT_URL = '<?= base_url('order/submit/' . $meja['kode_unik']) ?>';
    const cart = JSON.parse(localStorage.getItem('arzana_cart_' + KODE_MEJA) || '[]');
    
    let potongan = 0;
    let kodeValid = "";

    function cekPromo() {
        const input = document.getElementById('kode_promo').value.trim().toUpperCase();
        const info = document.getElementById('promo-info');
        const rowPromo = document.getElementById('row-promo');
        
        info.classList.remove('hidden');

        if (input === "") {
            potongan = 0;
            kodeValid = "";
            info.textContent = "Masukkan kode promo terlebih dahulu.";
            info.className = "text-xs mt-2 font-semibold text-red-400 block";
            rowPromo.classList.add('hidden');
            renderOrder();
            return;
        }

        if (input === "DISC10") {
            const subtotal = cart.reduce((s, i) => s + (i.harga * i.qty), 0);
            potongan = Math.floor(subtotal * 0.1);
            kodeValid = input;
            
            info.innerHTML = '<i class="fa-solid fa-circle-check mr-1"></i> Kode berhasil digunakan!';
            info.className = "text-xs mt-2 font-bold text-emerald-400 block";
            rowPromo.classList.remove('hidden');
            rowPromo.classList.add('flex');
            document.getElementById('promo-val').textContent = '-Rp ' + potongan.toLocaleString('id-ID');
        } else {
            potongan = 0;
            kodeValid = "";
            info.innerHTML = '<i class="fa-solid fa-circle-xmark mr-1"></i> Kode tidak ditemukan atau tidak valid.';
            info.className = "text-xs mt-2 font-bold text-red-400 block";
            rowPromo.classList.remove('flex');
            rowPromo.classList.add('hidden');
        }
        
        renderOrder();
    }

    function renderOrder() {
        const list = document.getElementById('order-list');
        if (cart.length === 0) {
            document.getElementById('empty-state').classList.remove('hidden');
            document.getElementById('checkout-content').classList.add('hidden');
            return;
        }
        
        let subtotal = 0;
        list.innerHTML = cart.map(item => {
            const sub = item.harga * item.qty;
            subtotal += sub;
            return `<div class="py-3 flex items-center justify-between">
                <div class="pr-4">
                    <p class="text-sm font-semibold text-white">${item.nama_menu}</p>
                    <p class="text-xs font-bold text-gold mt-1">x${item.qty}</p>
                </div>
                <p class="text-sm font-bold text-gray-300">Rp ${sub.toLocaleString('id-ID')}</p>
            </div>`;
        }).join('');
        
        const finalTotal = subtotal - potongan;
        document.getElementById('order-total').textContent = 'Rp ' + Math.max(0, finalTotal).toLocaleString('id-ID');
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
        formData.append('kode_promo', kodeValid);
        formData.append('potongan', potongan);

        try {
            const res = await fetch(SUBMIT_URL, { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                localStorage.removeItem('arzana_cart_' + KODE_MEJA);
                window.location.href = data.redirect;
            } else {
                showError(data.message || 'Terjadi kesalahan.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> Kirim Pesanan Sekarang';
            }
        } catch {
            showError('Gagal menghubungi server.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> Kirim Pesanan Sekarang';
        }
    }

    renderOrder();
</script>

</body>
</html>