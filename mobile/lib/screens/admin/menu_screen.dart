import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import '../../services/api_service.dart';

class MenuScreen extends StatefulWidget {
  const MenuScreen({super.key});

  @override
  State<MenuScreen> createState() => _MenuScreenState();
}

class _MenuScreenState extends State<MenuScreen> {
  List<dynamic> _menus = [];
  List<dynamic> _kategoris = [];
  bool _loading = false;
  String? _successBanner;

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  Future<void> _fetchData() async {
    setState(() => _loading = true);
    final results = await Future.wait([
      ApiService.getMenus(),
      ApiService.getKategoris(),
    ]);
    final menuRes = results[0];
    final katRes = results[1];

    if (mounted) {
      setState(() {
        _loading = false;
        if (menuRes['success'] == true) {
          _menus = menuRes['menus'] ?? [];
        }
        if (katRes['success'] == true) {
          _kategoris = katRes['kategoris'] ?? katRes['data'] ?? [];
        }
      });
    }
  }

  double _toDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    final cleaned = value.toString().replaceAll(RegExp(r'[^0-9.-]'), '');
    return double.tryParse(cleaned) ?? 0.0;
  }

  bool _toBool(dynamic value, {bool defaultValue = true}) {
    if (value == null) return defaultValue;
    if (value is bool) return value;
    if (value is num) return value != 0;
    final s = value.toString().trim().toLowerCase();
    if (s == '1' || s == 'true') return true;
    if (s == '0' || s == 'false') return false;
    return defaultValue;
  }

  String _formatCurrency(dynamic value) {
    final amount = _toDouble(value);
    return NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0).format(amount);
  }

  bool _isGamesCategory(int? kategoriId) {
    if (kategoriId == null) return false;
    for (final k in _kategoris) {
      if ('${k['id']}' == '$kategoriId') {
        final namaKat = (k['nama_kategori'] ?? k['nama'] ?? '').toString().toLowerCase();
        return namaKat.contains('game');
      }
    }
    return false;
  }

  void _showFormDialog({Map<String, dynamic>? existing}) {
    final namaController = TextEditingController(text: existing?['nama_menu']?.toString() ?? '');
    int? selectedKategoriId = existing?['kategori_id'] is int
        ? existing!['kategori_id']
        : int.tryParse('${existing?['kategori_id'] ?? ''}');
    final hargaController = TextEditingController(
      text: existing != null
          ? (_isGamesCategory(selectedKategoriId) ? 'Gratis' : '${existing['harga'] ?? ''}')
          : '',
    );
    final descController = TextEditingController(text: existing?['deskripsi']?.toString() ?? '');
    bool isTersedia = _toBool(existing?['stok'], defaultValue: true);
    bool isActive = _toBool(existing?['is_active'], defaultValue: true);
    XFile? pickedFile;
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
                        InkWell(
                          onTap: () => Navigator.pop(ctx),
                          borderRadius: BorderRadius.circular(8),
                          child: Container(
                            width: 32,
                            height: 32,
                            decoration: BoxDecoration(
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Icon(Icons.arrow_back, size: 16, color: Color(0xFF64748B)),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              existing == null ? 'Tambah Menu' : 'Edit Menu',
                              style: const TextStyle(
                                color: Color(0xFF1E293B),
                                fontWeight: FontWeight.bold,
                                fontSize: 18,
                              ),
                            ),
                            Text(
                              existing == null ? 'Tambahkan item menu baru.' : 'Perbarui item menu.',
                              style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // NAMA MENU *
                    const Text(
                      'NAMA MENU *',
                      style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: namaController,
                      style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                      decoration: InputDecoration(
                        hintText: 'Ayam Geprek + Es Teh',
                        hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
                        filled: true,
                        fillColor: Colors.white,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
                      ),
                      validator: (v) => (v == null || v.trim().isEmpty) ? 'Nama menu wajib diisi' : null,
                    ),
                    const SizedBox(height: 16),

                    // Row: HARGA * & KATEGORI
                    Builder(
                      builder: (_) {
                        final isGames = _isGamesCategory(selectedKategoriId);
                        return Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    isGames ? 'HARGA (GRATIS)' : 'HARGA *',
                                    style: TextStyle(
                                      color: isGames ? const Color(0xFF16A34A) : const Color(0xFF64748B),
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  const SizedBox(height: 6),
                                  TextFormField(
                                    controller: hargaController,
                                    enabled: !isGames,
                                    keyboardType: TextInputType.number,
                                    style: TextStyle(
                                      color: isGames ? const Color(0xFF16A34A) : const Color(0xFF1E293B),
                                      fontSize: 13,
                                      fontWeight: isGames ? FontWeight.bold : FontWeight.normal,
                                    ),
                                    decoration: InputDecoration(
                                      hintText: isGames ? 'Gratis (Rp 0)' : 'Rp 20000',
                                      hintStyle: TextStyle(
                                        color: isGames ? const Color(0xFF16A34A) : const Color(0xFF94A3B8),
                                        fontSize: 13,
                                        fontWeight: isGames ? FontWeight.bold : FontWeight.normal,
                                      ),
                                      filled: true,
                                      fillColor: isGames ? const Color(0xFFF0FDF4) : Colors.white,
                                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                                      disabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFBBF7D0))),
                                      focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
                                    ),
                                    validator: (v) {
                                      if (_isGamesCategory(selectedKategoriId)) return null;
                                      return (v == null || v.trim().isEmpty) ? 'Harga wajib diisi' : null;
                                    },
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('KATEGORI', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                                  const SizedBox(height: 6),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 12),
                                    decoration: BoxDecoration(
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(color: const Color(0xFFCBD5E1)),
                                    ),
                                    child: DropdownButtonHideUnderline(
                                      child: DropdownButton<int?>(
                                        value: selectedKategoriId,
                                        isExpanded: true,
                                        hint: const Text('-- Pilih Kategori --', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 13)),
                                        style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                                        items: [
                                          const DropdownMenuItem<int?>(value: null, child: Text('Tanpa Kategori')),
                                          ..._kategoris.map((k) => DropdownMenuItem<int?>(
                                                value: k['id'] is int ? k['id'] : int.tryParse('${k['id']}'),
                                                child: Text((k['nama_kategori'] ?? k['nama'] ?? '-').toString()),
                                              )),
                                        ],
                                        onChanged: (val) {
                                          setDlgState(() {
                                            final wasGames = _isGamesCategory(selectedKategoriId);
                                            selectedKategoriId = val;
                                            final nowGames = _isGamesCategory(val);
                                            if (nowGames) {
                                              hargaController.text = 'Gratis';
                                            } else if (wasGames && (hargaController.text == 'Gratis' || hargaController.text == '0')) {
                                              hargaController.clear();
                                            }
                                          });
                                        },
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        );
                      },
                    ),
                    const SizedBox(height: 16),

                    // DESKRIPSI
                    const Text('DESKRIPSI', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: descController,
                      maxLines: 3,
                      style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                      decoration: InputDecoration(
                        hintText: 'Deskripsi singkat menu (opsional)',
                        hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
                        filled: true,
                        fillColor: Colors.white,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Row: STOK * & STATUS *
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('STOK *', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12),
                                decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(color: const Color(0xFFCBD5E1)),
                                ),
                                child: DropdownButtonHideUnderline(
                                  child: DropdownButton<bool>(
                                    value: isTersedia,
                                    isExpanded: true,
                                    style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                                    items: const [
                                      DropdownMenuItem(value: true, child: Text('Tersedia')),
                                      DropdownMenuItem(value: false, child: Text('Habis')),
                                    ],
                                    onChanged: (val) {
                                      if (val != null) setDlgState(() => isTersedia = val);
                                    },
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
                              const Text('STATUS *', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12),
                                decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(color: const Color(0xFFCBD5E1)),
                                ),
                                child: DropdownButtonHideUnderline(
                                  child: DropdownButton<bool>(
                                    value: isActive,
                                    isExpanded: true,
                                    style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                                    items: const [
                                      DropdownMenuItem(value: true, child: Text('Aktif')),
                                      DropdownMenuItem(value: false, child: Text('Nonaktif')),
                                    ],
                                    onChanged: (val) {
                                      if (val != null) setDlgState(() => isActive = val);
                                    },
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    // GAMBAR MENU
                    const Text('GAMBAR MENU', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        OutlinedButton(
                          style: OutlinedButton.styleFrom(
                            side: const BorderSide(color: Color(0xFFCBD5E1)),
                            foregroundColor: const Color(0xFF334155),
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          onPressed: () async {
                            final picker = ImagePicker();
                            final img = await picker.pickImage(source: ImageSource.gallery);
                            if (img != null) {
                              setDlgState(() => pickedFile = img);
                            }
                          },
                          child: const Text('Choose File', style: TextStyle(fontSize: 12)),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            pickedFile != null ? pickedFile!.name : 'No file chosen',
                            style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    const Text('Format: JPG, PNG, WebP. Maks 2MB.', style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11)),
                    const SizedBox(height: 24),

                    // Buttons
                    Row(
                      children: [
                        ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF008CFF),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                            elevation: 0,
                          ),
                          onPressed: saving
                              ? null
                              : () async {
                                  if (!formKey.currentState!.validate()) return;
                                  setDlgState(() => saving = true);

                                  try {
                                    final isGames = _isGamesCategory(selectedKategoriId);
                                    final hargaClean = hargaController.text
                                        .replaceAll(RegExp(r'[^0-9]'), '');
                                    final fields = {
                                      'nama_menu': namaController.text.trim(),
                                      'harga': isGames
                                          ? '0'
                                          : (hargaClean.isNotEmpty
                                              ? hargaClean
                                              : hargaController.text.trim()),
                                      'deskripsi': descController.text.trim(),
                                      if (selectedKategoriId != null)
                                        'kategori_id': selectedKategoriId.toString(),
                                      'stok': isTersedia ? '1' : '0',
                                      'is_active': isActive ? '1' : '0',
                                    };

                                    List<int>? imgBytes;
                                    String? imgFilename;
                                    if (pickedFile != null) {
                                      imgBytes = await pickedFile!.readAsBytes();
                                      imgFilename = pickedFile!.name.isNotEmpty
                                          ? pickedFile!.name
                                          : 'menu_${DateTime.now().millisecondsSinceEpoch}.jpg';
                                    }

                                    final res = existing == null
                                        ? await ApiService.storeMenu(
                                            fields,
                                            imageBytes: imgBytes,
                                            filename: imgFilename,
                                          )
                                        : await ApiService.updateMenu(
                                            existing['id'],
                                            fields,
                                            imageBytes: imgBytes,
                                            filename: imgFilename,
                                          );

                                    if (res['success'] == true) {
                                      if (ctx.mounted) Navigator.pop(ctx);
                                      if (mounted) {
                                        setState(() {
                                          _successBanner = existing == null
                                              ? 'Menu berhasil ditambahkan.'
                                              : 'Menu berhasil diperbarui.';
                                        });
                                        _fetchData();
                                      }
                                    } else {
                                      if (ctx.mounted) {
                                        setDlgState(() => saving = false);
                                      }
                                      if (mounted) {
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          SnackBar(
                                            content: Text(res['message']?.toString() ?? 'Gagal menyimpan menu.'),
                                            backgroundColor: Colors.redAccent,
                                          ),
                                        );
                                      }
                                    }
                                  } catch (e) {
                                    if (ctx.mounted) {
                                      setDlgState(() => saving = false);
                                    }
                                    if (mounted) {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        SnackBar(
                                          content: Text('Gagal menyimpan menu: $e'),
                                          backgroundColor: Colors.redAccent,
                                        ),
                                      );
                                    }
                                  }
                                },
                          child: saving
                              ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                              : Text(existing == null ? 'Tambah Menu' : 'Simpan Perubahan'),
                        ),
                        const SizedBox(width: 16),
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

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Kelola Menu',
                    style: TextStyle(
                      color: Color(0xFF1E293B),
                      fontWeight: FontWeight.bold,
                      fontSize: 22,
                    ),
                  ),
                  SizedBox(height: 4),
                  Text(
                    'Daftar semua item menu yang tersedia untuk pelanggan.',
                    style: TextStyle(
                      color: Color(0xFF64748B),
                      fontSize: 13,
                    ),
                  ),
                ],
              ),
              ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF008CFF),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  elevation: 0,
                ),
                icon: const Icon(Icons.add, size: 16),
                label: const Text('Tambah Menu', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                onPressed: () => _showFormDialog(),
              ),
            ],
          ),
          const SizedBox(height: 20),

          // Success Banner
          if (_successBanner != null) ...[
            Container(
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
                  Text(
                    _successBanner!,
                    style: const TextStyle(color: Color(0xFF16A34A), fontSize: 13, fontWeight: FontWeight.w500),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
          ],

          // Table Card
          Container(
            width: double.infinity,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFF1F5F9)),
              boxShadow: const [
                BoxShadow(
                  color: Color(0x08000000),
                  blurRadius: 10,
                  offset: Offset(0, 2),
                ),
              ],
            ),
            child: _loading
                ? const Padding(
                    padding: EdgeInsets.all(40),
                    child: Center(child: CircularProgressIndicator(color: Color(0xFF008CFF))),
                  )
                : _menus.isEmpty
                    ? const Padding(
                        padding: EdgeInsets.all(40),
                        child: Center(
                          child: Text(
                            'Belum ada menu.',
                            style: TextStyle(color: Color(0xFF94A3B8)),
                          ),
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
                              DataColumn(label: Text('NO')),
                              DataColumn(label: Text('MENU')),
                              DataColumn(label: Text('KATEGORI')),
                              DataColumn(label: Text('HARGA')),
                              DataColumn(label: Text('STOK')),
                              DataColumn(label: Text('STATUS')),
                              DataColumn(label: Text('AKSI')),
                            ],
                            rows: List.generate(_menus.length, (i) {
                              final m = _menus[i];
                              final id = m['id'];
                              final nama = (m['nama_menu'] ?? '-').toString();
                              final kat = (m['kategori']?['nama_kategori'] ?? m['nama_kategori'] ?? 'Uncategorized').toString();
                              final harga = m['harga'] ?? 0;
                              final isGratis = _toDouble(harga) <= 0;
                              final isTersedia = _toBool(m['stok'], defaultValue: true);
                              final isActive = _toBool(m['is_active'], defaultValue: true);

                              return DataRow(
                                cells: [
                                  DataCell(Text('${i + 1}', style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13))),
                                  DataCell(
                                    Row(
                                      children: [
                                        Container(
                                          width: 36,
                                          height: 36,
                                          decoration: BoxDecoration(
                                            color: const Color(0xFFF1F5F9),
                                            borderRadius: BorderRadius.circular(8),
                                          ),
                                          child: const Icon(Icons.restaurant, color: Color(0xFF94A3B8), size: 18),
                                        ),
                                        const SizedBox(width: 12),
                                        Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Text(nama, style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.w600, fontSize: 13)),
                                            if ((m['deskripsi'] ?? '').toString().isNotEmpty)
                                              Text(
                                                (m['deskripsi']).toString(),
                                                style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                  DataCell(
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFF1F5F9),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(kat, style: const TextStyle(color: Color(0xFF64748B), fontSize: 11.5, fontWeight: FontWeight.w500)),
                                    ),
                                  ),
                                  DataCell(
                                    isGratis
                                        ? Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                            decoration: BoxDecoration(
                                              color: const Color(0xFFF0FDF4),
                                              borderRadius: BorderRadius.circular(6),
                                              border: Border.all(color: const Color(0xFFBBF7D0)),
                                            ),
                                            child: const Text(
                                              'Gratis',
                                              style: TextStyle(color: Color(0xFF16A34A), fontWeight: FontWeight.bold, fontSize: 12),
                                            ),
                                          )
                                        : Text(_formatCurrency(harga), style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 13)),
                                  ),
                                  DataCell(
                                    InkWell(
                                      onTap: () async {
                                        await ApiService.toggleStokMenu(id);
                                        _fetchData();
                                      },
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Container(
                                            width: 8,
                                            height: 8,
                                            decoration: BoxDecoration(
                                              shape: BoxShape.circle,
                                              color: isTersedia ? const Color(0xFF008CFF) : const Color(0xFFF43F5E),
                                            ),
                                          ),
                                          const SizedBox(width: 6),
                                          Text(
                                            isTersedia ? 'Tersedia' : 'Habis',
                                            style: TextStyle(
                                              color: isTersedia ? const Color(0xFF008CFF) : const Color(0xFFF43F5E),
                                              fontSize: 12.5,
                                              fontWeight: FontWeight.w500,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                  DataCell(
                                    InkWell(
                                      onTap: () async {
                                        await ApiService.toggleMenu(id);
                                        _fetchData();
                                      },
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Container(
                                            width: 8,
                                            height: 8,
                                            decoration: BoxDecoration(
                                              shape: BoxShape.circle,
                                              color: isActive ? const Color(0xFF16A34A) : const Color(0xFFCBD5E1),
                                            ),
                                          ),
                                          const SizedBox(width: 6),
                                          Text(
                                            isActive ? 'Aktif' : 'Nonaktif',
                                            style: TextStyle(
                                              color: isActive ? const Color(0xFF334155) : const Color(0xFF94A3B8),
                                              fontSize: 12.5,
                                              fontWeight: FontWeight.w500,
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
                                        InkWell(
                                          onTap: () => _showFormDialog(existing: m),
                                          child: const Row(
                                            children: [
                                              Icon(Icons.edit_outlined, size: 14, color: Color(0xFF64748B)),
                                              SizedBox(width: 4),
                                              Text('Edit', style: TextStyle(color: Color(0xFF64748B), fontSize: 12)),
                                            ],
                                          ),
                                        ),
                                        const SizedBox(width: 14),
                                        InkWell(
                                          onTap: () async {
                                            final confirm = await showDialog<bool>(
                                              context: context,
                                              builder: (c) => AlertDialog(
                                                title: const Text('Hapus Menu'),
                                                content: Text('Yakin ingin menghapus "$nama"?'),
                                                actions: [
                                                  TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
                                                  TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Hapus', style: TextStyle(color: Colors.red))),
                                                ],
                                              ),
                                            );
                                            if (confirm == true) {
                                              await ApiService.deleteMenu(id);
                                              _fetchData();
                                            }
                                          },
                                          child: const Row(
                                            children: [
                                              Icon(Icons.delete_outline, size: 14, color: Color(0xFF64748B)),
                                              SizedBox(width: 4),
                                              Text('Hapus', style: TextStyle(color: Color(0xFF64748B), fontSize: 12)),
                                            ],
                                          ),
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
          ),
        ],
      ),
    );
  }
}