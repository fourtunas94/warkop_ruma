import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../services/api_service.dart';
import '../../widgets/app_widgets.dart';

class PromoScreen extends StatefulWidget {
  const PromoScreen({super.key});

  @override
  State<PromoScreen> createState() => _PromoScreenState();
}

class _PromoScreenState extends State<PromoScreen> {
  List<dynamic> _promos = [];
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    _fetchPromos();
  }

  Future<void> _fetchPromos() async {
    setState(() => _loading = true);
    final res = await ApiService.getPromos();
    if (mounted) {
      setState(() {
        _loading = false;
        if (res['success'] == true) {
          _promos = res['promos'] ?? [];
        }
      });
    }
  }

  String _formatCurrency(dynamic value) {
    final num = (value is int) ? value.toDouble() : (value as double? ?? 0.0);
    return NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0).format(num);
  }

  void _showFormSheet({Map<String, dynamic>? existing}) {
    final kodeController = TextEditingController(text: existing?['kode'] ?? '');
    final namaController = TextEditingController(text: existing?['nama_promo'] ?? '');
    final descController = TextEditingController(text: existing?['deskripsi'] ?? '');
    final nilaiController = TextEditingController(text: existing != null ? '${existing['nilai_diskon']}' : '');
    final maxController = TextEditingController(text: existing != null ? '${existing['maksimal_diskon']}' : '0');
    final minController = TextEditingController(text: existing != null ? '${existing['minimal_belanja']}' : '0');
    final kuotaController = TextEditingController(text: existing != null ? '${existing['kuota']}' : '0');
    String tipeDiskon = existing?['tipe_diskon'] ?? 'nominal';
    final formKey = GlobalKey<FormState>();
    bool saving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: const Color(0xFF0F2135),
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheetState) => Padding(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 16,
            left: 20,
            right: 20,
            top: 20,
          ),
          child: SingleChildScrollView(
            child: Form(
              key: formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        existing == null ? 'Tambah Promo' : 'Edit Promo',
                        style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close, color: Color(0xFF94A3B8)),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: kodeController,
                    style: const TextStyle(color: Colors.white),
                    decoration: darkInputDecoration(label: 'Kode Promo (misal: DISKON10)'),
                    validator: (v) => v == null || v.trim().isEmpty ? 'Kode promo wajib diisi' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: namaController,
                    style: const TextStyle(color: Colors.white),
                    decoration: darkInputDecoration(label: 'Nama Promo'),
                    validator: (v) => v == null || v.trim().isEmpty ? 'Nama promo wajib diisi' : null,
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    initialValue: tipeDiskon,
                    dropdownColor: const Color(0xFF0F2135),
                    style: const TextStyle(color: Colors.white),
                    decoration: darkInputDecoration(label: 'Tipe Diskon'),
                    items: const [
                      DropdownMenuItem(value: 'nominal', child: Text('Nominal (Rp)')),
                      DropdownMenuItem(value: 'persen', child: Text('Persentase (%)')),
                    ],
                    onChanged: (val) => setSheetState(() => tipeDiskon = val ?? 'nominal'),
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: nilaiController,
                    keyboardType: TextInputType.number,
                    style: const TextStyle(color: Colors.white),
                    decoration: darkInputDecoration(
                      label: tipeDiskon == 'persen' ? 'Nilai Diskon (1-100%)' : 'Nilai Diskon (Rp)',
                    ),
                    validator: (v) {
                      if (v == null || v.isEmpty) return 'Nilai diskon wajib diisi';
                      final n = int.tryParse(v);
                      if (n == null || n < 1) return 'Nilai harus lebih dari 0';
                      if (tipeDiskon == 'persen' && n > 100) return 'Persentase maksimal 100%';
                      return null;
                    },
                  ),
                  const SizedBox(height: 12),
                  if (tipeDiskon == 'persen') ...[
                    TextFormField(
                      controller: maxController,
                      keyboardType: TextInputType.number,
                      style: const TextStyle(color: Colors.white),
                      decoration: darkInputDecoration(label: 'Maksimal Diskon Rp (0 = tanpa batas)'),
                    ),
                    const SizedBox(height: 12),
                  ],
                  TextFormField(
                    controller: minController,
                    keyboardType: TextInputType.number,
                    style: const TextStyle(color: Colors.white),
                    decoration: darkInputDecoration(label: 'Minimal Belanja Rp (0 = tanpa minimal)'),
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: kuotaController,
                    keyboardType: TextInputType.number,
                    style: const TextStyle(color: Colors.white),
                    decoration: darkInputDecoration(label: 'Kuota Penggunaan (0 = tak terbatas)'),
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: descController,
                    style: const TextStyle(color: Colors.white),
                    decoration: darkInputDecoration(label: 'Deskripsi Promo (opsional)'),
                    maxLines: 2,
                  ),
                  const SizedBox(height: 20),
                  ElevatedButton(
                    onPressed: saving
                        ? null
                        : () async {
                            if (!formKey.currentState!.validate()) return;
                            setSheetState(() => saving = true);

                            final body = {
                              'kode': kodeController.text.trim().toUpperCase(),
                              'nama_promo': namaController.text.trim(),
                              'tipe_diskon': tipeDiskon,
                              'nilai_diskon': int.parse(nilaiController.text.trim()),
                              'maksimal_diskon': int.tryParse(maxController.text.trim()) ?? 0,
                              'minimal_belanja': int.tryParse(minController.text.trim()) ?? 0,
                              'kuota': int.tryParse(kuotaController.text.trim()) ?? 0,
                              'deskripsi': descController.text.trim(),
                              'is_active': existing != null ? '${existing['is_active'] ?? 1}' : '1',
                            };

                            Map<String, dynamic> res;
                            if (existing == null) {
                              res = await ApiService.storePromo(body);
                            } else {
                              res = await ApiService.updatePromo(existing['id'], body);
                            }

                            if (ctx.mounted) Navigator.pop(ctx);
                            if (res['success'] == true) {
                              _fetchPromos();
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(content: Text(res['message'] ?? 'Promo berhasil disimpan.')),
                                );
                              }
                            } else {
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(content: Text(res['message'] ?? 'Gagal menyimpan promo.')),
                                );
                              }
                            }
                          },
                    child: saving
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : Text(existing == null ? 'Tambah Promo' : 'Simpan Perubahan'),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF020617),
      appBar: AppBar(
        title: const Text('Kelola Promo'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _fetchPromos,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: const Color(0xFF008CFF),
        onPressed: () => _showFormSheet(),
        child: const Icon(Icons.add, color: Colors.white),
      ),
      body: RefreshIndicator(
        onRefresh: _fetchPromos,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: Color(0xFF008CFF)))
            : _promos.isEmpty
                ? const Center(child: Text('Belum ada promo.', style: TextStyle(color: Color(0xFF94A3B8))))
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _promos.length,
                    itemBuilder: (ctx, i) {
                      final p = _promos[i];
                      final bool isActive = (p['is_active'] ?? 1) == 1;
                      final isPersen = p['tipe_diskon'] == 'persen';

                      return AppCard(
                        margin: const EdgeInsets.only(bottom: 12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: const Color(0x3300E5FF),
                                    borderRadius: BorderRadius.circular(6),
                                    border: Border.all(color: const Color(0xFF00E5FF)),
                                  ),
                                  child: Text(
                                    p['kode'] ?? '-',
                                    style: const TextStyle(
                                      color: Color(0xFF00E5FF),
                                      fontWeight: FontWeight.bold,
                                      letterSpacing: 1,
                                    ),
                                  ),
                                ),
                                Row(
                                  children: [
                                    InkWell(
                                      onTap: () async {
                                        await ApiService.togglePromo(p['id']);
                                        _fetchPromos();
                                      },
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                        decoration: BoxDecoration(
                                          color: isActive ? const Color(0x3322C55E) : const Color(0x3364748B),
                                          borderRadius: BorderRadius.circular(4),
                                        ),
                                        child: Text(
                                          isActive ? 'Aktif' : 'Nonaktif',
                                          style: TextStyle(
                                            color: isActive ? const Color(0xFF22C55E) : const Color(0xFF94A3B8),
                                            fontSize: 11,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 4),
                                    IconButton(
                                      icon: const Icon(Icons.edit_outlined, color: Color(0xFF008CFF), size: 18),
                                      onPressed: () => _showFormSheet(existing: p),
                                    ),
                                    IconButton(
                                      icon: const Icon(Icons.delete_outline, color: Color(0xFFF43F5E), size: 18),
                                      onPressed: () => ConfirmDialog.show(
                                        context,
                                        title: 'Hapus Promo',
                                        message: 'Yakin ingin menghapus promo ${p['kode']}?',
                                        onConfirm: () async {
                                          await ApiService.deletePromo(p['id']);
                                          _fetchPromos();
                                        },
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(
                              p['nama_promo'] ?? '-',
                              style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              isPersen
                                  ? 'Diskon ${p['nilai_diskon']}%${(p['maksimal_diskon'] ?? 0) > 0 ? ' (Maks. ${_formatCurrency(p['maksimal_diskon'])})' : ''}'
                                  : 'Potongan ${_formatCurrency(p['nilai_diskon'])}',
                              style: const TextStyle(color: Color(0xFF22C55E), fontWeight: FontWeight.bold),
                            ),
                            if ((p['minimal_belanja'] ?? 0) > 0) ...[
                              const SizedBox(height: 2),
                              Text(
                                'Min. belanja: ${_formatCurrency(p['minimal_belanja'])}',
                                style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                              ),
                            ],
                            const SizedBox(height: 6),
                            Row(
                              children: [
                                const Icon(Icons.people_outline, color: Color(0xFF64748B), size: 14),
                                const SizedBox(width: 4),
                                Text(
                                  'Digunakan: ${p['jumlah_digunakan'] ?? 0}${(p['kuota'] ?? 0) > 0 ? ' / ${p['kuota']}' : ' (Unlimited)'}',
                                  style: const TextStyle(color: Color(0xFF64748B), fontSize: 11),
                                ),
                              ],
                            ),
                          ],
                        ),
                      );
                    },
                  ),
      ),
    );
  }
}
