<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu — ARZANA</title>
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
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
    </style>
</head>
<body class="min-h-screen pb-28">

<header class="sticky top-0 z-30 bg-darkbg pb-3 pt-5 px-4 border-b border-gray-800">
    <div class="max-w-xl mx-auto flex items-center justify-between mb-5">
        <h1 class="font-bold text-gold text-xl tracking-widest uppercase">ARZANA</h1>
        <div class="flex items-center gap-5 text-gold text-xl">
            <p class="text-xs" style="color:#888;"><span style="color:#FDE047; font-weight:600;"><?= esc($meja['nomor_meja']) ?></span></p>
        </div>
    </div>
    <div class="max-w-xl mx-auto flex gap-3 overflow-x-auto no-scrollbar">
        <button onclick="filterKategori('semua')" id="btn-kat-semua" class="category-pill flex-shrink-0 text-sm font-semibold px-5 py-2.5 rounded-xl bg-gold text-black transition-all">
            Semua
        </button>
        <?php foreach ($menuByKategori as $kid => $kat): ?>
        <?php if (!empty($kat['items'])): ?>
        <button onclick="filterKategori(<?= $kid ?>)" id="btn-kat-<?= $kid ?>" class="category-pill flex-shrink-0 text-sm font-semibold px-5 py-2.5 rounded-xl bg-btnbg text-gray-300 transition-all">
            <?= esc($kat['nama']) ?>
        </button>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
</header>

<main class="max-w-xl mx-auto px-4 pt-6">
    <div class="grid grid-cols-2 gap-4" id="menu-grid">
        <?php foreach ($menuByKategori as $kid => $kat): ?>
        <?php foreach ($kat['items'] as $item): ?>
        <div class="menu-item cardbg rounded-2xl overflow-hidden flex flex-col border border-gray-800" data-kategori="<?= $kid ?>">
            <div class="w-full h-36 bg-btnbg relative">
                <?php if ($item['gambar']): ?>
                <img src="<?= base_url('uploads/menu/' . esc($item['gambar'])) ?>" alt="<?= esc($item['nama_menu']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                <div class="w-full h-full flex items-center justify-center">
                    <i class="fa-solid fa-image text-gray-600 text-2xl"></i>
                </div>
                <?php endif; ?>
            </div>
            <div class="p-4 flex flex-col flex-1">
                <p class="font-semibold text-white text-sm mb-1 leading-tight"><?= esc($item['nama_menu']) ?></p>
                <p class="text-sm font-bold text-gold mb-4 mt-auto">Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                <div class="action-container mt-auto" data-id="<?= $item['id'] ?>" data-nama="<?= esc($item['nama_menu']) ?>" data-harga="<?= $item['harga'] ?>" data-stok="<?= $item['stok'] ?>">
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</main>

<div id="bottom-bar" class="fixed bottom-0 inset-x-0 bg-cardbg border-t border-gray-800 z-40 transform translate-y-full transition-transform duration-300">
    <div class="max-w-xl mx-auto px-5 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="relative">
                <i class="fa-solid fa-cart-shopping text-gold text-3xl"></i>
                <span id="cart-badge" class="absolute -top-1 -right-2 w-5 h-5 bg-red-500 text-white text-[11px] font-bold rounded-full flex items-center justify-center">0</span>
            </div>
            <div class="ml-1">
                <p class="text-xs text-gray-400 font-medium mb-0.5">Total Pembayaran</p>
                <p id="cart-total" class="text-xl font-bold text-gold">Rp 0</p>
            </div>
        </div>
        <button onclick="goCheckout()" class="bg-gold hover:bg-yellow-500 text-black font-bold px-7 py-3 rounded-xl transition-colors text-sm">
            Checkout
        </button>
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

    function manualInput(id, val) {
        let qty = parseInt(val);
        let idx = cart.findIndex(i => i.id === parseInt(id));
        if (idx > -1) {
            if (isNaN(qty) || qty <= 0) {
                cart.splice(idx, 1);
            } else {
                cart[idx].qty = qty;
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
                container.innerHTML = `<button disabled class="w-full bg-btnbg text-gray-500 font-bold py-2 rounded-xl text-sm h-10">Habis</button>`;
                return;
            }

            if (item) {
                container.innerHTML = `
                    <div class="flex items-center justify-between h-10">
                        <button onclick="decreaseQty(${id})" class="w-11 h-11 flex items-center justify-center bg-btnbg text-gray-300 rounded-xl transition-colors"><i class="fa-solid fa-minus text-sm"></i></button>
                        <input type="number" onchange="manualInput(${id}, this.value)" value="${item.qty}" class="w-10 h-full bg-transparent text-white text-center text-base font-semibold focus:outline-none">
                        <button onclick="increaseQty(${id})" class="w-11 h-11 flex items-center justify-center bg-gold text-black rounded-xl transition-colors"><i class="fa-solid fa-plus text-sm"></i></button>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <button onclick="addToCart(${id}, '${nama.replace(/'/g, "\\'")}', ${harga})" class="w-full bg-gold hover:bg-yellow-500 text-black font-bold py-2 rounded-xl transition-colors text-sm flex items-center justify-center gap-2 h-10">
                        <i class="fa-solid fa-plus text-base"></i>
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
            document.getElementById('cart-total').textContent = 'Rp ' + totalPrice.toLocaleString('id-ID');
            bottomBar.classList.remove('translate-y-full');
        } else {
            bottomBar.classList.add('translate-y-full');
        }
    }

    function filterKategori(id) {
        document.querySelectorAll('.category-pill').forEach(el => {
            el.classList.remove('bg-gold', 'text-black');
            el.classList.add('bg-btnbg', 'text-gray-300');
        });
        
        let btnId = id === 'semua' ? 'btn-kat-semua' : 'btn-kat-' + id;
        let activeBtn = document.getElementById(btnId);
        if(activeBtn) {
            activeBtn.classList.remove('bg-btnbg', 'text-gray-300');
            activeBtn.classList.add('bg-gold', 'text-black');
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