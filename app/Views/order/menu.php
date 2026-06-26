<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu — ARZANA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0a0e17;
            --card-dark: #131824;
            --text-light: #ffffff;
            --text-dim: #8a94a6;
            --btn-blue: #0066ff;
            --btn-red: #dc2626;
            --cart-grad: linear-gradient(90deg, #007bff, #8a2be2);
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
            padding-bottom: 100px;
        }

        .max-w-xl {
            max-width: 480px;
            margin: 0 auto;
            position: relative;
        }

        /* --- Header --- */
        .header {
            position: sticky;
            top: 0;
            z-index: 30;
            background-color: var(--bg-dark);
            padding: 20px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-container {
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .info h1 {
            font-size: 16px;
            font-weight: 600;
            line-height: 1.2;
            color: var(--text-light);
        }

        .info p {
            font-size: 12px;
            color: var(--text-dim);
            margin-top: 2px;
        }

        .info p span {
            color: #FDE047;
            font-weight: 600;
        }

        .logout {
            width: 36px;
            height: 36px;
            background-color: var(--card-dark);
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .logout i {
            font-size: 16px;
            color: var(--text-dim);
        }

        /* --- Kategori --- */
        .categories-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 14px;
            padding: 0 20px;
        }

        .cat-list {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding: 0 20px 20px 20px;
        }

        .cat-list::-webkit-scrollbar {
            display: none;
        }

        .category-pill {
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
            border: none;
            transition: all 0.2s;
        }

        .category-pill.active {
            background-color: var(--text-light);
            color: var(--bg-dark);
        }

        .category-pill:not(.active) {
            background-color: var(--card-dark);
            color: var(--text-light);
        }

        /* --- Grid Makanan --- */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            padding: 0 20px;
        }

        .card {
            background-color: var(--card-dark);
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255, 255, 255, 0.03);
        }

        .img-container {
            width: 100%;
            height: 130px;
            background-color: #1e1e1e;
            position: relative;
        }

        .img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .img-container .no-img {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #555;
            font-size: 24px;
        }

        /* --- Perbaikan Layout Card Content --- */
        .card-content {
            padding: 12px;
            display: flex;
            flex-direction: column; /* Stack vertikal */
            justify-content: space-between;
            flex: 1;
        }

        .item-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-light);
            line-height: 1.3;
            margin-bottom: 8px;
            /* Batasi maksimal 2 baris agar seragam */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            min-height: 28px;
        }

        .item-price {
            font-size: 11px;
            color: var(--text-dim);
            font-style: italic;
        }

        /* --- Tombol Aksi --- */
        .action-container {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px; /* Jarak antar tombol */
        }

        .icon-btn {
            width: 26px;
            height: 26px;
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 14px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .btn-plus { background-color: var(--btn-blue); }
        .btn-minus { background-color: var(--btn-red); }

        .item-qty {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-light);
            min-width: 16px;
            text-align: center;
        }

        .btn-habis {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 12px;
            background-color: #333;
            color: #888;
            border: none;
        }

        /* --- Keranjang Bar --- */
        .bottom-bar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(150%);
            width: calc(100% - 40px);
            max-width: 440px;
            background: var(--cart-grad);
            border-radius: 14px;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
            cursor: pointer;
            z-index: 40;
            transition: transform 0.3s ease;
        }

        .bottom-bar.show { transform: translateX(-50%) translateY(0); }

        .cart-text h4 { font-size: 14px; font-weight: 600; color: white; }
        .cart-text p { font-size: 12px; margin-top: 2px; opacity: 0.9; color: white; }

        .cart-icon { position: relative; display: flex; align-items: center; }
        .cart-icon i { font-size: 22px; color: white; }

        .cart-badge {
            position: absolute;
            top: -6px;
            right: -8px;
            background-color: white;
            color: var(--btn-blue);
            font-size: 10px;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>

<div class="max-w-xl">
    <header class="header">
        <div class="header-top">
            <div class="profile">
                <div class="logo-container">
                    <img src="<?= base_url('assets/img/logo_k.png') ?>" alt="Logo Arzana">
                </div>
                <div class="info">
                    <h1>Arzana</h1>
                    <p>Meja <span><?= esc($meja['nomor_meja']) ?></span></p>
                </div>
            </div>
            <div class="logout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </div>
        </div>
    </header>

    <h2 class="categories-title">Kategori</h2>
    <div class="cat-list">
        <button onclick="filterKategori('semua')" id="btn-kat-semua" class="category-pill active">
            Semua
        </button>
        <?php foreach ($menuByKategori as $kid => $kat): ?>
        <?php if (!empty($kat['items'])): ?>
        <button onclick="filterKategori(<?= $kid ?>)" id="btn-kat-<?= $kid ?>" class="category-pill">
            <?= esc($kat['nama']) ?>
        </button>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <main class="menu-grid" id="menu-grid">
        <?php foreach ($menuByKategori as $kid => $kat): ?>
        <?php foreach ($kat['items'] as $item): ?>
        <div class="card menu-item" data-kategori="<?= $kid ?>">
            <div class="img-container">
                <?php if ($item['gambar']): ?>
                <img src="<?= base_url('uploads/menu/' . esc($item['gambar'])) ?>" alt="<?= esc($item['nama_menu']) ?>">
                <?php else: ?>
                <div class="no-img"><i class="fa-solid fa-image"></i></div>
                <?php endif; ?>
            </div>
            
            <div class="card-content">
                <h3 class="item-title"><?= esc($item['nama_menu']) ?></h3>
                
                <div class="item-footer">
                    <p class="item-price">Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                    <div class="action-container" data-id="<?= $item['id'] ?>" data-nama="<?= esc($item['nama_menu']) ?>" data-harga="<?= $item['harga'] ?>" data-stok="<?= $item['stok'] ?>">
                        </div>
                </div>
            </div>
            
        </div>
        <?php endforeach; ?>
        <?php endforeach; ?>
    </main>
</div>

<div id="bottom-bar" class="bottom-bar" onclick="goCheckout()">
    <div class="cart-text">
        <h4>Keranjang (<span id="cart-title-qty">0</span>)</h4>
        <p id="cart-total">Rp 0</p>
    </div>
    <div class="cart-icon">
        <i class="fa-solid fa-cart-shopping"></i>
        <div id="cart-badge" class="cart-badge">0</div>
    </div>
</div>

<script>
    const KODE_MEJA = '<?= esc($meja['kode_unik']) ?>';
    let cart = JSON.parse(localStorage.getItem('arzana_cart_' + KODE_MEJA) || '[]');
    
    function saveCart() {
        localStorage.setItem('arzana_cart_' + KODE_MEJA, JSON.stringify(cart));
        updateAllUI();
    }

    function addToCart(id, nama, harga) {
        cart.push({ id: parseInt(id), nama_menu: nama, harga: parseInt(harga), qty: 1, catatan: '' });
        saveCart();
    }

    function increaseQty(id) {
        let item = cart.find(i => i.id === parseInt(id));
        if (item) {
            item.qty++;
            saveCart();
        }
    }

    function decreaseQty(id) {
        let idx = cart.findIndex(i => i.id === parseInt(id));
        if (idx > -1) {
            if (cart[idx].qty > 1) {
                cart[idx].qty--;
            } else {
                cart.splice(idx, 1);
            }
            saveCart();
        }
    }

    function updateAllUI() {
        document.querySelectorAll('.action-container').forEach(container => {
            let id = parseInt(container.dataset.id);
            let nama = container.dataset.nama;
            let harga = parseInt(container.dataset.harga);
            let stok = parseInt(container.dataset.stok);
            
            let item = cart.find(i => i.id === id);
            
            if (!stok) {
                container.innerHTML = `<button disabled class="btn-habis">Habis</button>`;
                return;
            }

            if (item) {
                // Saat ada qty: tombol minus, angka, tombol plus
                container.innerHTML = `
                    <button onclick="decreaseQty(${id})" class="icon-btn btn-minus"><i class="fa-solid fa-minus"></i></button>
                    <span class="item-qty">${item.qty}</span>
                    <button onclick="increaseQty(${id})" class="icon-btn btn-plus"><i class="fa-solid fa-plus"></i></button>
                `;
            } else {
                // Saat kosong: hanya tombol plus
                container.innerHTML = `
                    <button onclick="addToCart(${id}, '${nama.replace(/'/g, "\\'")}', ${harga})" class="icon-btn btn-plus">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                `;
            }
        });

        updateBottomBar();
    }

    function updateBottomBar() {
        const bottomBar = document.getElementById('bottom-bar');
        const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
        const totalPrice = cart.reduce((sum, item) => sum + (item.harga * item.qty), 0);
        
        if (totalQty > 0) {
            document.getElementById('cart-badge').textContent = totalQty;
            document.getElementById('cart-title-qty').textContent = totalQty;
            
            let formattedPrice = totalPrice.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            document.getElementById('cart-total').textContent = 'Rp ' + formattedPrice;
            
            bottomBar.classList.add('show');
        } else {
            bottomBar.classList.remove('show');
        }
    }

    function filterKategori(id) {
        document.querySelectorAll('.category-pill').forEach(el => {
            el.classList.remove('active');
        });
        
        let btnId = id === 'semua' ? 'btn-kat-semua' : 'btn-kat-' + id;
        let activeBtn = document.getElementById(btnId);
        if(activeBtn) {
            activeBtn.classList.add('active');
        }

        document.querySelectorAll('.menu-item').forEach(item => {
            if (id === 'semua' || item.dataset.kategori == id) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function goCheckout() {
        if (cart.length === 0) return;
        window.location.href = '<?= base_url('order/checkout/' . $meja['kode_unik']) ?>';
    }

    updateAllUI();
</script>
</body>
</html>