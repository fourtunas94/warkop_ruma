<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="space-y-6">

    <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft">
        <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-purple-500/20 blur-3xl"></div>
        <div class="absolute -bottom-28 -left-24 w-72 h-72 rounded-full bg-sky-600/20 blur-3xl"></div>

        <div class="relative p-5 lg:p-7">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-500/10 border border-purple-400/20 text-purple-300 text-xs font-semibold mb-4">
                        <i class="fa-solid fa-qrcode"></i>
                        QR Code Meja
                    </div>

                    <h1 class="text-2xl lg:text-4xl font-extrabold tracking-tight text-white">
                        Meja & QR Code
                    </h1>

                    <p class="text-sm lg:text-base text-slate-400 mt-2 max-w-2xl">
                        Kelola meja dan QR Code untuk halaman pemesanan pelanggan.
                    </p>
                </div>

                <div class="rounded-3xl bg-white/[.04] border border-white/10 p-4 min-w-full sm:min-w-[280px] lg:min-w-[320px]">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-purple-500/10 border border-purple-400/20 flex items-center justify-center text-purple-300">
                            <i class="fa-solid fa-chair"></i>
                        </div>

                        <div>
                            <p class="text-sm font-bold text-white">Total Meja</p>
                            <p class="text-2xl font-extrabold text-white mt-1">
                                <?= number_format(count($mejas ?? []), 0, ',', '.') ?>
                            </p>
                        </div>
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

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="xl:col-span-1">
            <div class="rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft p-5 lg:p-6 xl:sticky xl:top-24">
                <div class="mb-5">
                    <h2 class="text-lg font-extrabold text-white">Tambah Meja Baru</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Nomor meja akan dibuatkan kode unik otomatis.
                    </p>
                </div>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="mb-4 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-xs text-red-300">
                        <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                            <p><?= esc($err) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('admin/meja/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                            Nomor Meja <span class="text-rose-400">*</span>
                        </label>

                        <input type="text"
                               name="nomor_meja"
                               value="<?= old('nomor_meja') ?>"
                               maxlength="10"
                               placeholder="Meja VIP1"
                               class="w-full uppercase rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">

                        <p class="text-xs text-slate-500 mt-2">
                            Maksimal 10 karakter. Contoh: Meja A1, Meja B2, Meja VIP1.
                        </p>
                    </div>

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-500 to-purple-600 px-5 py-3 text-sm font-bold text-white shadow-glow hover:opacity-95 transition">
                        <i class="fa-solid fa-plus"></i>
                        Tambah Meja
                    </button>
                </form>
            </div>
        </div>

        <div class="xl:col-span-2">
            <div class="rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft overflow-hidden">
                <div class="p-5 lg:p-6 border-b border-white/10">
                    <h2 class="text-lg font-extrabold text-white">Daftar Meja</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Klik tombol QR untuk melihat atau mencetak QR Code meja.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/10 bg-white/[.025] text-xs font-bold text-slate-500 uppercase tracking-[0.16em]">
                                <th class="px-5 py-4 whitespace-nowrap">Meja</th>
                                <th class="px-5 py-4 whitespace-nowrap">URL QR</th>
                                <th class="px-5 py-4 text-center whitespace-nowrap">Status</th>
                                <th class="px-5 py-4 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/10">
                            <?php if (! empty($mejas)): ?>
                                <?php foreach ($mejas as $m): ?>
                                    <?php $url = base_url('order/' . $m['kode_unik']); ?>

                                    <tr class="hover:bg-white/[.025] transition">
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-11 h-11 rounded-2xl bg-purple-500/10 border border-purple-400/20 text-purple-300 flex items-center justify-center">
                                                    <i class="fa-solid fa-chair"></i>
                                                </div>

                                                <div>
                                                    <p class="text-base font-extrabold text-white">
                                                        <?= esc($m['nomor_meja'] ?? '-') ?>
                                                    </p>
                                                    <p class="text-xs text-slate-500">
                                                        ID: <?= esc($m['id'] ?? '-') ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <p class="text-xs font-mono text-slate-500 truncate max-w-[260px]">
                                                <?= esc($url) ?>
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 text-center whitespace-nowrap">
                                            <form action="<?= base_url('admin/meja/toggle/' . $m['id']) ?>" method="post" class="inline">
                                                <?= csrf_field() ?>

                                                <button type="submit"
                                                        class="inline-flex items-center gap-2 text-[10px] font-bold px-3 py-1.5 rounded-full border uppercase tracking-wider transition
                                                        <?= ! empty($m['is_active'])
                                                            ? 'bg-emerald-500/10 text-emerald-300 border-emerald-400/20 hover:bg-emerald-500/15'
                                                            : 'bg-slate-500/10 text-slate-400 border-slate-400/20 hover:bg-slate-500/15' ?>">
                                                    <i class="fa-solid fa-circle text-[7px] <?= ! empty($m['is_active']) ? 'text-emerald-400' : 'text-slate-500' ?>"></i>
                                                    <?= ! empty($m['is_active']) ? 'Aktif' : 'Nonaktif' ?>
                                                </button>
                                            </form>
                                        </td>

                                        <td class="px-5 py-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button"
                                                        onclick="showQR('<?= esc($url) ?>', '<?= esc($m['nomor_meja'] ?? '-') ?>')"
                                                        class="inline-flex items-center gap-2 text-xs font-bold text-purple-300 hover:text-white bg-purple-500/10 hover:bg-purple-500/20 border border-purple-400/20 px-3 py-2 rounded-xl transition">
                                                    <i class="fa-solid fa-qrcode"></i>
                                                    QR
                                                </button>

                                                <form action="<?= base_url('admin/meja/delete/' . $m['id']) ?>"
                                                      method="post"
                                                      onsubmit="return confirm('Hapus meja ini?')">
                                                    <?= csrf_field() ?>

                                                    <button type="submit"
                                                            class="inline-flex items-center gap-2 text-xs font-bold text-rose-300 hover:text-white bg-rose-500/10 hover:bg-rose-500/20 border border-rose-400/20 px-3 py-2 rounded-xl transition">
                                                        <i class="fa-solid fa-trash"></i>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-5 py-20 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-16 h-16 rounded-3xl bg-white/[.04] border border-white/10 flex items-center justify-center">
                                                <i class="fa-solid fa-table-cells text-slate-500 text-2xl"></i>
                                            </div>

                                            <div>
                                                <p class="text-sm font-bold text-white">Belum ada meja</p>
                                                <p class="text-xs text-slate-500 mt-1">
                                                    Tambahkan meja melalui form di samping.
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

<div id="modal-qr" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
    <div class="relative w-full max-w-sm overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950 shadow-soft">
        <div class="absolute -top-20 -right-20 w-48 h-48 rounded-full bg-purple-500/20 blur-3xl"></div>

        <div class="relative p-6 text-center">
            <div class="w-14 h-14 rounded-2xl bg-purple-500/10 border border-purple-400/20 text-purple-300 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-qrcode text-xl"></i>
            </div>

            <h3 class="font-extrabold text-white text-lg mb-1">
                QR Code <span id="modal-nomor"></span>
            </h3>

            <p class="text-xs text-slate-500 mb-5">
                Pelanggan scan QR ini untuk mulai memesan.
            </p>

            <div class="bg-white rounded-3xl p-4 flex justify-center mb-4">
                <div id="qr-container"></div>
            </div>

            <p id="modal-url" class="text-xs font-mono text-slate-500 break-all mb-5"></p>

            <div class="flex gap-3">
                <button onclick="printQR()"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-500 to-purple-600 px-4 py-3 text-sm font-bold text-white shadow-glow hover:opacity-95 transition">
                    <i class="fa-solid fa-print"></i>
                    Print
                </button>

                <button onclick="closeQR()"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-white/[.04] hover:bg-white/[.08] border border-white/10 px-4 py-3 text-sm font-semibold text-slate-300 hover:text-white transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    function showQR(url, nomor) {
        document.getElementById('modal-nomor').textContent = nomor;
        document.getElementById('modal-url').textContent = url;

        const container = document.getElementById('qr-container');
        container.innerHTML = '';

        new QRCode(container, {
            text: url,
            width: 180,
            height: 180,
            correctLevel: QRCode.CorrectLevel.H
        });

        document.getElementById('modal-qr').classList.remove('hidden');
    }

    function closeQR() {
        document.getElementById('modal-qr').classList.add('hidden');
    }

    function printQR() {
        const canvas = document.querySelector('#qr-container canvas');

        if (!canvas) {
            return;
        }

        const nomor = document.getElementById('modal-nomor').textContent;
        const url = document.getElementById('modal-url').textContent;
        const win = window.open('');

        win.document.write(`
            <html>
            <head>
                <title>QR Code ${nomor}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                        padding: 30px;
                    }
                    .box {
                        border: 1px solid #ddd;
                        border-radius: 16px;
                        padding: 24px;
                        display: inline-block;
                    }
                    img {
                        width: 220px;
                        height: 220px;
                    }
                    h2 {
                        margin-bottom: 8px;
                    }
                    p {
                        font-size: 12px;
                        color: #555;
                        word-break: break-all;
                    }
                </style>
            </head>
            <body>
                <div class="box">
                    <h2>Meja ${nomor}</h2>
                    <img src="${canvas.toDataURL()}">
                    <p>${url}</p>
                </div>
            </body>
            </html>
        `);

        win.document.close();
        win.focus();
        win.print();
        win.close();
    }

    document.getElementById('modal-qr').addEventListener('click', function(e) {
        if (e.target === this) {
            closeQR();
        }
    });
</script>

<?= $this->endSection() ?>