<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-semibold text-slate-900">Meja & QR Code</h1>
        <p class="text-sm text-slate-500 mt-0.5">Kelola meja dan QR code untuk pemesanan pelanggan.</p>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3 rounded-lg">
    <i class="fa-solid fa-circle-check"></i> <?= session()->getFlashdata('success') ?>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="mb-4 flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-3 rounded-lg">
    <i class="fa-solid fa-circle-exclamation"></i> <?= session()->getFlashdata('error') ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <div class="xl:col-span-1">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Tambah Meja Baru</h2>
            <?php if (session()->getFlashdata('errors')): ?>
            <div class="mb-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs px-3 py-2 rounded-lg">
                <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                <p><?= esc($err) ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <form action="<?= base_url('admin/meja/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                        Nomor Meja <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="nomor_meja" value="<?= old('nomor_meja') ?>"
                        maxlength="10"
                        class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all uppercase"
                        placeholder="Contoh: A1, B2, VIP1">
                    <p class="text-xs text-slate-400 mt-1">Maks 10 karakter, otomatis kapital.</p>
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                    <i class="fa-solid fa-plus text-xs"></i> Tambah Meja
                </button>
            </form>
        </div>
    </div>

    <div class="xl:col-span-2">
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-left">
                        <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Meja</th>
                        <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">URL QR</th>
                        <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide text-center">Status</th>
                        <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($mejas)): ?>
                        <?php foreach ($mejas as $m): ?>
                        <?php $url = base_url('order/' . $m['kode_unik']); ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <span class="text-base font-bold text-slate-800"><?= esc($m['nomor_meja']) ?></span>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-xs font-mono text-slate-400 truncate max-w-[200px]"><?= $url ?></p>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <form action="<?= base_url('admin/meja/toggle/' . $m['id']) ?>" method="post" class="inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-md transition-colors
                                        <?= $m['is_active'] ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' ?>">
                                        <i class="fa-solid fa-circle text-xs <?= $m['is_active'] ? 'text-emerald-400' : 'text-slate-300' ?>"></i>
                                        <?= $m['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </button>
                                </form>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="showQR('<?= $url ?>', '<?= esc($m['nomor_meja']) ?>')"
                                        class="inline-flex items-center gap-1.5 text-xs text-slate-600 hover:text-violet-600 bg-slate-100 hover:bg-violet-50 px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="fa-solid fa-qrcode text-xs"></i> QR
                                    </button>
                                    <form action="<?= base_url('admin/meja/delete/' . $m['id']) ?>" method="post" onsubmit="return confirm('Hapus meja ini?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs text-slate-600 hover:text-rose-600 bg-slate-100 hover:bg-rose-50 px-3 py-1.5 rounded-lg transition-colors">
                                            <i class="fa-solid fa-trash text-xs"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-5 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
                                        <i class="fa-solid fa-table-cells text-slate-400 text-lg"></i>
                                    </div>
                                    <p class="text-sm font-medium text-slate-600">Belum ada meja</p>
                                    <p class="text-xs text-slate-400">Tambahkan meja melalui form di samping.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modal-qr" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-80 text-center">
        <h3 class="font-semibold text-slate-800 mb-1">QR Code Meja <span id="modal-nomor"></span></h3>
        <p class="text-xs text-slate-400 mb-4">Pelanggan scan untuk mulai memesan.</p>
        <div id="qr-container" class="flex justify-center mb-4"></div>
        <p id="modal-url" class="text-xs font-mono text-slate-400 break-all mb-4"></p>
        <div class="flex gap-2">
            <button onclick="printQR()" class="flex-1 text-sm bg-sky-500 hover:bg-sky-600 text-white font-medium py-2 rounded-lg transition-colors">
                <i class="fa-solid fa-print mr-1.5"></i> Print
            </button>
            <button onclick="closeQR()" class="flex-1 text-sm bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium py-2 rounded-lg transition-colors">
                Tutup
            </button>
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
        new QRCode(container, { text: url, width: 180, height: 180, correctLevel: QRCode.CorrectLevel.H });
        document.getElementById('modal-qr').classList.remove('hidden');
    }

    function closeQR() {
        document.getElementById('modal-qr').classList.add('hidden');
    }

    function printQR() {
        const canvas = document.querySelector('#qr-container canvas');
        if (!canvas) return;
        const win = window.open('');
        win.document.write('<img src="' + canvas.toDataURL() + '" style="width:200px"><br><p>' + document.getElementById('modal-url').textContent + '</p>');
        win.print();
        win.close();
    }

    document.getElementById('modal-qr').addEventListener('click', function(e) {
        if (e.target === this) closeQR();
    });
</script>

<?= $this->endSection() ?>