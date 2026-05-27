<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu — ARZANA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .category-pill.active { background: #0ea5e9; color: #fff; }
        .menu-section { scroll-margin-top: 120px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen pb-32">

<header class="sticky top-0 z-30 bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-xl mx-auto px-4 py-3 flex items-center justify-between">
        <div>
            <p class="font-bold text-slate-900 text-base">ARZANA</p>
            <p class="text-xs text-slate-500">Meja <span class="font-semibold text-sky-600"><?= esc($meja['nomor_meja']) ?></span></p>
        </div>
        <button onclick="openCart()" class="relative w-10 h-10 flex items-center justify-center bg-sky-500 hover:bg-sky-600 text-white rounded-xl transition-colors">
            <i class="fa-solid fa-bag-shopping"></i>
            <span id="cart-badge" class="hidden absolute -top-1 -right-1 w-4 h-4 text-xs bg-rose-500 text-white rounded-full flex items-center justify-center font-bold">0</span>
        </button>
    </div>
    <div class="max-w-xl mx-auto px-4 pb-3 flex gap-2 overflow-x-auto no-scrollbar">
        <?php foreach ($menuByKategori as $kid => $kat): ?>
        <?php if (!empty($kat['items'])): ?>
        <button onclick="scrollToKategori(<?= $kid ?>)"
            class="category-pill flex-shrink-0 text-xs font-semibold px-3.5 py-1.5 rounded-full border border-slate-200 text-slate-600 hover:bg-sky-500 hover:text-white hover:border-sky-500 transition-all">
            <?= esc($kat['nama']) ?>
        </button>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
</header>

<main class="max-w-xl mx-auto px-4 pt-4 space-y-8">
    <?php foreach ($menuByKategori as $kid => $kat): ?>
    <?php if (!empty($kat['items'])): ?>
    <section id="kategori-<?= $kid ?>" class="menu-section">
        <h2 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-3"><?= esc($kat['nama']) ?></h2>
        <div class="space-y-2">
            <?php foreach ($kat['items'] as $item): ?>
            <div class="bg-white rounded-xl border border-slate-200 p-3 flex items-center gap-3 <?= !$item['stok'] ? 'opacity-60' : '' ?>">
                <div class="w-16 h-16 rounded-lg flex-shrink-0 overflow-hidden bg-slate-100">
                    <?php if ($item['gambar']): ?>
                    <img src="<?= base_url('uploads/menu/' . esc($item['gambar'])) ?>" alt="<?= esc($item['nama_menu']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fa-solid fa-image text-slate-300 text-xl"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-800 text-sm"><?= esc($item['nama_menu']) ?></p>
                    <?php if ($item['deskripsi']): ?>
                    <p class="text-xs text-slate-400 mt-0.5 truncate"><?= esc($item['deskripsi']) ?></p>
                    <?php endif; ?>
                    <p class="text-sm font-bold text-sky-600 mt-1">Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                </div>
                <div class="flex-shrink-0">
                    <?php if ($item['stok']): ?>
                    <button onclick='addToCart(<?= json_encode(['id' => $item['id'], 'nama_menu' => $item['nama_menu'], 'harga' => $item['harga']]) ?>)'
                        class="w-8 h-8 flex items-center justify-center bg-sky-500 hover:bg-sky-600 text-white rounded-lg transition-colors">
                        <i class="fa-solid fa-plus text-xs"></i>
                    </button>
                    <?php else: ?>
                    <span class="text-xs text-rose-400 font-medium">Habis</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    <?php endforeach; ?>
</main>

<div id="panel-cart" class="hidden fixed inset-0 z-40 flex flex-col">
    <div class="flex-1 bg-black/50 backdrop-blur-sm" onclick="closeCart()"></div>
    <div class="bg-white rounded-t-2xl max-h-[80vh] flex flex-col shadow-2xl">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Keranjang</h3>
            <button onclick="closeCart()" class="w-7 h-7 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>
        <div id="cart-list" class="flex-1 overflow-y-auto px-5 py-3 space-y-3"></div>
        <div class="px-5 py-4 border-t border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-slate-600 font-medium">Total</span>
                <span id="cart-total" class="text-base font-bold text-slate-900">Rp 0</span>
            </div>
            <button onclick="goCheckout()" class="w-full bg-sky-500 hover:bg-sky-600 text-white font-semibold py-3 rounded-xl transition-colors text-sm">
                Pesan Sekarang
            </button>
        </div>
    </div>
</div>

<script>
    const KODE_MEJA = '<?= $meja['kode_unik'] ?>';
    let cart = JSON.parse(localStorage.getItem('arzana_cart_' + KODE_MEJA) || '[]');

    function saveCart() {
        localStorage.setItem('arzana_cart_' + KODE_MEJA, JSON.stringify(cart));
        renderBadge();
    }

    function renderBadge() {
        const total = cart.reduce((s, i) => s + i.qty, 0);
        const badge = document.getElementById('cart-badge');
        badge.textContent = total;
        badge.classList.toggle('hidden', total === 0);
        badge.classList.toggle('flex', total > 0);
    }

    function addToCart(item) {
        const idx = cart.findIndex(i => i.id === item.id);
        if (idx > -1) {
            cart[idx].qty++;
        } else {
            cart.push({ ...item, qty: 1, catatan: '' });
        }
        saveCart();
        renderCart();
    }

    function removeFromCart(id) {
        const idx = cart.findIndex(i => i.id === id);
        if (idx > -1) {
            if (cart[idx].qty > 1) cart[idx].qty--;
            else cart.splice(idx, 1);
        }
        saveCart();
        renderCart();
    }

    function renderCart() {
        const list = document.getElementById('cart-list');
        if (cart.length === 0) {
            list.innerHTML = '<div class="py-10 text-center"><i class="fa-solid fa-bag-shopping text-slate-300 text-3xl mb-2"></i><p class="text-sm text-slate-400">Keranjang kosong</p></div>';
            document.getElementById('cart-total').textContent = 'Rp 0';
            return;
        }
        let total = 0;
        list.innerHTML = cart.map(item => {
            const sub = item.harga * item.qty;
            total += sub;
            return `<div class="flex items-center gap-3">
                <div class="flex-1">
                    <p class="text-sm font-medium text-slate-800">${item.nama_menu}</p>
                    <p class="text-xs text-sky-600 font-semibold">Rp ${item.harga.toLocaleString('id-ID')}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="removeFromCart(${item.id})" class="w-7 h-7 flex items-center justify-center bg-slate-100 hover:bg-rose-50 hover:text-rose-500 rounded-lg text-slate-500 transition-colors">
                        <i class="fa-solid fa-minus text-xs"></i>
                    </button>
                    <span class="text-sm font-semibold w-5 text-center">${item.qty}</span>
                    <button onclick='addToCart(${JSON.stringify({id: item.id, nama_menu: item.nama_menu, harga: item.harga})})' class="w-7 h-7 flex items-center justify-center bg-sky-500 hover:bg-sky-600 text-white rounded-lg transition-colors">
                        <i class="fa-solid fa-plus text-xs"></i>
                    </button>
                </div>
            </div>`;
        }).join('');
        document.getElementById('cart-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    function openCart() { document.getElementById('panel-cart').classList.remove('hidden'); renderCart(); }
    function closeCart() { document.getElementById('panel-cart').classList.add('hidden'); }

    function goCheckout() {
        if (cart.length === 0) return;
        closeCart();
        window.location.href = '<?= base_url('order/checkout/' . $meja['kode_unik']) ?>';
    }

    function scrollToKategori(id) {
        document.getElementById('kategori-' + id)?.scrollIntoView({ behavior: 'smooth' });
    }

    renderBadge();
</script>

</body>
</html>