import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class KategoriScreen extends StatefulWidget {
  const KategoriScreen({super.key});

  @override
  State<KategoriScreen> createState() => _KategoriScreenState();
}

class _KategoriScreenState extends State<KategoriScreen> {
  List<dynamic> _kategoriList = [];
  bool _loading = false;
  String? _successBanner;

  @override
  void initState() {
    super.initState();
    _fetchKategori();
  }

  Future<void> _fetchKategori() async {
    setState(() => _loading = true);
    final result = await ApiService.getKategoris();
    if (mounted) {
      setState(() {
        _loading = false;
        if (result['success'] == true) {
          _kategoriList = (result['kategoris'] ?? result['data'] ?? []) as List<dynamic>;
        }
      });
    }
  }

  void _showFormDialog({Map<String, dynamic>? existing}) {
    final currentName = existing?['nama_kategori'] ?? existing?['nama'] ?? '';
    final currentDesc = existing?['deskripsi'] ?? '';
    final currentActive = (existing?['is_active'] ?? 1) == 1;

    final nameController = TextEditingController(text: currentName);
    final descController = TextEditingController(text: currentDesc);
    bool isActive = currentActive;
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
            width: 480,
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
                            existing == null ? 'Tambah Kategori' : 'Edit Kategori',
                            style: const TextStyle(
                              color: Color(0xFF1E293B),
                              fontWeight: FontWeight.bold,
                              fontSize: 18,
                            ),
                          ),
                          Text(
                            existing == null ? 'Buat kategori menu baru.' : 'Perbarui kategori menu.',
                            style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                          ),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // NAMA KATEGORI *
                  const Text(
                    'NAMA KATEGORI *',
                    style: TextStyle(
                      color: Color(0xFF64748B),
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 6),
                  TextFormField(
                    controller: nameController,
                    style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                    decoration: InputDecoration(
                      hintText: 'Contoh: Minuman, Makanan Utama',
                      hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
                      filled: true,
                      fillColor: Colors.white,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: const BorderSide(color: Color(0xFF008CFF)),
                      ),
                    ),
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Nama wajib diisi' : null,
                  ),
                  const SizedBox(height: 16),

                  // DESKRIPSI
                  const Text(
                    'DESKRIPSI',
                    style: TextStyle(
                      color: Color(0xFF64748B),
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 6),
                  TextFormField(
                    controller: descController,
                    maxLines: 3,
                    style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                    decoration: InputDecoration(
                      hintText: 'Deskripsi singkat kategori (opsional)',
                      hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
                      filled: true,
                      fillColor: Colors.white,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: const BorderSide(color: Color(0xFF008CFF)),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // STATUS *
                  const Text(
                    'STATUS *',
                    style: TextStyle(
                      color: Color(0xFF64748B),
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 0.5,
                    ),
                  ),
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

                                final body = {
                                  'nama_kategori': nameController.text.trim(),
                                  'deskripsi': descController.text.trim(),
                                  'is_active': isActive ? '1' : '0',
                                };

                                final res = existing == null
                                    ? await ApiService.createKategori(body)
                                    : await ApiService.updateKategori(existing['id'], body);

                                if (ctx.mounted) Navigator.pop(ctx);
                                if (res['success'] == true) {
                                  setState(() {
                                    _successBanner = existing == null
                                        ? 'Kategori berhasil ditambahkan.'
                                        : 'Kategori berhasil diperbarui.';
                                  });
                                  _fetchKategori();
                                }
                              },
                        child: saving
                            ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                            : Text(existing == null ? 'Tambah Kategori' : 'Simpan Kategori'),
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
                    'Kelola Kategori',
                    style: TextStyle(
                      color: Color(0xFF1E293B),
                      fontWeight: FontWeight.bold,
                      fontSize: 22,
                    ),
                  ),
                  SizedBox(height: 4),
                  Text(
                    'Atur kategori untuk pengelompokan menu.',
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
                label: const Text('Tambah Kategori', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
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
                : _kategoriList.isEmpty
                    ? const Padding(
                        padding: EdgeInsets.all(40),
                        child: Center(
                          child: Text(
                            'Belum ada kategori.',
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
                            dataRowMaxHeight: 56,
                            horizontalMargin: 20,
                            columnSpacing: 30,
                            headingTextStyle: const TextStyle(
                              color: Color(0xFF94A3B8),
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 0.5,
                            ),
                            columns: const [
                              DataColumn(label: Text('NO')),
                              DataColumn(label: Text('NAMA KATEGORI')),
                              DataColumn(label: Text('SLUG')),
                              DataColumn(label: Text('DESKRIPSI')),
                              DataColumn(label: Text('STATUS')),
                              DataColumn(label: Text('AKSI')),
                            ],
                            rows: List.generate(_kategoriList.length, (i) {
                              final kat = _kategoriList[i];
                              final id = kat['id'];
                              final nama = (kat['nama_kategori'] ?? kat['nama'] ?? '-').toString();
                              final slug = (kat['slug'] ?? '-').toString();
                              final desc = (kat['deskripsi'] ?? '—').toString();
                              final isActive = (kat['is_active'] ?? 1) == 1;

                              return DataRow(
                                cells: [
                                  DataCell(Text('${i + 1}', style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13))),
                                  DataCell(Text(nama, style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.w600, fontSize: 13))),
                                  DataCell(Text(slug, style: const TextStyle(color: Color(0xFF94A3B8), fontFamily: 'monospace', fontSize: 12))),
                                  DataCell(Text(desc.isEmpty ? '—' : desc, style: const TextStyle(color: Color(0xFF64748B), fontSize: 13))),
                                  DataCell(
                                    InkWell(
                                      onTap: () async {
                                        await ApiService.toggleKategori(id);
                                        _fetchKategori();
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
                                          onTap: () => _showFormDialog(existing: kat),
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
                                                title: const Text('Hapus Kategori'),
                                                content: Text('Yakin ingin menghapus "$nama"?'),
                                                actions: [
                                                  TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
                                                  TextButton(
                                                    onPressed: () => Navigator.pop(c, true),
                                                    child: const Text('Hapus', style: TextStyle(color: Colors.red)),
                                                  ),
                                                ],
                                              ),
                                            );
                                            if (confirm == true) {
                                              await ApiService.deleteKategori(id);
                                              _fetchKategori();
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
