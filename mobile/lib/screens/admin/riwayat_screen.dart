import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../helpers/laporan_print_helper.dart';
import '../../services/api_service.dart';

class AdminRiwayatScreen extends StatefulWidget {
  const AdminRiwayatScreen({super.key});

  @override
  State<AdminRiwayatScreen> createState() => _AdminRiwayatScreenState();
}

class _AdminRiwayatScreenState extends State<AdminRiwayatScreen> {
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

  Future<void> _fetchRiwayat() async {
    setState(() => _loading = true);

    String? startStr;
    String? endStr;
    if (_startDate != null) startStr = DateFormat('yyyy-MM-dd').format(_startDate!);
    if (_endDate != null) endStr = DateFormat('yyyy-MM-dd').format(_endDate!);

    final res = await ApiService.adminRiwayat(
      periode: _selectedPeriode,
      startDate: startStr,
      endDate: endStr,
    );

    if (mounted) {
      setState(() {
        _loading = false;
        if (res['success'] == true) {
          _riwayat = res['riwayat'] ?? [];
          _topMenus = res['top_menus'] ?? [];
          _summary = Map<String, dynamic>.from(res['summary'] ?? {});
          _totalPesanan = res['total_pesanan'] ?? 0;
          _totalPendapatan = res['total_pendapatan'] ?? 0;
          _periodeLabel = res['periode']?['label'] ?? _periodeLabel;
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
        _periodeLabel = '7 Hari Terakhir (${DateFormat('dd MMM').format(_startDate!)} - ${DateFormat('dd MMM yyyy').format(now)})';
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
        _periodeLabel = '${DateFormat('dd/MM/yyyy').format(picked.start)} - ${DateFormat('dd/MM/yyyy').format(picked.end)}';
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
        printedBy: 'Administrator',
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
    final numVal = (value is int) ? value.toDouble() : (value as double? ?? 0.0);
    return NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0).format(numVal);
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
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Riwayat Transaksi & Laporan',
                    style: TextStyle(
                      color: Color(0xFF1E293B),
                      fontWeight: FontWeight.bold,
                      fontSize: 22,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Periode: $_periodeLabel',
                    style: const TextStyle(color: Color(0xFF64748B), fontSize: 13, fontWeight: FontWeight.w500),
                  ),
                ],
              ),
              Row(
                children: [
                  ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF008CFF),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      elevation: 0,
                    ),
                    icon: const Icon(Icons.picture_as_pdf, size: 16),
                    label: const Text(
                      'Cetak Laporan PDF',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                    ),
                    onPressed: _cetakLaporanPdf,
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Period Filter Tabs
          Row(
            children: [
              _buildPeriodChip('harian', 'Harian (Hari Ini)', Icons.today),
              const SizedBox(width: 8),
              _buildPeriodChip('mingguan', 'Mingguan (7 Hari)', Icons.date_range),
              const SizedBox(width: 8),
              _buildPeriodChip('bulanan', 'Bulanan (Bulan Ini)', Icons.calendar_month),
              const SizedBox(width: 8),
              OutlinedButton.icon(
                style: OutlinedButton.styleFrom(
                  side: BorderSide(
                    color: _selectedPeriode == 'kustom' ? const Color(0xFF008CFF) : const Color(0xFFCBD5E1),
                    width: _selectedPeriode == 'kustom' ? 1.5 : 1.0,
                  ),
                  backgroundColor: _selectedPeriode == 'kustom' ? const Color(0xFFF0F9FF) : Colors.white,
                  foregroundColor: _selectedPeriode == 'kustom' ? const Color(0xFF008CFF) : const Color(0xFF334155),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
                icon: const Icon(Icons.tune, size: 15),
                label: Text(
                  _selectedPeriode == 'kustom' && _startDate != null && _endDate != null
                      ? '${DateFormat('dd/MM').format(_startDate!)} - ${DateFormat('dd/MM').format(_endDate!)}'
                      : 'Pilih Tanggal',
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
                ),
                onPressed: _pickDateRange,
              ),
            ],
          ),
          const SizedBox(height: 20),

          // 4 Summary Cards (Executive KPI)
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
              const SizedBox(width: 14),
              Expanded(
                child: _buildKpiCard(
                  title: 'TOTAL OMZET',
                  value: _formatCurrency(_totalPendapatan),
                  sub: 'Pendapatan bersih',
                  icon: Icons.savings_outlined,
                  color: const Color(0xFF16A34A),
                  bgColor: const Color(0xFFF0FDF4),
                ),
              ),
             
              const SizedBox(width: 14),
              Expanded(
                child: _buildKpiCard(
                  title: 'POTONGAN PROMO',
                  value: _formatCurrency(_summary['total_potongan'] ?? 0),
                  sub: 'Diskon transaksi',
                  icon: Icons.local_offer_outlined,
                  color: const Color(0xFFD97706),
                  bgColor: const Color(0xFFFFFBEB),
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),

          // SEKSI MENU PALING BANYAK DIPESAN (BEST SELLERS)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
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
                        Icon(Icons.leaderboard, color: Color(0xFF008CFF), size: 20),
                        SizedBox(width: 8),
                        Text(
                          'Menu Paling Banyak Dipesan (Best Sellers)',
                          style: TextStyle(
                            color: Color(0xFF1E293B),
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                    Text(
                      '${_topMenus.length} menu tercatat',
                      style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                if (_loading)
                  const Center(
                    child: Padding(
                      padding: EdgeInsets.all(24),
                      child: CircularProgressIndicator(color: Color(0xFF008CFF)),
                    ),
                  )
                else if (_topMenus.isEmpty)
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 20),
                    child: Center(
                      child: Text(
                        'Belum ada data pesanan menu pada periode ini.',
                        style: TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
                      ),
                    ),
                  )
                else
                  Wrap(
                    spacing: 12,
                    runSpacing: 12,
                    children: _topMenus.take(6).map((item) {
                      final m = Map<String, dynamic>.from(item as Map);
                      final rank = m['ranking'] ?? 1;
                      final nama = m['nama_menu'] ?? '-';
                      final qty = m['total_qty'] ?? 0;
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
                        width: 240,
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: Row(
                          children: [
                            Container(
                              width: 32,
                              height: 32,
                              decoration: BoxDecoration(
                                color: badgeBg,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              alignment: Alignment.center,
                              child: Text(
                                '#$rank',
                                style: TextStyle(
                                  color: badgeColor,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 12,
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),
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
                                      fontSize: 12.5,
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
                                          fontSize: 11,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                      Text(
                                        _formatCurrency(nominal),
                                        style: const TextStyle(
                                          color: Color(0xFF64748B),
                                          fontSize: 11,
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
          const SizedBox(height: 24),

          // Table Card: Riwayat Transaksi
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
                  padding: const EdgeInsets.all(18),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Daftar Rincian Transaksi',
                        style: TextStyle(
                          color: Color(0xFF1E293B),
                          fontWeight: FontWeight.bold,
                          fontSize: 15,
                        ),
                      ),
                      Text(
                        'Total ${_riwayat.length} Transaksi',
                        style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                      ),
                    ],
                  ),
                ),
                const Divider(height: 1, color: Color(0xFFF1F5F9)),
                _loading
                    ? const Padding(
                        padding: EdgeInsets.all(40),
                        child: Center(child: CircularProgressIndicator(color: Color(0xFF008CFF))),
                      )
                    : _riwayat.isEmpty
                        ? const Padding(
                            padding: EdgeInsets.all(40),
                            child: Center(
                              child: Text(
                                'Belum ada riwayat transaksi pada periode ini.',
                                style: TextStyle(color: Color(0xFF94A3B8)),
                              ),
                            ),
                          )
                        : ClipRRect(
                            borderRadius: const BorderRadius.vertical(bottom: Radius.circular(14)),
                            child: SingleChildScrollView(
                              scrollDirection: Axis.horizontal,
                              child: DataTable(
                                headingRowHeight: 44,
                                dataRowMaxHeight: 56,
                                horizontalMargin: 20,
                                columnSpacing: 24,
                                headingTextStyle: const TextStyle(
                                  color: Color(0xFF94A3B8),
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  letterSpacing: 0.5,
                                ),
                                columns: const [
                                  DataColumn(label: Text('KODE / MEJA')),
                                  DataColumn(label: Text('PELANGGAN')),
                                  DataColumn(label: Text('WAKTU')),
                                  DataColumn(label: Text('ITEM DIPESAN')),
                                  DataColumn(label: Text('METODE')),
                                  DataColumn(label: Text('TOTAL')),
                                  DataColumn(label: Text('STATUS')),
                                ],
                                rows: List.generate(_riwayat.length, (i) {
                                  final p = _riwayat[i];
                                  final nomorMeja = (p['nomor_meja'] ?? '-').toString();
                                  final kode = (p['kode_pesanan'] ?? '-').toString();
                                  final nama = (p['nama_pemesan'] ?? 'Pelanggan').toString();
                                  final waktu = (p['created_at'] ?? '-').toString();
                                  final metode = (p['metode_pembayaran'] ?? 'tunai').toString();
                                  final total = p['total_harga'] ?? 0;
                                  final status = (p['status'] ?? 'selesai').toString();

                                  final itemsRaw = (p['items'] as List<dynamic>?) ?? [];
                                  final itemsSummary = itemsRaw.map((it) {
                                    final n = it['nama_menu'] ?? it['nama'] ?? '-';
                                    final q = it['qty'] ?? 1;
                                    return '${q}x $n';
                                  }).join(', ');

                                  return DataRow(
                                    cells: [
                                      DataCell(
                                        Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Text('Meja $nomorMeja', style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 13)),
                                            Text('#$kode', style: const TextStyle(color: Color(0xFF94A3B8), fontFamily: 'monospace', fontSize: 11)),
                                          ],
                                        ),
                                      ),
                                      DataCell(Text(nama, style: const TextStyle(color: Color(0xFF334155), fontWeight: FontWeight.w500, fontSize: 13))),
                                      DataCell(Text(waktu.length >= 16 ? waktu.substring(0, 16) : waktu, style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12))),
                                      DataCell(
                                        ConstrainedBox(
                                          constraints: const BoxConstraints(maxWidth: 240),
                                          child: Text(
                                            itemsSummary.isEmpty ? '-' : itemsSummary,
                                            maxLines: 2,
                                            overflow: TextOverflow.ellipsis,
                                            style: const TextStyle(color: Color(0xFF475569), fontSize: 12),
                                          ),
                                        ),
                                      ),
                                      DataCell(
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                          decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(6)),
                                          child: Text(metode.toUpperCase(), style: const TextStyle(color: Color(0xFF64748B), fontSize: 10.5, fontWeight: FontWeight.bold)),
                                        ),
                                      ),
                                      DataCell(Text(_formatCurrency(total), style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 13))),
                                      DataCell(
                                        Text(
                                          status.toUpperCase(),
                                          style: TextStyle(
                                            color: status == 'selesai' ? const Color(0xFF16A34A) : const Color(0xFFD97706),
                                            fontWeight: FontWeight.bold,
                                            fontSize: 11,
                                            letterSpacing: 0.5,
                                          ),
                                        ),
                                      ),
                                    ],
                                  );
                                }),
                              ),
                            ),
                          ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPeriodChip(String id, String label, IconData icon) {
    final isSelected = _selectedPeriode == id;
    return InkWell(
      onTap: () => _onSelectPeriode(id),
      borderRadius: BorderRadius.circular(8),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
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
              size: 15,
              color: isSelected ? Colors.white : const Color(0xFF64748B),
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                color: isSelected ? Colors.white : const Color(0xFF334155),
                fontSize: 12,
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
      padding: const EdgeInsets.all(16),
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
            width: 42,
            height: 42,
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 10.5, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 3),
                Text(
                  value,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 17),
                ),
                const SizedBox(height: 1),
                Text(
                  sub,
                  style: const TextStyle(color: Color(0xFF64748B), fontSize: 10.5),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
