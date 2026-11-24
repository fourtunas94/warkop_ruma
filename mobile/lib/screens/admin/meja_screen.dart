import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../../services/api_service.dart';

class MejaScreen extends StatefulWidget {
  const MejaScreen({super.key});

  @override
  State<MejaScreen> createState() => _MejaScreenState();
}

class _MejaScreenState extends State<MejaScreen> {
  List<dynamic> _mejas = [];
  bool _loading = false;
  final TextEditingController _nomorMejaController = TextEditingController();
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();
  bool _saving = false;
  String? _successBanner;

  @override
  void initState() {
    super.initState();
    _fetchMejas();
  }

  @override
  void dispose() {
    _nomorMejaController.dispose();
    super.dispose();
  }

  Future<void> _fetchMejas() async {
    setState(() => _loading = true);
    final res = await ApiService.getMejas();
    if (mounted) {
      setState(() {
        _loading = false;
        if (res['success'] == true) {
          _mejas = res['mejas'] ?? [];
        }
      });
    }
  }

  Future<void> _storeMeja() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);
    final res = await ApiService.storeMeja(_nomorMejaController.text.trim().toUpperCase());
    setState(() => _saving = false);

    if (res['success'] == true) {
      _nomorMejaController.clear();
      setState(() => _successBanner = 'Meja berhasil ditambahkan.');
      _fetchMejas();
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Gagal menambahkan meja.')),
        );
      }
    }
  }

  // ─── QR MODAL (SCREENSHOT 6) ────────────────────────────────────────────────
  void _showQrDialog(Map<String, dynamic> meja) {
    final rawUrl = (meja['qr_url'] ?? '').toString();
    final qrUrl = rawUrl
        .replaceAll('localhost', '192.168.1.18')
        .replaceAll('127.0.0.1', '192.168.1.18');

    final rawNo = (meja['nomor_meja'] ?? '-').toString();
    final nomorMeja = rawNo.toUpperCase().startsWith('NO') ? rawNo : 'NO $rawNo';

    showDialog(
      context: context,
      barrierColor: Colors.black.withOpacity(0.55),
      builder: (ctx) => AlertDialog(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        contentPadding: const EdgeInsets.all(28),
        content: SizedBox(
          width: 360,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                'QR Code Meja $nomorMeja',
                style: const TextStyle(
                  color: Color(0xFF1E293B),
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 4),
              const Text(
                'Pelanggan scan untuk mulai memesan.',
                style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),

              // QR Box
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: qrUrl.isNotEmpty
                    ? SizedBox(
                        width: 200,
                        height: 200,
                        child: QrImageView(
                          data: qrUrl,
                          version: QrVersions.auto,
                          size: 200.0,
                          backgroundColor: Colors.white,
                          eyeStyle: const QrEyeStyle(
                            eyeShape: QrEyeShape.square,
                            color: Colors.black,
                          ),
                          dataModuleStyle: const QrDataModuleStyle(
                            dataModuleShape: QrDataModuleShape.square,
                            color: Colors.black,
                          ),
                        ),
                      )
                    : const SizedBox(
                        width: 200,
                        height: 200,
                        child: Center(
                          child: Text('QR URL tidak tersedia', style: TextStyle(color: Colors.black)),
                        ),
                      ),
              ),
              const SizedBox(height: 16),

              SelectableText(
                qrUrl,
                style: const TextStyle(color: Color(0xFF94A3B8), fontFamily: 'monospace', fontSize: 11),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),

              // Buttons: Print & Tutup
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF008CFF),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      elevation: 0,
                    ),
                    icon: const Icon(Icons.print, size: 16),
                    label: const Text('Print', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                    onPressed: () {
                      Clipboard.setData(ClipboardData(text: qrUrl));
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Link QR disalin untuk cetak.')),
                      );
                    },
                  ),
                  const SizedBox(width: 12),
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFF1F5F9),
                      foregroundColor: const Color(0xFF64748B),
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      elevation: 0,
                    ),
                    onPressed: () => Navigator.pop(ctx),
                    child: const Text('Tutup', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                  ),
                ],
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
          // Page Header
          const Text(
            'Meja & QR Code',
            style: TextStyle(
              color: Color(0xFF1E293B),
              fontSize: 22,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            'Kelola meja dan QR code untuk pemesanan pelanggan.',
            style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
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

          // 2 Columns Layout (Screenshot 5)
          LayoutBuilder(
            builder: (ctx, constraints) {
              final isWide = constraints.maxWidth >= 820;

              if (isWide) {
                return Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SizedBox(width: 320, child: _buildAddMejaCard()),
                    const SizedBox(width: 24),
                    Expanded(child: _buildMejaTableCard()),
                  ],
                );
              }

              return Column(
                children: [
                  _buildAddMejaCard(),
                  const SizedBox(height: 20),
                  _buildMejaTableCard(),
                ],
              );
            },
          ),
        ],
      ),
    );
  }

  // ─── LEFT: TAMBAH MEJA BARU CARD (SCREENSHOT 5) ─────────────────────────────
  Widget _buildAddMejaCard() {
    return Container(
      padding: const EdgeInsets.all(20),
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
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Tambah Meja Baru',
              style: TextStyle(
                color: Color(0xFF1E293B),
                fontWeight: FontWeight.bold,
                fontSize: 15,
              ),
            ),
            const SizedBox(height: 16),

            const Text(
              'NOMOR MEJA *',
              style: TextStyle(
                color: Color(0xFF64748B),
                fontSize: 11,
                fontWeight: FontWeight.bold,
                letterSpacing: 0.5,
              ),
            ),
            const SizedBox(height: 6),
            TextFormField(
              controller: _nomorMejaController,
              textCapitalization: TextCapitalization.characters,
              maxLength: 10,
              buildCounter: (_, {required currentLength, required isFocused, maxLength}) => null,
              style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13, fontWeight: FontWeight.bold),
              decoration: InputDecoration(
                hintText: 'CONTOH: A1, B2, VIP1',
                hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12, fontWeight: FontWeight.normal),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
              ),
              validator: (v) => (v == null || v.trim().isEmpty) ? 'Nomor meja wajib diisi' : null,
            ),
            const SizedBox(height: 4),
            const Text(
              'Maks 10 karakter, otomatis kapital.',
              style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
            ),
            const SizedBox(height: 16),

            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF008CFF),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  elevation: 0,
                ),
                icon: _saving
                    ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Icon(Icons.add, size: 16),
                label: Text(
                  _saving ? 'Menyimpan...' : '+ Tambah Meja',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                ),
                onPressed: _saving ? null : _storeMeja,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ─── RIGHT: MEJA TABLE CARD (SCREENSHOT 5) ──────────────────────────────────
  Widget _buildMejaTableCard() {
    return Container(
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
          : _mejas.isEmpty
              ? const Padding(
                  padding: EdgeInsets.all(40),
                  child: Center(child: Text('Belum ada meja.', style: TextStyle(color: Color(0xFF94A3B8)))),
                )
              : ClipRRect(
                  borderRadius: BorderRadius.circular(14),
                  child: SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: DataTable(
                      headingRowHeight: 48,
                      dataRowMaxHeight: 56,
                      horizontalMargin: 20,
                      columnSpacing: 28,
                      headingTextStyle: const TextStyle(
                        color: Color(0xFF94A3B8),
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        letterSpacing: 0.5,
                      ),
                      columns: const [
                        DataColumn(label: Text('MEJA')),
                        DataColumn(label: Text('URL QR')),
                        DataColumn(label: Text('STATUS')),
                        DataColumn(label: Text('AKSI')),
                      ],
                      rows: List.generate(_mejas.length, (i) {
                        final m = _mejas[i];
                        final id = m['id'];
                        final rawNo = (m['nomor_meja'] ?? '-').toString();
                        final nomorMeja = rawNo.toUpperCase().startsWith('NO') ? rawNo : 'NO $rawNo';
                        final rawUrl = (m['qr_url'] ?? '').toString();
                        final qrUrl = rawUrl
                            .replaceAll('localhost', '192.168.1.18')
                            .replaceAll('127.0.0.1', '192.168.1.18');
                        final isActive = (m['is_active'] ?? 1) == 1;

                        return DataRow(
                          cells: [
                            DataCell(Text(nomorMeja, style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 13))),
                            DataCell(
                              SizedBox(
                                width: 220,
                                child: Text(
                                  qrUrl,
                                  style: const TextStyle(color: Color(0xFF94A3B8), fontFamily: 'monospace', fontSize: 11),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ),
                            DataCell(
                              InkWell(
                                onTap: () async {
                                  await ApiService.toggleMeja(id);
                                  _fetchMejas();
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
                                  // QR Button
                                  OutlinedButton.icon(
                                    style: OutlinedButton.styleFrom(
                                      side: const BorderSide(color: Color(0xFFCBD5E1)),
                                      foregroundColor: const Color(0xFF334155),
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                      minimumSize: Size.zero,
                                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                                    ),
                                    icon: const Icon(Icons.qr_code, size: 14, color: Color(0xFF64748B)),
                                    label: const Text('QR', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600)),
                                    onPressed: () => _showQrDialog(m),
                                  ),
                                  const SizedBox(width: 8),

                                  // Hapus Button
                                  OutlinedButton.icon(
                                    style: OutlinedButton.styleFrom(
                                      side: const BorderSide(color: Color(0xFFCBD5E1)),
                                      foregroundColor: const Color(0xFF334155),
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                      minimumSize: Size.zero,
                                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                                    ),
                                    icon: const Icon(Icons.delete_outline, size: 14, color: Color(0xFF64748B)),
                                    label: const Text('Hapus', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600)),
                                    onPressed: () async {
                                      final confirm = await showDialog<bool>(
                                        context: context,
                                        builder: (c) => AlertDialog(
                                          title: const Text('Hapus Meja'),
                                          content: Text('Yakin ingin menghapus Meja $nomorMeja?'),
                                          actions: [
                                            TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
                                            TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Hapus', style: TextStyle(color: Colors.red))),
                                          ],
                                        ),
                                      );
                                      if (confirm == true) {
                                        await ApiService.deleteMeja(id);
                                        _fetchMejas();
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
}
