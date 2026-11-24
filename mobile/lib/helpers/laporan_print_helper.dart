import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../screens/kasir/print_helper_stub.dart'
    if (dart.library.html) '../screens/kasir/print_helper_web.dart' as print_helper;

class LaporanPrintHelper {
  static String _formatCurrency(num value) {
    try {
      return NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0).format(value);
    } catch (_) {
      return 'Rp ${value.round()}';
    }
  }

  static String _escapeHtml(String s) => s
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;');

  static int _toInt(dynamic v, {int fallback = 0}) {
    if (v is int) return v;
    if (v is double) return v.round();
    if (v is String) return int.tryParse(v) ?? fallback;
    return fallback;
  }

  static bool printLaporan({
    required String periodeLabel,
    required String periodeType,
    required Map<String, dynamic> summary,
    required List<dynamic> topMenus,
    required List<dynamic> transactions,
    String printedBy = 'Kasir / Manajemen',
  }) {
    try {
      final html = generateLaporanHtml(
        periodeLabel: periodeLabel,
        periodeType: periodeType,
        summary: summary,
        topMenus: topMenus,
        transactions: transactions,
        printedBy: printedBy,
      );
      return print_helper.printHtmlDoc(
        html,
        windowFeatures: 'width=950,height=850,resizable=yes,scrollbars=yes',
      );
    } catch (e) {
      debugPrint('Error printLaporan: $e');
      return false;
    }
  }

  static String generateLaporanHtml({
    required String periodeLabel,
    required String periodeType,
    required Map<String, dynamic> summary,
    required List<dynamic> topMenus,
    required List<dynamic> transactions,
    String printedBy = 'Kasir / Manajemen',
  }) {
    String nowStr;
    try {
      nowStr = DateFormat('dd MMMM yyyy, HH:mm', 'id_ID').format(DateTime.now());
    } catch (_) {
      nowStr = DateFormat('dd/MM/yyyy HH:mm').format(DateTime.now());
    }

    final totalPesanan = _toInt(summary['total_pesanan'], fallback: transactions.length);
    final totalPendapatan = _toInt(summary['total_pendapatan']);
    final totalPotongan = _toInt(summary['total_potongan']);

    final metode = (summary['metode'] is Map) ? Map<dynamic, dynamic>.from(summary['metode'] as Map) : {};
    final tunai = _toInt(metode['tunai']);
    final qris = _toInt(metode['qris']);
    final transfer = _toInt(metode['transfer']);
    final split = _toInt(metode['split']);

    // Badge styling based on period
    String periodBadge = 'LAPORAN TRANSAKSI';
    final pLower = periodeType.toLowerCase();
    if (pLower.contains('hari')) {
      periodBadge = 'LAPORAN PENJUALAN HARIAN';
    } else if (pLower.contains('minggu')) {
      periodBadge = 'LAPORAN PENJUALAN MINGGUAN';
    } else if (pLower.contains('bulan')) {
      periodBadge = 'LAPORAN PENJUALAN BULANAN';
    }

    // Top Menus Table Rows
    final topMenuRows = topMenus.isEmpty
        ? '<tr><td colspan="5" style="text-align:center; padding:12px; color:#64748b;">Belum ada data menu terjual pada periode ini</td></tr>'
        : topMenus.take(10).map((it) {
            final Map<dynamic, dynamic> mRaw = (it is Map) ? it : {};
            final rank = mRaw['ranking'] ?? 1;
            final nama = _escapeHtml(mRaw['nama_menu']?.toString() ?? '-');
            final qty = _toInt(mRaw['total_qty']);
            final nominal = _toInt(mRaw['total_nominal']);
            final pesananCount = _toInt(mRaw['total_pesanan']);

            String rankBadge = '#$rank';
            String rankStyle = 'font-weight:bold; color:#475569;';
            if (rank == 1) {
              rankBadge = '🥇 #1';
              rankStyle = 'font-weight:900; color:#b45309;';
            } else if (rank == 2) {
              rankBadge = '🥈 #2';
              rankStyle = 'font-weight:900; color:#475569;';
            } else if (rank == 3) {
              rankBadge = '🥉 #3';
              rankStyle = 'font-weight:900; color:#9a3412;';
            }

            return '''
              <tr>
                <td style="text-align:center; $rankStyle">$rankBadge</td>
                <td style="font-weight:600; color:#1e293b;">$nama</td>
                <td style="text-align:center; font-weight:bold; color:#0284c7;">$qty porsi</td>
                <td style="text-align:center; color:#64748b;">$pesananCount transaksi</td>
                <td style="text-align:right; font-weight:bold; color:#0f172a;">${_formatCurrency(nominal)}</td>
              </tr>
            ''';
          }).join();

    // Transactions Table Rows
    final txRows = transactions.isEmpty
        ? '<tr><td colspan="7" style="text-align:center; padding:12px; color:#64748b;">Tidak ada transaksi selesai pada periode ini</td></tr>'
        : transactions.asMap().entries.map((entry) {
            final idx = entry.key + 1;
            final Map<dynamic, dynamic> tx = (entry.value is Map) ? entry.value : {};
            final antrean = _escapeHtml(tx['nomor_antrian']?.toString() ?? '-');
            final meja = _escapeHtml(tx['nomor_meja']?.toString() ?? '-');
            final kode = _escapeHtml(tx['kode_pesanan']?.toString() ?? '-');
            final nama = _escapeHtml(tx['nama_pemesan']?.toString() ?? 'Tamu');
            final metodeBayar = _escapeHtml((tx['metode_pembayaran']?.toString() ?? 'tunai').toUpperCase());
            final total = _toInt(tx['total_harga'] ?? tx['total']);

            String waktu = '-';
            final dtRaw = tx['updated_at'] ?? tx['created_at'];
            if (dtRaw != null) {
              try {
                waktu = DateFormat('dd/MM HH:mm').format(DateTime.parse(dtRaw.toString()).toLocal());
              } catch (_) {
                final s = dtRaw.toString();
                waktu = s.length >= 16 ? s.substring(0, 16) : s;
              }
            }

            final itemsRaw = (tx['items'] as List<dynamic>?) ?? [];
            final itemsSummary = itemsRaw.map((it) {
              if (it is Map) {
                final n = it['nama_menu'] ?? it['nama'] ?? '-';
                final q = it['qty'] ?? it['jumlah'] ?? 1;
                return '${q}x $n';
              }
              return '';
            }).where((s) => s.isNotEmpty).join(', ');

            return '''
              <tr>
                <td style="text-align:center; color:#64748b;">$idx</td>
                <td style="font-size:11px; color:#475569; white-space:nowrap;">$waktu</td>
                <td style="font-weight:600; text-align:center;">Meja $meja<div style="font-size:10px; color:#0284c7;">#$antrean</div></td>
                <td>
                  <div style="font-weight:bold; color:#0f172a; font-size:11.5px;">$kode</div>
                  <div style="font-size:10.5px; color:#64748b;">$nama</div>
                </td>
                <td style="font-size:10.5px; color:#334155; max-width:240px;">${itemsSummary.isEmpty ? '-' : itemsSummary}</td>
                <td style="text-align:center;"><span class="pay-badge">${metodeBayar.isEmpty ? 'TUNAI' : metodeBayar}</span></td>
                <td style="text-align:right; font-weight:bold; color:#0f172a; font-size:11.5px;">${_formatCurrency(total)}</td>
              </tr>
            ''';
          }).join();

    return '''
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Transaksi - $periodeLabel</title>
<style>
  @page {
    size: A4;
    margin: 12mm 15mm;
  }
  * { box-sizing: border-box; }
  body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #1e293b;
    background: #f8fafc;
    margin: 0;
    padding: 24px;
    font-size: 11.5px;
    line-height: 1.45;
  }
  .report-sheet {
    max-width: 820px;
    margin: 0 auto;
    background: #ffffff;
    padding: 28px 32px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
  }
  .header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 2px solid #008CFF;
    padding-bottom: 14px;
    margin-bottom: 18px;
  }
  .brand-title {
    font-size: 20px;
    font-weight: 900;
    color: #0f172a;
    letter-spacing: 0.5px;
    margin: 0 0 2px 0;
  }
  .brand-sub {
    font-size: 11px;
    color: #64748b;
  }
  .doc-badge {
    display: inline-block;
    background: #008CFF;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 4px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }
  .meta-right {
    text-align: right;
  }
  .period-highlight {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
    margin-top: 4px;
  }
  
  /* KPI GRID */
  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 18px;
  }
  .kpi-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 10px 12px;
  }
  .kpi-label {
    font-size: 10px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .kpi-value {
    font-size: 16px;
    font-weight: 900;
    color: #0f172a;
    margin-top: 4px;
  }
  .kpi-desc {
    font-size: 9.5px;
    color: #94a3b8;
    margin-top: 2px;
  }
  
  /* METODE BREAKDOWN BAR */
  .metode-bar {
    background: #f1f5f9;
    border-radius: 6px;
    padding: 8px 14px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
  }
  .metode-pill {
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .metode-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
  }
  
  /* SECTION HEADERS */
  .section-title {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
    margin: 18px 0 8px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1.5px solid #e2e8f0;
    padding-bottom: 5px;
  }
  .section-count {
    font-size: 11px;
    font-weight: normal;
    color: #64748b;
  }

  /* TABLES */
  table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 16px;
  }
  th {
    background: #f1f5f9;
    color: #475569;
    font-size: 10.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding: 7px 10px;
    border: 1px solid #e2e8f0;
  }
  td {
    padding: 7px 10px;
    border: 1px solid #e2e8f0;
    font-size: 11px;
  }
  tr:nth-child(even) {
    background-color: #fafbfc;
  }
  .pay-badge {
    display: inline-block;
    padding: 2px 6px;
    font-size: 9px;
    font-weight: 700;
    border-radius: 4px;
    background: #e0f2fe;
    color: #0369a1;
  }

  /* SIGNATURES */
  .sign-container {
    display: flex;
    justify-content: space-between;
    margin-top: 28px;
    padding-top: 14px;
    page-break-inside: avoid;
  }
  .sign-box {
    width: 220px;
    text-align: center;
  }
  .sign-space {
    height: 52px;
  }
  .sign-line {
    border-top: 1px solid #64748b;
    margin-top: 4px;
    font-weight: 700;
    color: #1e293b;
    padding-top: 4px;
  }
  .footer-note {
    text-align: center;
    font-size: 9.5px;
    color: #94a3b8;
    margin-top: 24px;
    border-top: 1px dashed #cbd5e1;
    padding-top: 8px;
  }

  @media print {
    body {
      background: #ffffff;
      padding: 0;
      font-size: 10.5px;
    }
    .report-sheet {
      box-shadow: none;
      padding: 0;
      max-width: 100%;
    }
    tr {
      page-break-inside: avoid;
    }
  }
</style>
</head>
<body onload="window.print()">
  <div class="report-sheet">
    <!-- Header -->
    <div class="header">
      <div>
        <div class="brand-title">WARKOP RUMA SIDIKALANG</div>
        <div class="brand-sub">Jl. Sidikalang, Dairi, Sumatera Utara • Telp: (0627) 21xxx</div>
        <div class="brand-sub" style="margin-top:2px;">Sistem Kasir &amp; Laporan Penjualan Resmi</div>
      </div>
      <div class="meta-right">
        <div class="doc-badge">$periodBadge</div>
        <div class="period-highlight">$periodeLabel</div>
        <div style="font-size:10px; color:#64748b; margin-top:3px;">Dicetak: $nowStr</div>
      </div>
    </div>

    <!-- Executive KPI Summary -->
    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-label">Total Pesanan</div>
        <div class="kpi-value">$totalPesanan <span style="font-size:11px; font-weight:normal; color:#64748b;">Order</span></div>
        <div class="kpi-desc">Status pesanan selesai</div>
      </div>
      <div class="kpi-card" style="border-left: 3px solid #10b981;">
        <div class="kpi-label">Total Pendapatan</div>
        <div class="kpi-value" style="color:#047857;">${_formatCurrency(totalPendapatan)}</div>
        <div class="kpi-desc">Omzet bersih penjualan</div>
      </div>
      <div class="kpi-card" style="border-left: 3px solid #f59e0b;">
        <div class="kpi-label">Potongan / Promo</div>
        <div class="kpi-value" style="color:#b45309;">${_formatCurrency(totalPotongan)}</div>
        <div class="kpi-desc">Total diskon diberikan</div>
      </div>
    </div>

    <!-- Metode Pembayaran Breakdown -->
    <div class="metode-bar">
      <div style="font-weight:700; color:#334155;">Rincian Metode Bayar:</div>
      <div class="metode-pill"><div class="metode-dot" style="background:#10b981;"></div>Tunai: <b>${_formatCurrency(tunai)}</b></div>
      <div class="metode-pill"><div class="metode-dot" style="background:#0284c7;"></div>QRIS: <b>${_formatCurrency(qris)}</b></div>
      <div class="metode-pill"><div class="metode-dot" style="background:#8b5cf6;"></div>Transfer: <b>${_formatCurrency(transfer)}</b></div>
      <div class="metode-pill"><div class="metode-dot" style="background:#f59e0b;"></div>Split Bill: <b>${_formatCurrency(split)}</b></div>
    </div>

    <!-- SEKSI 1: Menu Terlaris (Paling Banyak Dipesan) -->
    <div class="section-title">
      <span>1. MENU PALING BANYAK DIPESAN (BEST SELLERS)</span>
      <span class="section-count">Top 10 Menu</span>
    </div>
    <table>
      <thead>
        <tr>
          <th style="width: 50px;">Rank</th>
          <th>Nama Menu</th>
          <th style="width: 100px;">Terjual</th>
          <th style="width: 120px;">Frekuensi</th>
          <th style="width: 130px; text-align:right;">Total Omzet</th>
        </tr>
      </thead>
      <tbody>
        $topMenuRows
      </tbody>
    </table>

    <!-- SEKSI 2: Rincian Riwayat Transaksi -->
    <div class="section-title">
      <span>2. RINCIAN RIWAYAT TRANSAKSI</span>
      <span class="section-count">Total $totalPesanan Transaksi</span>
    </div>
    <table>
      <thead>
        <tr>
          <th style="width: 30px;">No</th>
          <th style="width: 80px;">Waktu</th>
          <th style="width: 65px;">Meja</th>
          <th style="width: 130px;">Kode &amp; Pemesan</th>
          <th>Rincian Item Dipesan</th>
          <th style="width: 70px;">Metode</th>
          <th style="width: 100px; text-align:right;">Total</th>
        </tr>
      </thead>
      <tbody>
        $txRows
      </tbody>
    </table>

    <!-- Tanda Tangan & Pengesahan -->
    <div class="sign-container">
      <div class="sign-box">
        <div style="color:#64748b; font-size:10.5px;">Dicetak &amp; Diverifikasi Oleh:</div>
        <div class="sign-space"></div>
        <div class="sign-line">$printedBy</div>
        <div style="font-size:10px; color:#64748b;">Staff Kasir / Operator POS</div>
      </div>
      <div class="sign-box">
        <div style="color:#64748b; font-size:10.5px;">Mengetahui / Menyetujui:</div>
        <div class="sign-space"></div>
        <div class="sign-line">( ............................................ )</div>
        <div style="font-size:10px; color:#64748b;">Manajer Operasional / Pemilik</div>
      </div>
    </div>

    <div class="footer-note">
      Dokumen ini dicetak otomatis secara elektronik dari Sistem POS Warkop Ruma Sidikalang pada $nowStr. Valid tanpa cap basah jika ditandatangani.
    </div>
  </div>
</body>
</html>
''';
  }
}
