import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../helpers/laporan_print_helper.dart';
import '../../services/api_service.dart';
import 'struk_dialog.dart';

class KasirRiwayatScreen extends StatefulWidget {
  const KasirRiwayatScreen({super.key});

  @override
  State<KasirRiwayatScreen> createState() => _KasirRiwayatScreenState();
}

class _KasirRiwayatScreenState extends State<KasirRiwayatScreen> {
  List<dynamic> _riwayat = [];
  List<dynamic> _topMenus = [];
  Map<String, dynamic> _summary = {};
  bool _loading = false;
  int _totalPesanan = 0;
  dynamic _totalPendapatan = 0;

  String _selectedPeriode = 'harian'; // 'harian', 'mingguan', 'bulanan', 'kustom'
  String _periodeLabel = 'Hari Ini';
  DateTime? _startDate = DateTime.now();
  DateTime? _endDate = DateTime.now();

  @override
  void initState() {
    super.initState();
    _fetchRiwayat();
  }

  double _toDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    final cleaned = value.toString().replaceAll(RegExp(r'[^0-9.-]'), '');
    return double.tryParse(cleaned) ?? 0.0;
  }

  int _toInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is num) return value.toInt();
    final cleaned = value.toString().replaceAll(RegExp(r'[^0-9-]'), '');
    return int.tryParse(cleaned) ?? _toDouble(value).round();
  }

  Future<void> _fetchRiwayat() async {
    setState(() => _loading = true);

    String? startStr;
    String? endStr;
    if (_startDate != null) startStr = DateFormat('yyyy-MM-dd').format(_startDate!);
    if (_endDate != null) endStr = DateFormat('yyyy-MM-dd').format(_endDate!);

    final res = await ApiService.kasirRiwayat(
      periode: _selectedPeriode,
      startDate: startStr,
      endDate: endStr,
    );

    if (mounted) {
      setState(() {
        _loading = false;
        if (res['success'] == true) {
          final dataMap = res['data'] is Map ? Map<String, dynamic>.from(res['data'] as Map) : <String, dynamic>{};
          _riwayat = res['riwayat'] is List
              ? res['riwayat']
              : (dataMap['transactions'] is List ? dataMap['transactions'] : []);
          _topMenus = res['top_menus'] is List ? res['top_menus'] : [];
          _summary = res['summary'] is Map
              ? Map<String, dynamic>.from(res['summary'] as Map)
              : <String, dynamic>{};
          _totalPesanan = res['total_pesanan'] != null
              ? _toInt(res['total_pesanan'])
              : _riwayat.length;
          _totalPendapatan = res['total_pendapatan'] ?? dataMap['total_pendapatan'] ?? 0;
          _periodeLabel = res['periode']?['label']?.toString() ?? _periodeLabel;
        }
      });
    }
  }

  void _onSelectPeriode(String periode) {
    final now = DateTime.now();
    setState(() {
      _selectedPeriode = periode;
      if (periode == 'harian') {
        _startDate = now;
        _endDate = now;
        _periodeLabel = 'Hari Ini (${DateFormat('dd MMM yyyy').format(now)})';
      } else if (periode == 'mingguan') {
        _startDate = now.subtract(const Duration(days: 6));
        _endDate = now;
        _periodeLabel =
            '7 Hari Terakhir (${DateFormat('dd MMM').format(_startDate!)} - ${DateFormat('dd MMM yyyy').format(now)})';
      } else if (periode == 'bulanan') {
        _startDate = DateTime(now.year, now.month, 1);
        _endDate = now;
        _periodeLabel = 'Bulan Ini (${DateFormat('MMMM yyyy').format(now)})';
      }
    });
    _fetchRiwayat();
  }

  Future<void> _pickDateRange() async {
    final picked = await showDateRangePicker(
      context: context,
      firstDate: DateTime(2020),
      lastDate: DateTime(2030),
      initialDateRange: (_startDate != null && _endDate != null)
          ? DateTimeRange(start: _startDate!, end: _endDate!)
          : null,
      builder: (context, child) {
        return Theme(
          data: ThemeData.light().copyWith(
            colorScheme: const ColorScheme.light(
              primary: Color(0xFF008CFF),
              onPrimary: Colors.white,
              surface: Colors.white,
              onSurface: Color(0xFF1E293B),
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() {
        _selectedPeriode = 'kustom';
        _startDate = picked.start;
        _endDate = picked.end;
        _periodeLabel =
            '${DateFormat('dd/MM/yyyy').format(picked.start)} - ${DateFormat('dd/MM/yyyy').format(picked.end)}';
      });
      _fetchRiwayat();
    }
  }

  void _cetakLaporanPdf() {
    try {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Row(
            children: [
              SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)),
              SizedBox(width: 12),
              Text('Membuka pratinjau cetak / simpan laporan PDF...'),
            ],
          ),
          duration: Duration(seconds: 2),
        ),
      );

      final success = LaporanPrintHelper.printLaporan(
        periodeLabel: _periodeLabel,
        periodeType: _selectedPeriode,
        summary: _summary.isNotEmpty
            ? _summary
            : {
                'total_pesanan': _totalPesanan,
                'total_pendapatan': _totalPendapatan,
                'total_ppn': 0,
                'total_potongan': 0,
                'metode': {},
              },
        topMenus: _topMenus,
        transactions: _riwayat,
        printedBy: 'Kasir POS',
      );

      if (!success && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Gagal membuka cetak. Pastikan pop-up di browser tidak diblokir.'),
            backgroundColor: Colors.redAccent,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Terjadi kendala cetak: $e'),
            backgroundColor: Colors.redAccent,
          ),
        );
      }
    }
  }

  String _formatCurrency(dynamic value) {
    final amount = _toDouble(value);
    return NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0).format(amount);
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      color: const Color(0xFFF4F6FA),
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header Row
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Riwayat Transaksi Kasir',
                      style: TextStyle(
                        color: Color(0xFF1E293B),
                        fontWeight: FontWeight.bold,
                        fontSize: 20,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      'Periode: $_periodeLabel',
                      style: const TextStyle(color: Color(0xFF64748B), fontSize: 12.5, fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
                ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF008CFF),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    elevation: 0,
                  ),
                  icon: const Icon(Icons.picture_as_pdf, size: 15),
                  label: const Text(
                    'Cetak Laporan PDF',
                    style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold),
                  ),
                  onPressed: _cetakLaporanPdf,
                ),
              ],
            ),
            const SizedBox(height: 14),

            // Period Filter Buttons
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                _buildPeriodChip('harian', 'Harian (Hari Ini)', Icons.today),
                _buildPeriodChip('mingguan', 'Mingguan (7 Hari)', Icons.date_range),
                _buildPeriodChip('bulanan', 'Bulanan (Bulan Ini)', Icons.calendar_month),
                OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    side: BorderSide(
                      color: _selectedPeriode == 'kustom' ? const Color(0xFF008CFF) : const Color(0xFFCBD5E1),
                      width: _selectedPeriode == 'kustom' ? 1.5 : 1.0,
                    ),
                    backgroundColor: _selectedPeriode == 'kustom' ? const Color(0xFFF0F9FF) : Colors.white,
                    foregroundColor: _selectedPeriode == 'kustom' ? const Color(0xFF008CFF) : const Color(0xFF334155),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  icon: const Icon(Icons.tune, size: 14),
                  label: Text(
                    _selectedPeriode == 'kustom' && _startDate != null && _endDate != null
                        ? '${DateFormat('dd/MM').format(_startDate!)} - ${DateFormat('dd/MM').format(_endDate!)}'
                        : 'Pilih Tanggal',
                    style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600),
                  ),
                  onPressed: _pickDateRange,
                ),
              ],
            ),
            const SizedBox(height: 18),

            // 3 Summary Cards (Executive KPI)
            LayoutBuilder(
              builder: (context, constraints) {
                final isWide = constraints.maxWidth > 700;
                if (isWide) {
                  return Row(
                    children: [
                      Expanded(
                        child: _buildKpiCard(
                          title: 'TOTAL PESANAN',
                          value: '$_totalPesanan',
                          sub: 'Transaksi selesai',
                          icon: Icons.receipt_long,
                          color: const Color(0xFF008CFF),
                          bgColor: const Color(0xFFF0F9FF),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _buildKpiCard(
                          title: 'TOTAL PENDAPATAN',
                          value: _formatCurrency(_totalPendapatan),
                          sub: 'Omzet penjualan',
                          icon: Icons.payments_outlined,
                          color: const Color(0xFF16A34A),
                          bgColor: const Color(0xFFF0FDF4),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _buildKpiCard(
                          title: 'DISKON PROMO',
                          value: _formatCurrency(_summary['total_potongan'] ?? 0),
                          sub: 'Potongan harga',
                          icon: Icons.local_offer_outlined,
                          color: const Color(0xFFD97706),
                          bgColor: const Color(0xFFFFFBEB),
                        ),
                      ),
                    ],
                  );
                }
                return Column(
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: _buildKpiCard(
                            title: 'TOTAL PESANAN',
                            value: '$_totalPesanan',
                            sub: 'Transaksi selesai',
                            icon: Icons.receipt_long,
                            color: const Color(0xFF008CFF),
                            bgColor: const Color(0xFFF0F9FF),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: _buildKpiCard(
                            title: 'TOTAL PENDAPATAN',
                            value: _formatCurrency(_totalPendapatan),
                            sub: 'Omzet penjualan',
                            icon: Icons.payments_outlined,
                            color: const Color(0xFF16A34A),
                            bgColor: const Color(0xFFF0FDF4),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    _buildKpiCard(
                      title: 'DISKON PROMO',
                      value: _formatCurrency(_summary['total_potongan'] ?? 0),
                      sub: 'Potongan harga',
                      icon: Icons.local_offer_outlined,
                      color: const Color(0xFFD97706),
                      bgColor: const Color(0xFFFFFBEB),
                    ),
                  ],
                );
              },
            ),
            const SizedBox(height: 20),

            // SEKSI MENU PALING BANYAK DIPESAN (BEST SELLERS)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFF1F5F9)),
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
                      const Row(
                        children: [
                          Icon(Icons.leaderboard, color: Color(0xFF008CFF), size: 18),
                          SizedBox(width: 8),
                          Text(
                            'Menu Paling Banyak Dipesan (Best Sellers)',
                            style: TextStyle(
                              color: Color(0xFF1E293B),
                              fontSize: 14.5,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      Text(
                        '${_topMenus.length} menu',
                        style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11.5),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  if (_loading)
                    const Center(
                      child: Padding(
                        padding: EdgeInsets.all(20),
                        child: CircularProgressIndicator(color: Color(0xFF008CFF)),
                      ),
                    )
                  else if (_topMenus.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 16),
                      child: Center(
                        child: Text(
                          'Belum ada data menu terjual pada periode ini.',
                          style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                        ),
                      ),
                    )
                  else
                    Wrap(
                      spacing: 10,
                      runSpacing: 10,
                      children: _topMenus.take(6).map((item) {
                        final m = Map<String, dynamic>.from(item as Map);
                        final rank = _toInt(m['ranking'] ?? 1);
                        final nama = (m['nama_menu'] ?? '-').toString();
                        final qty = _toInt(m['total_qty']);
                        final nominal = m['total_nominal'] ?? 0;

                        Color badgeColor = const Color(0xFF64748B);
                        Color badgeBg = const Color(0xFFF1F5F9);
                        if (rank == 1) {
                          badgeColor = const Color(0xFFB45309);
                          badgeBg = const Color(0xFFFEF3C7);
                        } else if (rank == 2) {
                          badgeColor = const Color(0xFF475569);
                          badgeBg = const Color(0xFFE2E8F0);
                        } else if (rank == 3) {
                          badgeColor = const Color(0xFF9A3412);
                          badgeBg = const Color(0xFFFFEDD5);
                        }

                        return Container(
                          width: 230,
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: Row(
                            children: [
                              Container(
                                width: 28,
                                height: 28,
                                decoration: BoxDecoration(
                                  color: badgeBg,
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                alignment: Alignment.center,
                                child: Text(
                                  '#$rank',
                                  style: TextStyle(
                                    color: badgeColor,
                                    fontWeight: FontWeight.w900,
                                    fontSize: 11,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      nama,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: const TextStyle(
                                        color: Color(0xFF1E293B),
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          '$qty terjual',
                                          style: const TextStyle(
                                            color: Color(0xFF008CFF),
                                            fontSize: 10.5,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                        Text(
                                          _formatCurrency(nominal),
                                          style: const TextStyle(
                                            color: Color(0xFF64748B),
                                            fontSize: 10.5,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        );
                      }).toList(),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Daftar Rincian Transaksi
            Container(
              width: double.infinity,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFF1F5F9)),
                boxShadow: const [
                  BoxShadow(color: Color(0x08000000), blurRadius: 10, offset: Offset(0, 2)),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Daftar Transaksi Selesai',
                          style: TextStyle(
                            color: Color(0xFF1E293B),
                            fontWeight: FontWeight.bold,
                            fontSize: 14.5,
                          ),
                        ),
                        Text(
                          'Total ${_riwayat.length} Transaksi',
                          style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11.5),
                        ),
                      ],
                    ),
                  ),
                  const Divider(height: 1, color: Color(0xFFF1F5F9)),
                  if (_loading)
                    const Padding(
                      padding: EdgeInsets.all(36),
                      child: Center(child: CircularProgressIndicator(color: Color(0xFF008CFF))),
                    )
                  else if (_riwayat.isEmpty)
                    const Padding(
                      padding: EdgeInsets.all(36),
                      child: Center(
                        child: Text(
                          'Belum ada transaksi pada periode ini.',
                          style: TextStyle(color: Color(0xFF94A3B8)),
                        ),
                      ),
                    )
                  else
                    ListView.separated(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: _riwayat.length,
                      separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFF1F5F9)),
                      itemBuilder: (context, i) {
                        final tx = Map<String, dynamic>.from(_riwayat[i] as Map);
                        final meja = (tx['nomor_meja'] ?? '-').toString();
                        final kode = (tx['kode_pesanan'] ?? '-').toString();
                        final antrean = (tx['nomor_antrian'] ?? '-').toString();
                        final nama = (tx['nama_pemesan'] ?? 'Tamu').toString();
                        final metode = (tx['metode_pembayaran'] ?? 'tunai').toString();
                        final total = tx['total_harga'] ?? tx['total'] ?? 0;
                        final waktu = tx['created_at']?.toString() ?? '-';

                        final itemsRaw = (tx['items'] as List<dynamic>?) ?? [];
                        final itemsSummary = itemsRaw.map((it) {
                          final itemMap = it is Map ? Map<String, dynamic>.from(it) : <String, dynamic>{};
                          final n = itemMap['nama_menu'] ?? itemMap['nama'] ?? '-';
                          final q = _toInt(itemMap['qty'] ?? itemMap['jumlah'] ?? 1);
                          return '${q}x $n';
                        }).join(', ');

                        return Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          child: Row(
                            children: [
                              Container(
                                width: 40,
                                height: 40,
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF0F9FF),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                alignment: Alignment.center,
                                child: Text(
                                  '#$antrean',
                                  style: const TextStyle(
                                    color: Color(0xFF008CFF),
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Text(
                                          'Meja $meja • $nama',
                                          style: const TextStyle(
                                            color: Color(0xFF1E293B),
                                            fontWeight: FontWeight.bold,
                                            fontSize: 13,
                                          ),
                                        ),
                                        const SizedBox(width: 8),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                          decoration: BoxDecoration(
                                            color: const Color(0xFFF1F5F9),
                                            borderRadius: BorderRadius.circular(4),
                                          ),
                                          child: Text(
                                            metode.toUpperCase(),
                                            style: const TextStyle(
                                              color: Color(0xFF64748B),
                                              fontSize: 9.5,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 3),
                                    Text(
                                      itemsSummary.isEmpty ? '-' : itemsSummary,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: const TextStyle(color: Color(0xFF64748B), fontSize: 11.5),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      'Kode: $kode • ${_formatDate(waktu)}',
                                      style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 10.5),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(width: 12),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text(
                                    _formatCurrency(total),
                                    style: const TextStyle(
                                      color: Color(0xFF1E293B),
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13.5,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  InkWell(
                                    onTap: () => StrukDialog.showOrderReceipt(context, order: tx),
                                    child: const Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(Icons.receipt_long, size: 12, color: Color(0xFF008CFF)),
                                        SizedBox(width: 3),
                                        Text(
                                          'Lihat Struk',
                                          style: TextStyle(
                                            color: Color(0xFF008CFF),
                                            fontSize: 11,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _formatDate(String? dateStr) {
    if (dateStr == null || dateStr.isEmpty) return '-';
    try {
      final dt = DateTime.parse(dateStr).toLocal();
      return DateFormat('dd/MM/yyyy HH:mm').format(dt);
    } catch (_) {
      return dateStr;
    }
  }

  Widget _buildPeriodChip(String id, String label, IconData icon) {
    final isSelected = _selectedPeriode == id;
    return InkWell(
      onTap: () => _onSelectPeriode(id),
      borderRadius: BorderRadius.circular(8),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF008CFF) : Colors.white,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: isSelected ? const Color(0xFF008CFF) : const Color(0xFFCBD5E1),
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: const Color(0xFF008CFF).withOpacity(0.25),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 14,
              color: isSelected ? Colors.white : const Color(0xFF64748B),
            ),
            const SizedBox(width: 5),
            Text(
              label,
              style: TextStyle(
                color: isSelected ? Colors.white : const Color(0xFF334155),
                fontSize: 11.5,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildKpiCard({
    required String title,
    required String value,
    required String sub,
    required IconData icon,
    required Color color,
    required Color bgColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFF1F5F9)),
        boxShadow: const [
          BoxShadow(color: Color(0x08000000), blurRadius: 10, offset: Offset(0, 2)),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 38,
            height: 38,
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: color, size: 18),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 10, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 15),
                ),
                const SizedBox(height: 1),
                Text(
                  sub,
                  style: const TextStyle(color: Color(0xFF64748B), fontSize: 10),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}