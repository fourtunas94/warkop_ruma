import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../services/api_service.dart';
import '../../widgets/app_widgets.dart';

class PengaturanScreen extends StatefulWidget {
  const PengaturanScreen({super.key});

  @override
  State<PengaturanScreen> createState() => _PengaturanScreenState();
}

class _PengaturanScreenState extends State<PengaturanScreen> {
  final _nameController = TextEditingController();
  final _descController = TextEditingController();
  final _waController = TextEditingController();
  final _addressController = TextEditingController();
  final _footerController = TextEditingController();

  File? _logoFile;
  File? _faviconFile;
  bool _loading = false;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _fetchPengaturan();
  }

  Future<void> _fetchPengaturan() async {
    setState(() => _loading = true);
    final res = await ApiService.getPengaturan();
    if (mounted) {
      setState(() {
        _loading = false;
        if (res['success'] == true) {
          final s = res['settings'] ?? {};
          _nameController.text = s['store_name'] ?? '';
          _descController.text = s['store_description'] ?? '';
          _waController.text = s['store_whatsapp'] ?? '';
          _addressController.text = s['store_address'] ?? '';
          _footerController.text = s['footer_text'] ?? '';
        }
      });
    }
  }

  Future<void> _savePengaturan() async {
    setState(() => _saving = true);

    final fields = {
      'store_name': _nameController.text.trim(),
      'store_description': _descController.text.trim(),
      'store_whatsapp': _waController.text.trim(),
      'store_address': _addressController.text.trim(),
      'footer_text': _footerController.text.trim(),
    };

    final res = await ApiService.updatePengaturan(
      fields,
      storeLogo: _logoFile,
      storeFavicon: _faviconFile,
    );

    if (mounted) {
      setState(() => _saving = false);
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Pengaturan berhasil disimpan.')),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Gagal menyimpan pengaturan.')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF020617),
      appBar: AppBar(
        title: const Text('Pengaturan Toko'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _fetchPengaturan,
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF008CFF)))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  AppCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Informasi Restoran',
                          style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 16),
                        TextField(
                          controller: _nameController,
                          style: const TextStyle(color: Colors.white),
                          decoration: darkInputDecoration(label: 'Nama Toko / Restoran'),
                        ),
                        const SizedBox(height: 12),
                        TextField(
                          controller: _descController,
                          style: const TextStyle(color: Colors.white),
                          decoration: darkInputDecoration(label: 'Deskripsi Singkat'),
                          maxLines: 2,
                        ),
                        const SizedBox(height: 12),
                        TextField(
                          controller: _waController,
                          keyboardType: TextInputType.phone,
                          style: const TextStyle(color: Colors.white),
                          decoration: darkInputDecoration(
                            label: 'Nomor WhatsApp (misal: 628123456789)',
                            prefixIcon: const Icon(Icons.chat_bubble_outline, color: Color(0xFF22C55E)),
                          ),
                        ),
                        const SizedBox(height: 12),
                        TextField(
                          controller: _addressController,
                          style: const TextStyle(color: Colors.white),
                          decoration: darkInputDecoration(label: 'Alamat Restoran'),
                          maxLines: 2,
                        ),
                        const SizedBox(height: 12),
                        TextField(
                          controller: _footerController,
                          style: const TextStyle(color: Colors.white),
                          decoration: darkInputDecoration(label: 'Footer Text'),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  AppCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Logo & Favicon',
                          style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            OutlinedButton.icon(
                              style: OutlinedButton.styleFrom(
                                foregroundColor: const Color(0xFF00E5FF),
                                side: const BorderSide(color: Color(0xFF00E5FF)),
                              ),
                              onPressed: () async {
                                final picker = ImagePicker();
                                final img = await picker.pickImage(source: ImageSource.gallery);
                                if (img != null) setState(() => _logoFile = File(img.path));
                              },
                              icon: const Icon(Icons.image, size: 18),
                              label: Text(_logoFile != null ? 'Logo Dipilih' : 'Pilih Logo'),
                            ),
                            const SizedBox(width: 12),
                            if (_logoFile != null)
                              Expanded(
                                child: Text(
                                  _logoFile!.path.split(Platform.pathSeparator).last,
                                  style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            OutlinedButton.icon(
                              style: OutlinedButton.styleFrom(
                                foregroundColor: const Color(0xFF7C3AED),
                                side: const BorderSide(color: Color(0xFF7C3AED)),
                              ),
                              onPressed: () async {
                                final picker = ImagePicker();
                                final img = await picker.pickImage(source: ImageSource.gallery);
                                if (img != null) setState(() => _faviconFile = File(img.path));
                              },
                              icon: const Icon(Icons.star_border, size: 18),
                              label: Text(_faviconFile != null ? 'Favicon Dipilih' : 'Pilih Favicon'),
                            ),
                            const SizedBox(width: 12),
                            if (_faviconFile != null)
                              Expanded(
                                child: Text(
                                  _faviconFile!.path.split(Platform.pathSeparator).last,
                                  style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: _saving ? null : _savePengaturan,
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      backgroundColor: const Color(0xFF008CFF),
                    ),
                    child: _saving
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : const Text('Simpan Pengaturan', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),
    );
  }
}
