import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import '../../services/api_service.dart';

class StokScreen extends StatefulWidget {
  const StokScreen({super.key});

  @override
  State<StokScreen> createState() => _StokScreenState();
}

class _StokScreenState extends State<StokScreen> {
  List<dynamic> _stokList = [];
  List<dynamic> _riwayatPembelian = [];
  Map<String, dynamic> _summary = {
    'total_items': 0,
    'stok_menipis': 0,
    'total_pengeluaran': 0,
  };

  bool _loading = false;
  int _currentTab = 0; // 0: Daftar Stok, 1: Riwayat Pembelian
  String _filterStatus = 'semua'; // 'semua', 'aman', 'menipis', 'habis'
  String? _successBanner;

  @override
  void initState() {
    super.initState();
    _fetchStokData();
    _fetchRiwayatPembelian();
  }

  double _parseDouble(dynamic value, {double fallback = 0.0}) {
    if (value == null) return fallback;
    if (value is num) return value.toDouble();
    final cleaned = value.toString().replaceAll(',', '.').trim();
    return double.tryParse(cleaned) ?? fallback;
  }

  Future<void> _fetchStokData() async {
    setState(() => _loading = true);
    final res = await ApiService.getStok();
    if (mounted) {
      setState(() {
        _loading = false;
        if (res['success'] == true) {
          _stokList = res['data'] ?? [];
          if (res['summary'] != null) {
            _summary = Map<String, dynamic>.from(res['summary']);
          }
        }
      });
    }
  }

  Future<void> _fetchRiwayatPembelian() async {
    final res = await ApiService.getRiwayatPembelianStok();
    if (mounted && res['success'] == true) {
      setState(() {
        _riwayatPembelian = res['data'] ?? [];
      });
    }
  }

  String _formatCurrency(dynamic value) {
    final amount = _parseDouble(value, fallback: 0.0);
    return NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0).format(amount);
  }

  List<dynamic> get _filteredStokList {
    if (_filterStatus == 'semua') return _stokList;

    return _stokList.where((item) {
      final stok = _parseDouble(item['stok'], fallback: 0.0);
      final minStok = _parseDouble(item['minimum_stok'], fallback: 0.0);

      if (_filterStatus == 'habis') return stok <= 0;
      if (_filterStatus == 'menipis') return stok > 0 && stok <= minStok;
      if (_filterStatus == 'aman') return stok > minStok;
      return true;
    }).toList();
  }

  // ─── MODAL CATAT PEMBELIAN / RESTOCK DENGAN BUKTI FOTO ───────────────────────
  void _showCatatPembelianDialog({Map<String, dynamic>? preselectedBarang}) {
    int? selectedBarangId = preselectedBarang?['id'];
    final jumlahController = TextEditingController();
    final biayaController = TextEditingController();
    final supplierController = TextEditingController(text: preselectedBarang?['supplier'] ?? '');
    final catatanController = TextEditingController();
    DateTime tanggalPembelian = DateTime.now();
    XFile? pickedPhoto;
    Uint8List? photoBytes;

    final formKey = GlobalKey<FormState>();
    bool saving = false;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) => AlertDialog(
          backgroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          contentPadding: const EdgeInsets.all(24),
          content: SizedBox(
            width: 540,
            child: SingleChildScrollView(
              child: Form(
                key: formKey,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(
                            color: const Color(0xFFF0FDF4),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.add_shopping_cart, color: Color(0xFF16A34A), size: 20),
                        ),
                        const SizedBox(width: 12),
                        const Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Catat Pembelian & Restock',
                              style: TextStyle(
                                color: Color(0xFF1E293B),
                                fontWeight: FontWeight.bold,
                                fontSize: 17,
                              ),
                            ),
                            Text(
                              'Tambah stok barang dengan bukti foto pembelian.',
                              style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // Pilih Barang
                    const Text('PILIH BARANG *', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14),
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: const Color(0xFFCBD5E1)),
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<int>(
                          value: selectedBarangId,
                          hint: const Text('-- Pilih Barang yang Dibeli --', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 13)),
                          isExpanded: true,
                          style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                          items: _stokList.map((item) {
                            return DropdownMenuItem<int>(
                              value: item['id'],
                              child: Text('${item['nama_barang']} (Stok: ${item['stok']} ${item['satuan']})'),
                            );
                          }).toList(),
                          onChanged: (val) {
                            setDlgState(() {
                              selectedBarangId = val;
                              final found = _stokList.firstWhere((b) => b['id'] == val, orElse: () => null);
                              if (found != null && (found['supplier'] ?? '').toString().isNotEmpty) {
                                supplierController.text = found['supplier'];
                              }
                            });
                          },
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Row: Jumlah & Total Biaya
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('JUMLAH MASUK *', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: jumlahController,
                                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                                decoration: InputDecoration(
                                  hintText: 'Contoh: 10 atau 2.5',
                                  hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                                  filled: true,
                                  fillColor: Colors.white,
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                  focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
                                ),
                                validator: (v) => (v == null || v.trim().isEmpty) ? 'Wajib diisi' : null,
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('TOTAL BIAYA PEMBELIAN *', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: biayaController,
                                keyboardType: TextInputType.number,
                                style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                                decoration: InputDecoration(
                                  hintText: 'Rp 150000',
                                  hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                                  filled: true,
                                  fillColor: Colors.white,
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                  focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
                                ),
                                validator: (v) => (v == null || v.trim().isEmpty) ? 'Wajib diisi' : null,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    // Row: Tanggal & Supplier
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('TANGGAL PEMBELIAN *', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              InkWell(
                                onTap: () async {
                                  final picked = await showDatePicker(
                                    context: ctx,
                                    initialDate: tanggalPembelian,
                                    firstDate: DateTime(2020),
                                    lastDate: DateTime(2030),
                                  );
                                  if (picked != null) {
                                    setDlgState(() => tanggalPembelian = picked);
                                  }
                                },
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                  decoration: BoxDecoration(
                                    border: Border.all(color: const Color(0xFFCBD5E1)),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        DateFormat('dd MMMM yyyy').format(tanggalPembelian),
                                        style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                                      ),
                                      const Icon(Icons.calendar_today, size: 16, color: Color(0xFF64748B)),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('SUPPLIER / TOKO', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: supplierController,
                                style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                                decoration: InputDecoration(
                                  hintText: 'Nama Toko / Distributor',
                                  hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                                  filled: true,
                                  fillColor: Colors.white,
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                  focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    // UPLOAD FOTO BUKTI PEMBELIAN / STRUK NOTA
                    const Text('FOTO BUKTI PEMBELIAN / STRUK NOTA', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),

                    if (photoBytes != null) ...[
                      Stack(
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(10),
                            child: Image.memory(
                              photoBytes!,
                              height: 140,
                              width: double.infinity,
                              fit: BoxFit.cover,
                            ),
                          ),
                          Positioned(
                            top: 8,
                            right: 8,
                            child: InkWell(
                              onTap: () {
                                setDlgState(() {
                                  pickedPhoto = null;
                                  photoBytes = null;
                                });
                              },
                              child: Container(
                                padding: const EdgeInsets.all(4),
                                decoration: const BoxDecoration(
                                  color: Colors.black54,
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.close, color: Colors.white, size: 16),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                    ],

                    Row(
                      children: [
                        OutlinedButton.icon(
                          style: OutlinedButton.styleFrom(
                            side: const BorderSide(color: Color(0xFFCBD5E1)),
                            foregroundColor: const Color(0xFF334155),
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          icon: const Icon(Icons.photo_library_outlined, size: 16),
                          label: const Text('Pilih dari Galeri', style: TextStyle(fontSize: 12.5)),
                          onPressed: () async {
                            final picker = ImagePicker();
                            final img = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
                            if (img != null) {
                              final bytes = await img.readAsBytes();
                              setDlgState(() {
                                pickedPhoto = img;
                                photoBytes = bytes;
                              });
                            }
                          },
                        ),
                        const SizedBox(width: 10),
                        OutlinedButton.icon(
                          style: OutlinedButton.styleFrom(
                            side: const BorderSide(color: Color(0xFFCBD5E1)),
                            foregroundColor: const Color(0xFF334155),
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          icon: const Icon(Icons.camera_alt_outlined, size: 16),
                          label: const Text('Kamera', style: TextStyle(fontSize: 12.5)),
                          onPressed: () async {
                            final picker = ImagePicker();
                            final img = await picker.pickImage(source: ImageSource.camera, imageQuality: 85);
                            if (img != null) {
                              final bytes = await img.readAsBytes();
                              setDlgState(() {
                                pickedPhoto = img;
                                photoBytes = bytes;
                              });
                            }
                          },
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    const Text('Format: JPG, PNG, WebP. Maks 5MB. Bukti struk/nota pembelian barang.', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11)),
                    const SizedBox(height: 16),

                    // Catatan
                    const Text('CATATAN PEMBELIAN', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: catatanController,
                      maxLines: 2,
                      style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                      decoration: InputDecoration(
                        hintText: 'Keterangan tambahan (opsional)',
                        hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                        filled: true,
                        fillColor: Colors.white,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Buttons
                    Row(
                      children: [
                        ElevatedButton.icon(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF008CFF),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                            elevation: 0,
                          ),
                          icon: saving
                              ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                              : const Icon(Icons.check, size: 16),
                          label: Text(saving ? 'Menyimpan...' : 'Simpan Pembelian & Tambah Stok', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                          onPressed: saving
                              ? null
                              : () async {
                                  if (selectedBarangId == null) {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      const SnackBar(content: Text('Pilih barang terlebih dahulu.')),
                                    );
                                    return;
                                  }
                                  if (!formKey.currentState!.validate()) return;
                                  setDlgState(() => saving = true);

                                  final fields = {
                                    'stok_barang_id': selectedBarangId.toString(),
                                    'jumlah': jumlahController.text.trim(),
                                    'total_biaya': biayaController.text.trim(),
                                    'tanggal_pembelian': DateFormat('yyyy-MM-dd').format(tanggalPembelian),
                                    'supplier': supplierController.text.trim(),
                                    'catatan': catatanController.text.trim(),
                                  };

                                  File? fileObj;
                                  if (!kIsWeb && pickedPhoto != null) {
                                    fileObj = File(pickedPhoto!.path);
                                  }

                                  final res = await ApiService.catatPembelianStok(
                                    fields,
                                    imageBytes: photoBytes,
                                    filename: pickedPhoto?.name,
                                    imageFile: fileObj,
                                  );

                                  if (ctx.mounted) Navigator.pop(ctx);
                                  if (res['success'] == true) {
                                    setState(() {
                                      _successBanner = 'Pembelian barang berhasil dicatat dan stok bertambah!';
                                    });
                                    _fetchStokData();
                                    _fetchRiwayatPembelian();
                                  } else {
                                    if (mounted) {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        SnackBar(content: Text(res['message'] ?? 'Gagal mencatat pembelian.')),
                                      );
                                    }
                                  }
                                },
                        ),
                        const SizedBox(width: 14),
                        TextButton(
                          onPressed: () => Navigator.pop(ctx),
                          child: const Text('Batal', style: TextStyle(color: Color(0xFF64748B))),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  // ─── MODAL TAMBAH / EDIT BARANG INVENTARIS ──────────────────────────────────
  void _showBarangDialog({Map<String, dynamic>? existing}) {
    final namaController = TextEditingController(text: existing?['nama_barang'] ?? '');
    final kategoriController = TextEditingController(text: existing?['kategori'] ?? 'Bahan Baku');
    final stokController = TextEditingController(text: existing != null ? '${existing['stok']}' : '0');
    final satuanController = TextEditingController(text: existing?['satuan'] ?? 'pcs');
    final minStokController = TextEditingController(text: existing != null ? '${existing['minimum_stok']}' : '5');
    final supplierController = TextEditingController(text: existing?['supplier'] ?? '');
    final catatanController = TextEditingController(text: existing?['catatan'] ?? '');

    final formKey = GlobalKey<FormState>();
    bool saving = false;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) => AlertDialog(
          backgroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          contentPadding: const EdgeInsets.all(24),
          content: SizedBox(
            width: 500,
            child: SingleChildScrollView(
              child: Form(
                key: formKey,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      existing == null ? 'Tambah Barang Inventaris Baru' : 'Edit Barang Inventaris',
                      style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 18),
                    ),
                    const SizedBox(height: 4),
                    const Text('Atur data bahan baku, kemasan, dan batas minimum stok.', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12)),
                    const SizedBox(height: 20),

                    // NAMA BARANG *
                    const Text('NAMA BARANG *', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: namaController,
                      style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                      decoration: InputDecoration(
                        hintText: 'Contoh: Biji Kopi Robusta, Gula Pasir, Cup 16oz',
                        hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                        filled: true,
                        fillColor: Colors.white,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
                      ),
                      validator: (v) => (v == null || v.trim().isEmpty) ? 'Nama barang wajib diisi' : null,
                    ),
                    const SizedBox(height: 14),

                    // Row: Kategori & Satuan
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('KATEGORI', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: kategoriController,
                                style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                                decoration: InputDecoration(
                                  hintText: 'Bahan Baku / Kemasan',
                                  hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                                  filled: true,
                                  fillColor: Colors.white,
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                  focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('SATUAN *', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: satuanController,
                                style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                                decoration: InputDecoration(
                                  hintText: 'kg / liter / pcs / pack',
                                  hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                                  filled: true,
                                  fillColor: Colors.white,
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                  focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
                                ),
                                validator: (v) => (v == null || v.trim().isEmpty) ? 'Wajib diisi' : null,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Row: Stok Awal & Minimum Stok
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('STOK SAAT INI', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: stokController,
                                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                                decoration: InputDecoration(
                                  hintText: '0',
                                  filled: true,
                                  fillColor: Colors.white,
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                  focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('MINIMUM STOK ALERT *', style: TextStyle(color: Color(0xFFE11D48), fontSize: 11, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              TextFormField(
                                controller: minStokController,
                                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13, fontWeight: FontWeight.bold),
                                decoration: InputDecoration(
                                  hintText: '5',
                                  helperText: 'Peringatan jika stok <= angka ini',
                                  helperStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 10.5),
                                  filled: true,
                                  fillColor: Colors.white,
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                  focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
                                ),
                                validator: (v) => (v == null || v.trim().isEmpty) ? 'Wajib diisi' : null,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Supplier & Catatan
                    const Text('SUPPLIER REKOMENDASI', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: supplierController,
                      style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                      decoration: InputDecoration(
                        hintText: 'Nama supplier / distributor langganan',
                        hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                        filled: true,
                        fillColor: Colors.white,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Action Buttons
                    Row(
                      children: [
                        ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF008CFF),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                            elevation: 0,
                          ),
                          onPressed: saving
                              ? null
                              : () async {
                                  if (!formKey.currentState!.validate()) return;
                                  setDlgState(() => saving = true);

                                  final body = {
                                    'nama_barang': namaController.text.trim(),
                                    'kategori': kategoriController.text.trim(),
                                    'stok': _parseDouble(stokController.text.trim(), fallback: 0.0),
                                    'satuan': satuanController.text.trim(),
                                    'minimum_stok': _parseDouble(minStokController.text.trim(), fallback: 5.0),
                                    'supplier': supplierController.text.trim(),
                                    'catatan': catatanController.text.trim(),
                                  };

                                  final res = existing == null
                                      ? await ApiService.storeStok(body)
                                      : await ApiService.updateStok(existing['id'], body);

                                  if (ctx.mounted) Navigator.pop(ctx);
                                  if (res['success'] == true) {
                                    setState(() {
                                      _successBanner = existing == null ? 'Barang stok baru berhasil ditambahkan.' : 'Barang stok berhasil diperbarui.';
                                    });
                                    _fetchStokData();
                                  }
                                },
                          child: saving
                              ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                              : Text(existing == null ? 'Tambah Barang' : 'Simpan Perubahan'),
                        ),
                        const SizedBox(width: 14),
                        TextButton(
                          onPressed: () => Navigator.pop(ctx),
                          child: const Text('Batal', style: TextStyle(color: Color(0xFF64748B))),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  // ─── MODAL ZOOM LIHAT FOTO STRUK ───────────────────────────────────────────
  void _showFotoPreviewDialog(String fotoUrl, String title) {
    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: Colors.transparent,
        child: Container(
          constraints: const BoxConstraints(maxWidth: 600),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    title,
                    style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 16),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: Image.network(
                  fotoUrl,
                  fit: BoxFit.contain,
                  loadingBuilder: (c, child, progress) {
                    if (progress == null) return child;
                    return const SizedBox(
                      height: 250,
                      child: Center(child: CircularProgressIndicator(color: Color(0xFF008CFF))),
                    );
                  },
                  errorBuilder: (_, __, ___) => const SizedBox(
                    height: 200,
                    child: Center(child: Text('Foto bukti nota tidak dapat dimuat.')),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row (Responsif HP & Desktop)
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Kelola Stok Barang & Pembelian',
                      style: TextStyle(
                        color: Color(0xFF1E293B),
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    SizedBox(height: 4),
                    Text(
                      'Manajemen inventaris bahan baku, peringatan minimum stok, dan bukti foto pembelian.',
                      style: TextStyle(color: Color(0xFF64748B), fontSize: 12.5),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 10),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Color(0xFFCBD5E1)),
                      foregroundColor: const Color(0xFF334155),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    icon: const Icon(Icons.add_box_outlined, size: 16),
                    label: const Text('+ Tambah Barang Baru', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 12.5)),
                    onPressed: () => _showBarangDialog(),
                  ),
                  ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF008CFF),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      elevation: 0,
                    ),
                    icon: const Icon(Icons.shopping_cart_outlined, size: 16),
                    label: const Text('Catat Pembelian / Restock', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5)),
                    onPressed: () => _showCatatPembelianDialog(),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 20),

          // Banner Success
          if (_successBanner != null) ...[
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: const Color(0xFFF0FDF4),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFBBF7D0)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.check_circle, color: Color(0xFF16A34A), size: 18),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      _successBanner!,
                      style: const TextStyle(color: Color(0xFF16A34A), fontSize: 13, fontWeight: FontWeight.w500),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
          ],

          // 3 Summary Cards
          LayoutBuilder(
            builder: (ctx, constraints) {
              final availableWidth = constraints.maxWidth.isFinite
                  ? constraints.maxWidth
                  : MediaQuery.of(ctx).size.width - 48;
              final isWide = availableWidth >= 720;
              final cardWidth = isWide ? (availableWidth - 32) / 3 : availableWidth;

              return SizedBox(
                width: availableWidth,
                child: Wrap(
                  spacing: 16,
                  runSpacing: 16,
                  children: [
                    _buildStatCard(
                      title: 'TOTAL ITEM BARANG',
                      value: '${_summary['total_items'] ?? _stokList.length}',
                      subtitle: 'Bahan baku & kemasan',
                      icon: Icons.inventory_2_outlined,
                      iconBg: const Color(0xFFF0F9FF),
                      iconColor: const Color(0xFF008CFF),
                      width: cardWidth,
                    ),
                    _buildStatCard(
                      title: 'STOK MENIPIS / KRITIS',
                      value: '${_summary['stok_menipis'] ?? 0}',
                      subtitle: 'Stok <= batas minimum stok',
                      icon: Icons.warning_amber_rounded,
                      iconBg: const Color(0xFFFFF1F2),
                      iconColor: const Color(0xFFE11D48),
                      width: cardWidth,
                      alert: _parseDouble(_summary['stok_menipis'], fallback: 0.0) > 0,
                    ),
                    _buildStatCard(
                      title: 'TOTAL PENGELUARAN BELANJA',
                      value: _formatCurrency(_summary['total_pengeluaran'] ?? 0),
                      subtitle: 'Akumulasi biaya pembelian',
                      icon: Icons.payments_outlined,
                      iconBg: const Color(0xFFF0FDF4),
                      iconColor: const Color(0xFF16A34A),
                      width: cardWidth,
                    ),
                  ],
                ),
              );
            },
          ),
          const SizedBox(height: 24),

          // Tabs & Filter Bar (Responsif HP & Desktop)
          Wrap(
            alignment: WrapAlignment.spaceBetween,
            runAlignment: WrapAlignment.center,
            spacing: 12,
            runSpacing: 12,
            children: [
              Container(
                padding: const EdgeInsets.all(4),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Wrap(
                  spacing: 4,
                  runSpacing: 4,
                  children: [
                    _buildTabButton(0, 'Daftar Stok Bahan (${_stokList.length})', Icons.format_list_bulleted),
                    _buildTabButton(1, 'Riwayat Pembelian & Nota (${_riwayatPembelian.length})', Icons.receipt_long),
                  ],
                ),
              ),
              if (_currentTab == 0)
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    _buildFilterChip('semua', 'Semua'),
                    _buildFilterChip('aman', 'Stok Aman'),
                    _buildFilterChip('menipis', 'Stok Menipis (<= Min)'),
                    _buildFilterChip('habis', 'Habis (0)'),
                  ],
                ),
            ],
          ),
          const SizedBox(height: 16),

          // Main View Content
          _currentTab == 0 ? _buildStokTableCard() : _buildRiwayatPembelianTableCard(),
        ],
      ),
    );
  }

  Widget _buildTabButton(int index, String label, IconData icon) {
    final active = _currentTab == index;
    return InkWell(
      onTap: () => setState(() => _currentTab = index),
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: active ? const Color(0xFF008CFF) : Colors.transparent,
          borderRadius: BorderRadius.circular(8),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 15, color: active ? Colors.white : const Color(0xFF64748B)),
            const SizedBox(width: 8),
            Text(
              label,
              style: TextStyle(
                color: active ? Colors.white : const Color(0xFF64748B),
                fontWeight: active ? FontWeight.bold : FontWeight.w500,
                fontSize: 12.5,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChip(String key, String label) {
    final active = _filterStatus == key;
    return InkWell(
      onTap: () => setState(() => _filterStatus = key),
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: active ? const Color(0xFF1E293B) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: active ? const Color(0xFF1E293B) : const Color(0xFFE2E8F0)),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: active ? Colors.white : const Color(0xFF64748B),
            fontSize: 11.5,
            fontWeight: active ? FontWeight.bold : FontWeight.w500,
          ),
        ),
      ),
    );
  }

  Widget _buildStatCard({
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color iconBg,
    required Color iconColor,
    required double width,
    bool alert = false,
  }) {
    return Container(
      width: width,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: alert ? const Color(0xFFFECDD3) : const Color(0xFFF1F5F9)),
        boxShadow: const [
          BoxShadow(color: Color(0x08000000), blurRadius: 10, offset: Offset(0, 2)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  title,
                  style: TextStyle(
                    color: alert ? const Color(0xFFE11D48) : const Color(0xFF94A3B8),
                    fontSize: 10.5,
                    fontWeight: FontWeight.bold,
                    letterSpacing: 0.5,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: iconBg,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, color: iconColor, size: 16),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            value,
            style: TextStyle(
              color: alert ? const Color(0xFFE11D48) : const Color(0xFF1E293B),
              fontSize: 22,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          Text(subtitle, style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11)),
        ],
      ),
    );
  }

  // ─── TAB 1: TABEL DAFTAR STOK BARANG ────────────────────────────────────────
  Widget _buildStokTableCard() {
    final list = _filteredStokList;

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFF1F5F9)),
        boxShadow: const [
          BoxShadow(color: Color(0x08000000), blurRadius: 10, offset: Offset(0, 2)),
        ],
      ),
      child: _loading
          ? const Padding(padding: EdgeInsets.all(40), child: Center(child: CircularProgressIndicator(color: Color(0xFF008CFF))))
          : list.isEmpty
              ? const Padding(
                  padding: EdgeInsets.all(40),
                  child: Center(child: Text('Tidak ada barang inventaris sesuai filter.', style: TextStyle(color: Color(0xFF94A3B8)))),
                )
              : ClipRRect(
                  borderRadius: BorderRadius.circular(14),
                  child: SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: DataTable(
                      headingRowHeight: 48,
                      dataRowMaxHeight: 60,
                      horizontalMargin: 20,
                      columnSpacing: 28,
                      headingTextStyle: const TextStyle(
                        color: Color(0xFF94A3B8),
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        letterSpacing: 0.5,
                      ),
                      columns: const [
                        DataColumn(label: Text('NO')),
                        DataColumn(label: Text('NAMA BARANG')),
                        DataColumn(label: Text('KATEGORI')),
                        DataColumn(label: Text('STOK SAAT INI')),
                        DataColumn(label: Text('MINIMUM STOK')),
                        DataColumn(label: Text('STATUS')),
                        DataColumn(label: Text('AKSI')),
                      ],
                      rows: List.generate(list.length, (i) {
                        final item = list[i];
                        final id = item['id'];
                        final nama = (item['nama_barang'] ?? '-').toString();
                        final kategori = (item['kategori'] ?? 'Bahan Baku').toString();
                        final stok = _parseDouble(item['stok'], fallback: 0.0);
                        final satuan = (item['satuan'] ?? 'pcs').toString();
                        final minStok = _parseDouble(item['minimum_stok'], fallback: 0.0);

                        final bool isHabis = stok <= 0;
                        final bool isMenipis = stok > 0 && stok <= minStok;

                        return DataRow(
                          cells: [
                            DataCell(Text('${i + 1}', style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13))),
                            DataCell(
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Text(nama, style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 13)),
                                  if ((item['supplier'] ?? '').toString().isNotEmpty)
                                    Text('Supplier: ${item['supplier']}', style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11)),
                                ],
                              ),
                            ),
                            DataCell(
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF1F5F9),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(kategori, style: const TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.w500)),
                              ),
                            ),
                            DataCell(
                              Text(
                                '${stok.toStringAsFixed(stok.truncateToDouble() == stok ? 0 : 1)} $satuan',
                                style: TextStyle(
                                  color: isHabis ? const Color(0xFFE11D48) : (isMenipis ? const Color(0xFFD97706) : const Color(0xFF1E293B)),
                                  fontWeight: FontWeight.bold,
                                  fontSize: 13.5,
                                ),
                              ),
                            ),
                            DataCell(
                              Text(
                                '${minStok.toStringAsFixed(minStok.truncateToDouble() == minStok ? 0 : 1)} $satuan',
                                style: const TextStyle(color: Color(0xFF64748B), fontSize: 12.5),
                              ),
                            ),
                            DataCell(
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(
                                  color: isHabis
                                      ? const Color(0xFFFFF1F2)
                                      : (isMenipis ? const Color(0xFFFFFBEB) : const Color(0xFFF0FDF4)),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      isHabis ? Icons.cancel : (isMenipis ? Icons.warning_amber_rounded : Icons.check_circle),
                                      size: 13,
                                      color: isHabis ? const Color(0xFFE11D48) : (isMenipis ? const Color(0xFFD97706) : const Color(0xFF16A34A)),
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      isHabis ? 'Habis' : (isMenipis ? 'Stok Menipis' : 'Aman'),
                                      style: TextStyle(
                                        color: isHabis ? const Color(0xFFE11D48) : (isMenipis ? const Color(0xFFD97706) : const Color(0xFF16A34A)),
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                            DataCell(
                              Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  ElevatedButton.icon(
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: const Color(0xFFF0F9FF),
                                      foregroundColor: const Color(0xFF008CFF),
                                      elevation: 0,
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                      minimumSize: Size.zero,
                                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                                    ),
                                    icon: const Icon(Icons.add_shopping_cart, size: 13),
                                    label: const Text('+ Restock', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                                    onPressed: () => _showCatatPembelianDialog(preselectedBarang: item),
                                  ),
                                  const SizedBox(width: 8),
                                  OutlinedButton(
                                    style: OutlinedButton.styleFrom(
                                      side: const BorderSide(color: Color(0xFFCBD5E1)),
                                      foregroundColor: const Color(0xFF475569),
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                      minimumSize: Size.zero,
                                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                                    ),
                                    onPressed: () => _showBarangDialog(existing: item),
                                    child: const Text('Edit', style: TextStyle(fontSize: 11)),
                                  ),
                                  const SizedBox(width: 6),
                                  IconButton(
                                    icon: const Icon(Icons.delete_outline, size: 16, color: Color(0xFF94A3B8)),
                                    onPressed: () async {
                                      final confirm = await showDialog<bool>(
                                        context: context,
                                        builder: (c) => AlertDialog(
                                          title: const Text('Hapus Barang'),
                                          content: Text('Yakin ingin menghapus "$nama"?'),
                                          actions: [
                                            TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
                                            TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Hapus', style: TextStyle(color: Colors.red))),
                                          ],
                                        ),
                                      );
                                      if (confirm == true) {
                                        await ApiService.deleteStok(id);
                                        _fetchStokData();
                                      }
                                    },
                                  ),
                                ],
                              ),
                            ),
                          ],
                        );
                      }),
                    ),
                  ),
                ),
    );
  }

  // ─── TAB 2: TABEL RIWAYAT PEMBELIAN DENGAN BUKTI FOTO ───────────────────────
  Widget _buildRiwayatPembelianTableCard() {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFF1F5F9)),
        boxShadow: const [
          BoxShadow(color: Color(0x08000000), blurRadius: 10, offset: Offset(0, 2)),
        ],
      ),
      child: _riwayatPembelian.isEmpty
          ? const Padding(
              padding: EdgeInsets.all(40),
              child: Center(
                child: Text('Belum ada riwayat pembelian / restock barang.', style: TextStyle(color: Color(0xFF94A3B8))),
              ),
            )
          : ClipRRect(
              borderRadius: BorderRadius.circular(14),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: DataTable(
                  headingRowHeight: 48,
                  dataRowMaxHeight: 64,
                  horizontalMargin: 20,
                  columnSpacing: 28,
                  headingTextStyle: const TextStyle(
                    color: Color(0xFF94A3B8),
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    letterSpacing: 0.5,
                  ),
                  columns: const [
                    DataColumn(label: Text('TANGGAL')),
                    DataColumn(label: Text('BARANG DIBELI')),
                    DataColumn(label: Text('JUMLAH MASUK')),
                    DataColumn(label: Text('TOTAL BIAYA')),
                    DataColumn(label: Text('SUPPLIER')),
                    DataColumn(label: Text('FOTO BUKTI NOTA')),
                  ],
                  rows: List.generate(_riwayatPembelian.length, (i) {
                    final item = _riwayatPembelian[i];
                    final tanggal = (item['tanggal_pembelian'] ?? '-').toString();
                    final namaBarang = (item['nama_barang'] ?? '-').toString();
                    final satuan = (item['satuan'] ?? 'pcs').toString();
                    final jumlah = item['jumlah'] ?? 0;
                    final totalBiaya = item['total_biaya'] ?? 0;
                    final supplier = (item['supplier'] ?? '-').toString();
                    final fotoUrl = item['foto_nota_url'] as String?;

                    return DataRow(
                      cells: [
                        DataCell(Text(tanggal, style: const TextStyle(color: Color(0xFF64748B), fontSize: 12.5))),
                        DataCell(
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Text(namaBarang, style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 13)),
                              if ((item['catatan'] ?? '').toString().isNotEmpty)
                                Text((item['catatan']).toString(), style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11)),
                            ],
                          ),
                        ),
                        DataCell(
                          Text(
                            '+ $jumlah $satuan',
                            style: const TextStyle(color: Color(0xFF16A34A), fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                        ),
                        DataCell(
                          Text(
                            _formatCurrency(totalBiaya),
                            style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                        ),
                        DataCell(Text(supplier, style: const TextStyle(color: Color(0xFF64748B), fontSize: 12.5))),
                        DataCell(
                          fotoUrl != null
                              ? InkWell(
                                  onTap: () => _showFotoPreviewDialog(fotoUrl, 'Bukti Nota: $namaBarang'),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      ClipRRect(
                                        borderRadius: BorderRadius.circular(6),
                                        child: Image.network(
                                          fotoUrl,
                                          width: 36,
                                          height: 36,
                                          fit: BoxFit.cover,
                                          errorBuilder: (_, __, ___) => const Icon(Icons.receipt, color: Color(0xFF008CFF)),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      const Text(
                                        'Lihat Nota',
                                        style: TextStyle(color: Color(0xFF008CFF), fontSize: 11.5, fontWeight: FontWeight.bold),
                                      ),
                                    ],
                                  ),
                                )
                              : const Text(
                                  'Tanpa Foto',
                                  style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11.5),
                                ),
                        ),
                      ],
                    );
                  }),
                ),
              ),
            ),
    );
  }
}