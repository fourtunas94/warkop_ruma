<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — ARZANA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0a0e17;
            --card-dark: #131824;
            --text-light: #ffffff;
            --text-dim: #8a94a6;
            --btn-blue: #0066ff;
            --border-color: rgba(255, 255, 255, 0.05);
            --cart-grad: linear-gradient(90deg, #007bff, #8a2be2);
            --success-color: #10b981;
            --danger-color: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-light);
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        .app-container {
            max-width: 480px;
            margin: 0 auto;
            position: relative;
            padding-bottom: 120px;
        }

        /* --- Header --- */
        .header {
            position: sticky;
            top: 0;
            z-index: 30;
            background-color: var(--bg-dark);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 1px solid var(--border-color);
        }

        .btn-back {
            width: 40px;
            height: 40px;
            background-color: var(--card-dark);
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text-dim);
            text-decoration: none;
            border: 1px solid var(--border-color);
            transition: 0.2s;
        }

        .btn-back:hover {
            color: var(--text-light);
            background-color: #1a2235;
        }

        .header-title h1 {
            font-size: 16px;
            font-weight: 600;
        }

        .header-title p {
            font-size: 12px;
            color: var(--text-dim);
        }

        .header-title p span {
            color: #FDE047;
            font-weight: 600;
        }

        /* --- Main Content --- */
        .main-content {
            padding: 20px;
        }

        .section-card {
            background-color: var(--card-dark);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }

        .section-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: var(--btn-blue);
        }

        /* --- Order List --- */
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-info h4 {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-light);
        }

        .item-info p {
            font-size: 12px;
            color: var(--btn-blue);
            font-weight: 600;
            margin-top: 4px;
        }

        .item-price {
            font-size: 14px;
            font-weight: 600;
        }

        .order-summary {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border-color);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .summary-row.promo {
            color: var(--success-color);
            display: none;
        }

        .summary-row.promo.show {
            display: flex;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--border-color);
        }

        .summary-total span {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-dim);
        }

        .summary-total strong {
            font-size: 18px;
            font-weight: 700;
            color: var(--btn-blue);
        }

        /* --- Forms --- */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .form-group label span {
            color: var(--danger-color);
        }

        .form-control {
            width: 100%;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            color: var(--text-light);
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 13px;
            transition: 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--btn-blue);
        }

        textarea.form-control {
            resize: none;
        }

        .promo-wrap {
            display: flex;
            gap: 10px;
        }

        .btn-promo {
            background-color: #1e2538;
            color: var(--text-light);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-promo:hover {
            background-color: var(--btn-blue);
            border-color: var(--btn-blue);
        }

        .promo-msg {
            font-size: 11px;
            font-weight: 600;
            margin-top: 8px;
            display: none;
        }

        /* --- Metode Pembayaran (Baru) --- */
        .payment-options {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .pay-option {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .pay-option:hover {
            border-color: rgba(255, 255, 255, 0.2);
        }

        .pay-option input[type="radio"] {
            display: none;
        }

        .pay-radio-custom {
            width: 18px;
            height: 18px;
            border: 2px solid var(--text-dim);
            border-radius: 50%;
            margin-right: 12px;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .pay-option input[type="radio"]:checked + .pay-radio-custom {
            border-color: var(--btn-blue);
        }

        .pay-option input[type="radio"]:checked + .pay-radio-custom::after {
            content: '';
            width: 8px;
            height: 8px;
            background-color: var(--btn-blue);
            border-radius: 50%;
        }

        .pay-info {
            display: flex;
            flex-direction: column;
        }

        .pay-info span {
            font-size: 13px;
            font-weight: 600;
        }

        .pay-info small {
            font-size: 11px;
            color: var(--text-dim);
            margin-top: 2px;
        }

        /* --- Bottom Fixed Bar --- */
        .bottom-action {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: var(--card-dark);
            padding: 16px 20px;
            border-top: 1px solid var(--border-color);
            z-index: 40;
            display: flex;
            justify-content: center;
        }

        .bottom-action-inner {
            width: 100%;
            max-width: 440px;
        }

        .btn-submit {
            width: 100%;
            background: var(--cart-grad);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 16px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            transition: transform 0.1s;
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .btn-submit:disabled {
            background: #333;
            color: #888;
            box-shadow: none;
            cursor: not-allowed;
        }

        /* Error Toast */
        #error-msg {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--danger-color);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            z-index: 100;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            display: none;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            display: none;
        }

        .empty-state i {
            font-size: 48px;
            color: var(--text-dim);
            margin-bottom: 16px;
        }

        .empty-state p {
            color: var(--text-dim);
            margin-bottom: 20px;
        }

        .btn-outline {
            display: inline-block;
            border: 1px solid var(--btn-blue);
            color: var(--btn-blue);
            padding: 10px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="app-container">
    
    <div id="error-msg">Pesan error disini</div>

    <header class="header">
        <a href="javascript:history.back()" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="header-title">
            <h1>Konfirmasi Pesanan</h1>
            <p>Meja <span><?= esc($meja['nomor_meja']) ?></span></p>
        </div>
    </header>

    <main class="main-content">
        
        <div id="empty-state" class="empty-state">
            <i class="fa-solid fa-cart-arrow-down"></i>
            <p>Keranjang kamu masih kosong nih.</p>
            <a href="javascript:history.back()" class="btn-outline">Kembali ke Menu</a>
        </div>

        <div id="checkout-content">
            <section class="section-card">
                <h3 class="section-title"><i class="fa-solid fa-clipboard-list"></i> Pesanan Kamu</h3>
                <div id="order-list"></div>
                
                <div class="order-summary">
                    <div id="row-promo" class="summary-row promo">
                        <span>Potongan (Promo)</span>
                        <span id="promo-val">-Rp 0</span>
                    </div>
                    <div class="summary-total">
                        <span>Total Pembayaran</span>
                        <strong id="order-total">Rp 0</strong>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <h3 class="section-title"><i class="fa-solid fa-wallet"></i> Metode Pembayaran</h3>
                <div class="payment-options">
                    <label class="pay-option">
                        <input type="radio" name="payment_method" value="tunai" checked>
                        <div class="pay-radio-custom"></div>
                        <div class="pay-info">
                            <span>Tunai ke Kasir</span>
                            <small>Bayar langsung di meja kasir</small>
                        </div>
                    </label>
                    <label class="pay-option">
                        <input type="radio" name="payment_method" value="qris">
                        <div class="pay-radio-custom"></div>
                        <div class="pay-info">
                            <span>QRIS / E-Wallet</span>
                            <small>Gopay, OVO, Dana, ShopeePay</small>
                        </div>
                    </label>
                    <label class="pay-option">
                        <input type="radio" name="payment_method" value="transfer">
                        <div class="pay-radio-custom"></div>
                        <div class="pay-info">
                            <span>Transfer Bank</span>
                            <small>Virtual Account BCA, Mandiri, BNI</small>
                        </div>
                    </label>
                </div>
            </section>

            <section class="section-card">
                <h3 class="section-title"><i class="fa-solid fa-user-pen"></i> Data Pemesan</h3>
                
                <div class="form-group">
                    <label>Nama Kamu <span>*</span></label>
                    <input type="text" id="nama_pemesan" class="form-control" placeholder="Masukkan nama kamu">
                </div>
                
                <div class="form-group">
                    <label>Catatan (opsional)</label>
                    <textarea id="catatan" rows="2" class="form-control" placeholder="Misal: tidak pakai pedas, es dipisah"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Kode Promo</label>
                    <div class="promo-wrap">
                        <input type="text" id="kode_promo" class="form-control uppercase" placeholder="Masukkan kode">
                        <button type="button" onclick="cekPromo()" class="btn-promo">Pakai</button>
                    </div>
                    <p id="promo-info" class="promo-msg"></p>
                </div>
            </section>
        </div>
    </main>

    <div class="bottom-action">
        <div class="bottom-action-inner">
            <button id="btn-pesan" onclick="submitOrder()" class="btn-submit">
                <i class="fa-solid fa-paper-plane"></i> Kirim Pesanan Sekarang
            </button>
        </div>
    </div>
</div>

<script>
    const KODE_MEJA  = '<?= esc($meja['kode_unik']) ?>';
    const SUBMIT_URL = '<?= base_url('order/submit/' . $meja['kode_unik']) ?>';
    const cart       = JSON.parse(localStorage.getItem('arzana_cart_' + KODE_MEJA) || '[]');

    let potongan  = 0;
    let kodeValid = '';

    function formatRupiah(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function cekPromo() {
        const input   = document.getElementById('kode_promo').value.trim().toUpperCase();
        const info    = document.getElementById('promo-info');
        const rowPromo = document.getElementById('row-promo');

        info.style.display = 'block';

        if (input === '') {
            potongan  = 0;
            kodeValid = '';
            info.textContent  = 'Masukkan kode promo terlebih dahulu.';
            info.style.color  = 'var(--danger-color)';
            rowPromo.classList.remove('show');
            renderOrder();
            return;
        }

        if (input === 'DISC10') {
            const subtotal = cart.reduce((s, i) => s + (i.harga * i.qty), 0);
            potongan  = Math.floor(subtotal * 0.1);
            kodeValid = input;

            info.innerHTML   = '<i class="fa-solid fa-circle-check mr-1"></i> Kode berhasil digunakan!';
            info.style.color = 'var(--success-color)';
            rowPromo.classList.add('show');
            document.getElementById('promo-val').textContent = '-Rp ' + formatRupiah(potongan);
        } else {
            potongan  = 0;
            kodeValid = '';
            info.innerHTML   = '<i class="fa-solid fa-circle-xmark mr-1"></i> Kode tidak valid.';
            info.style.color = 'var(--danger-color)';
            rowPromo.classList.remove('show');
        }

        renderOrder();
    }

    function renderOrder() {
        const list = document.getElementById('order-list');

        if (cart.length === 0) {
            document.getElementById('empty-state').style.display    = 'block';
            document.getElementById('checkout-content').style.display = 'none';
            document.querySelector('.bottom-action').style.display  = 'none';
            return;
        }

        let subtotal = 0;
        list.innerHTML = cart.map(item => {
            const sub = item.harga * item.qty;
            subtotal += sub;
            return `
                <div class="order-item">
                    <div class="item-info">
                        <h4>${item.nama_menu}</h4>
                        <p>x${item.qty}</p>
                    </div>
                    <div class="item-price">Rp ${formatRupiah(sub)}</div>
                </div>
            `;
        }).join('');

        const finalTotal = subtotal - potongan;
        document.getElementById('order-total').textContent = 'Rp ' + formatRupiah(Math.max(0, finalTotal));
    }

    function showError(msg) {
        const el = document.getElementById('error-msg');
        el.textContent    = msg;
        el.style.display  = 'block';
        setTimeout(() => el.style.display = 'none', 4000);
    }

    async function submitOrder() {
        const nama          = document.getElementById('nama_pemesan').value.trim();
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

        if (!nama)           { showError('Nama pemesan wajib diisi.'); return; }
        if (cart.length === 0) { showError('Keranjang kosong.'); return; }

        const btn = document.getElementById('btn-pesan');
        btn.disabled  = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';

        const formData = new FormData();
        formData.append('nama_pemesan',   nama);
        formData.append('catatan',        document.getElementById('catatan').value);
        formData.append('payment_method', paymentMethod);
        formData.append('items',          JSON.stringify(cart));
        formData.append('kode_promo',     kodeValid);
        formData.append('potongan',       potongan);

        try {
            const res  = await fetch(SUBMIT_URL, { method: 'POST', body: formData });
            const text = await res.text();

            let data;
            try {
                data = JSON.parse(text);
            } catch(e) {
                showError('Response tidak valid dari server.');
                console.error('Raw response:', text);
                btn.disabled  = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Pesanan Sekarang';
                return;
            }

            if (data.success) {
                localStorage.removeItem('arzana_cart_' + KODE_MEJA);
                window.location.href = data.redirect;
            } else if (data.debug || data.debug_error) {
                showError('Debug mode aktif — cek console.');
                console.log('DEBUG RESPONSE:', data);
                btn.disabled  = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Pesanan Sekarang';
            } else {
                showError(data.message || 'Terjadi kesalahan.');
                btn.disabled  = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Pesanan Sekarang';
            }
        } catch(err) {
            showError('Gagal menghubungi server: ' + err.message);
            console.error(err);
            btn.disabled  = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Pesanan Sekarang';
        }
    }

    renderOrder();
</script>

</body>
</html>