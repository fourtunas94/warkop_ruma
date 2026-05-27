<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Pesanan - ARZANA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-gacoan-black { background-color: #121212; }
        .bg-gacoan-gray { background-color: #1E1E1E; }
        .border-gacoan-gray { border-color: #2D2D2D; }
        .text-gacoan-gold { color: #EAB308; }
        .bg-gacoan-gold { background-color: #EAB308; }
    </style>
</head>
<body class="bg-gacoan-black text-white pb-32">

    <header class="fixed top-0 left-0 w-full bg-gacoan-black z-50 border-b border-gacoan-gray shadow-md">
        <div class="flex justify-between items-center p-4">
            <h1 class="text-3xl font-extrabold text-gacoan-gold tracking-tight">ARZANA</h1>
            <div class="flex items-center gap-4">
                <button class="text-gray-400 hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31a8.967 8.967 0 0 1-2.312-6.022c0-3.121-1.692-5.747-4.11-6.707A3.332 3.332 0 0 0 12 1.5c-1.28 0-2.4.717-2.986 1.777a8.967 8.967 0 0 1-4.11 6.707 8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </button>
                <button class="text-gray-400 hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex items-center gap-3 p-4 pt-1 overflow-x-auto border-b border-gacoan-gray bg-gacoan-black/80 backdrop-blur-sm">
            <button onclick="filterKategori('all', this)" class="tab-btn bg-gacoan-gold text-black font-semibold px-6 py-2.5 rounded-xl whitespace-nowrap transition">Semua</button>
            
            <?php foreach($categories as $c): ?>
                <button onclick="filterKategori(<?= $c['id'] ?>, this)" class="tab-btn bg-gacoan-gray text-gray-300 font-medium px-6 py-2.5 rounded-xl whitespace-nowrap hover:bg-gray-800 transition">
                    <?= esc($c['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </header>

    <div class="p-4 pt-44">
        <h2 class="text-xl font-semibold mb-6 text-gray-300">Daftar Menu (Meja <?= esc($table_id); ?>)</h2>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="product-container">
            <?php foreach($products as $p): ?>
            <div class="product-card bg-gacoan-gray rounded-3xl p-3 border border-gacoan-gray shadow-xl group hover:border-gacoan-gold transition-colors flex flex-col justify-between" data-kategori="<?= $p['category_id'] ?>">
                <div>
                    <div class="bg-gray-700 h-36 rounded-2xl mb-4 flex items-center justify-center text-gray-500 text-sm overflow-hidden border border-gacoan-gray">
                        <?php if(!empty($p['image'])): ?>
                            <img src="<?= base_url('uploads/' . esc($p['image'])); ?>" alt="<?= esc($p['name']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <span>No Image</span>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-bold text-gray-100 text-lg mb-1 group-hover:text-white transition"><?= esc($p['name']) ?></h3>
                    <p class="text-gacoan-gold font-bold text-xl mb-4">Rp <?= number_format($p['price'], 0, ',', '.') ?></p>
                </div>
                
                <div id="controls-<?= $p['id'] ?>" class="w-full mt-auto">
                    <button onclick="handleTambahProduk(<?= $p['id'] ?>, '<?= addslashes($p['name']) ?>', <?= $p['price'] ?>)" 
                            class="add-btn bg-gacoan-gold text-black w-full h-14 rounded-xl flex items-center justify-center hover:opacity-90 transition active:scale-95 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </button>

                    <div class="qty-controls hidden flex items-center justify-between gap-3 bg-gacoan-gray h-14 rounded-xl border border-gacoan-gold p-1 shadow-lg">
                        <button onclick="handleKurangiKuantitas(<?= $p['id'] ?>)" class="w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center text-gacoan-gold hover:bg-gray-700 transition active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                            </svg>
                        </button>
                        <span class="text-3xl font-extrabold text-white qty-text" id="qty-text-<?= $p['id'] ?>">1</span>
                        <button onclick="handleTambahKuantitas(<?= $p['id'] ?>)" class="w-12 h-12 bg-gacoan-gold rounded-lg flex items-center justify-center text-black hover:opacity-90 transition active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="fixed bottom-0 left-0 w-full bg-gacoan-gray/95 backdrop-blur-sm border-t border-gacoan-gray p-4 pt-5 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.3)] flex justify-between items-center z-50 rounded-t-3xl">
        <div class="flex items-center gap-4">
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-10 h-10 text-gacoan-gold">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
                <div id="badge-keranjang" class="absolute -top-1 -right-2 bg-red-600 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center hidden">0</div>
            </div>
            <div>
                <p class="text-sm text-gray-400">Total Pembayaran</p>
                <p class="text-2xl font-extrabold text-white" id="totalHarga">Rp 0</p>
            </div>
        </div>
        <button onclick="bukaCheckout()" class="bg-gacoan-gold hover:opacity-90 text-black px-12 py-4 rounded-2xl font-bold text-xl shadow-lg transition active:scale-95 whitespace-nowrap">
            Checkout
        </button>
    </div>

    <script>
        let keranjang = [];

        // --- Logika Tombol & Keranjang ---
        function handleTambahProduk(id, nama, harga) {
            tambahKeKeranjang(id, nama, harga);
            toggleTombol(id, true);
        }

        function handleTambahKuantitas(id) {
            let item = keranjang.find(item => item.id === id);
            if (item) {
                item.qty += 1;
                updateKuantitasDiKartu(id, item.qty);
                updateTampilanKeranjang();
            }
        }

        function handleKurangiKuantitas(id) {
            let itemIndex = keranjang.findIndex(item => item.id === id);
            if (itemIndex !== -1) {
                keranjang[itemIndex].qty -= 1;
                
                if (keranjang[itemIndex].qty <= 0) {
                    keranjang.splice(itemIndex, 1);
                    toggleTombol(id, false);
                } else {
                    updateKuantitasDiKartu(id, keranjang[itemIndex].qty);
                }
                updateTampilanKeranjang();
            }
        }

        function tambahKeKeranjang(id, nama, harga) {
            let itemAda = keranjang.find(item => item.id === id);
            if (itemAda) {
                itemAda.qty += 1;
            } else {
                keranjang.push({ id: id, nama: nama, harga: harga, qty: 1 });
            }
            updateTampilanKeranjang();
        }

        function updateTampilanKeranjang() {
            let totalItem = 0;
            let totalHarga = 0;

            keranjang.forEach(item => {
                totalItem += item.qty;
                totalHarga += (item.harga * item.qty);
            });

            document.getElementById('totalHarga').innerText = 'Rp ' + totalHarga.toLocaleString('id-ID');
            
            let badge = document.getElementById('badge-keranjang');
            if (totalItem > 0) {
                badge.innerText = totalItem;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        function toggleTombol(id, showQtyControls) {
            let controlsContainer = document.getElementById('controls-' + id);
            let addBtn = controlsContainer.querySelector('.add-btn');
            let qtyControls = controlsContainer.querySelector('.qty-controls');
            let qtyText = controlsContainer.querySelector('.qty-text');

            if (showQtyControls) {
                addBtn.classList.add('hidden');
                qtyControls.classList.remove('hidden');
                qtyText.innerText = 1;
            } else {
                addBtn.classList.remove('hidden');
                qtyControls.classList.add('hidden');
            }
        }

        function updateKuantitasDiKartu(id, qty) {
            document.getElementById('qty-text-' + id).innerText = qty;
        }

        function bukaCheckout() {
            if (keranjang.length === 0) {
                alert('Keranjang masih kosong! Silakan pilih menu dulu.');
                return;
            }
            localStorage.setItem('keranjang', JSON.stringify(keranjang));
            alert('Data keranjang berhasil disimpan di LocalStorage. Siap diarahkan ke halaman Checkout.');
        }

        // --- Logika Filter Kategori ---
        function filterKategori(idKategori, elemenTombol) {
            // 1. Reset semua warna tombol tab kembali ke warna abu-abu (tidak aktif)
            let tabs = document.querySelectorAll('.tab-btn');
            tabs.forEach(tab => {
                tab.classList.remove('bg-gacoan-gold', 'text-black', 'font-semibold');
                tab.classList.add('bg-gacoan-gray', 'text-gray-300', 'font-medium');
            });
            
            // 2. Beri warna emas (aktif) pada tombol yang baru saja diklik
            elemenTombol.classList.remove('bg-gacoan-gray', 'text-gray-300', 'font-medium');
            elemenTombol.classList.add('bg-gacoan-gold', 'text-black', 'font-semibold');

            // 3. Filter produk berdasarkan kategori
            let products = document.querySelectorAll('.product-card');
            products.forEach(card => {
                // Ambil data-kategori dari setiap kartu produk
                let kategoriProduk = card.getAttribute('data-kategori');
                
                // Jika yang diklik 'all' ATAU id kategori cocok, tampilkan kartu (flex)
                if (idKategori === 'all' || kategoriProduk == idKategori) {
                    card.style.display = 'flex'; 
                } else {
                    // Jika tidak cocok, sembunyikan kartu
                    card.style.display = 'none'; 
                }
            });
        }
    </script>
</body>
</html>