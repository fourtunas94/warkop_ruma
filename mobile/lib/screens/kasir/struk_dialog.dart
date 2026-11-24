import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import '../../widgets/app_widgets.dart';
import 'print_helper_stub.dart'
    if (dart.library.html) 'print_helper_web.dart' as print_helper;

class StrukDialog extends StatelessWidget {
  final Map<String, dynamic> order;
  final Map<String, dynamic>? split;

  const StrukDialog({
    super.key,
    required this.order,
    this.split,
  });

  static Future<void> showSplitReceipt(
    BuildContext context, {
    required Map<String, dynamic> order,
    required Map<String, dynamic> split,
  }) {
    return showDialog(
      context: context,
      builder: (_) => StrukDialog(order: order, split: split),
    );
  }

  static Future<void> showOrderReceipt(
    BuildContext context, {
    required Map<String, dynamic> order,
  }) {
    return showDialog(
      context: context,
      builder: (_) => StrukDialog(order: order),
    );
  }

  static bool printKitchenTicket(Map<String, dynamic> order) {
    final html = buildKitchenTicketHtml(order);
    return print_helper.printHtmlReceipt(html);
  }

  static String buildKitchenTicketHtml(Map<String, dynamic> order) {
    String escapeHtml(String s) => s
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;');

    final meja = escapeHtml(order['nomor_meja']?.toString() ?? '-');
    final kode = escapeHtml(order['kode_pesanan']?.toString() ?? '-');
    final antrian = escapeHtml(order['nomor_antrian']?.toString() ?? '-');
    final nama = escapeHtml(order['nama_pemesan']?.toString() ?? 'Tamu');
    final catatanPesanan = order['catatan']?.toString() ?? '';
    final dateStr = DateFormat('dd/MM/yyyy HH:mm').format(DateTime.now());

    final rawItems = (order['items'] as List<dynamic>?) ?? [];
    final items = rawItems.map((e) => Map<String, dynamic>.from(e as Map)).toList();

    final itemsHtml = items.map((it) {
      final qty = (it['qty'] is int) ? it['qty'] : int.tryParse(it['qty']?.toString() ?? '1') ?? 1;
      final namaMenu = escapeHtml(it['nama_menu']?.toString() ?? '-');
      final catatanItem = it['catatan']?.toString() ?? '';

      return '''
        <div class="k-item">
          <div class="k-qty">${qty}x</div>
          <div class="k-details">
            <div class="k-title">$namaMenu</div>
            ${catatanItem.isNotEmpty ? '<div class="k-note">Catatan: ' + escapeHtml(catatanItem) + '</div>' : ''}
          </div>
        </div>
      ''';
    }).join();

    final orderNoteHtml = catatanPesanan.isNotEmpty
        ? '<div class="k-order-note"><b>CATATAN PESANAN:</b><br/>${escapeHtml(catatanPesanan)}</div>'
        : '';

    return '''
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>TIKET DAPUR - Meja $meja (#$antrian)</title>
<style>
  body { font-family: 'Courier New', monospace; background: #fff; margin: 0; padding: 12px; display: flex; justify-content: center; }
  .ticket { width: 300px; color: #000; font-size: 13px; line-height: 1.4; }
  .center { text-align: center; }
  .k-header { border-bottom: 2px dashed #000; padding-bottom: 8px; margin-bottom: 10px; text-align: center; }
  .k-badge { display: inline-block; background: #000; color: #fff; font-size: 13px; font-weight: 900; padding: 2px 8px; border-radius: 3px; letter-spacing: 1px; }
  .table-box { border: 2px solid #000; padding: 6px 10px; margin: 8px 0; border-radius: 4px; text-align: center; }
  .table-title { font-size: 26px; font-weight: 900; letter-spacing: 2px; }
  .sub-info { font-size: 12px; font-weight: bold; }
  .dash { border-top: 1.5px dashed #000; margin: 10px 0; }
  .k-item { display: flex; align-items: flex-start; margin: 8px 0; border-bottom: 1px dotted #ccc; padding-bottom: 6px; }
  .k-qty { font-size: 19px; font-weight: 900; width: 42px; flex-shrink: 0; color: #000; }
  .k-details { flex: 1; }
  .k-title { font-size: 15px; font-weight: bold; color: #000; }
  .k-note { font-size: 11.5px; font-style: italic; background: #eee; padding: 2px 6px; border-radius: 3px; margin-top: 3px; display: inline-block; border-left: 3px solid #000; }
  .k-order-note { border: 1.5px solid #000; padding: 6px 8px; font-size: 12px; margin: 10px 0; background: #fafafa; }
  .k-footer { text-align: center; font-size: 11px; font-weight: bold; margin-top: 10px; letter-spacing: 1px; }
  @media print {
    body { padding: 0; }
    .ticket { width: 100%; }
  }
</style>
</head>
<body onload="window.print()">
  <div class="ticket">
    <div class="k-header">
      <div class="k-badge">TIKET DAPUR / BARISTA</div>
      <div style="font-size: 11px; margin-top: 4px; font-weight: bold;">WARKOP RUMA SIDIKALANG</div>
    </div>
    <div class="table-box">
      <div class="table-title">MEJA $meja</div>
      <div class="sub-info">ANTREAN #$antrian • $nama</div>
    </div>
    <div style="font-size: 11px;">
      <div><b>Kode:</b> $kode</div>
      <div><b>Waktu:</b> $dateStr</div>
    </div>
    <div class="dash"></div>
    <div style="font-weight: bold; font-size: 12px; margin-bottom: 6px;">DAFTAR MENU:</div>
    $itemsHtml
    $orderNoteHtml
    <div class="dash"></div>
    <div class="k-footer">*** SEGERA DIPROSES DAPUR ***</div>
  </div>
</body>
</html>
''';
  }

  int _toInt(dynamic v, {int fallback = 0}) {
    if (v is int) return v;
    if (v is double) return v.round();
    if (v is String) return int.tryParse(v) ?? fallback;
    return fallback;
  }

  String _formatCurrency(num value) {
    return NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0).format(value);
  }

  String _metodeLabel(String? m) {
    switch ((m ?? '').toLowerCase()) {
      case 'qris':
        return 'QRIS / E-Wallet';
      case 'transfer':
        return 'Transfer Bank / VA';
      case 'split':
        return 'Split Bill';
      default:
        return 'Tunai / Kasir';
    }
  }

  bool get _isSplit => split != null;

  List<Map<String, dynamic>> get _receiptItems {
    if (_isSplit) {
      final raw = (split!['items'] as List<dynamic>?) ?? [];
      return raw.map((e) => Map<String, dynamic>.from(e as Map)).toList();
    }
    final raw = (order['items'] as List<dynamic>?) ?? [];
    return raw.map((e) => Map<String, dynamic>.from(e as Map)).toList();
  }

  int get _totalAmount =>
      _isSplit ? _toInt(split!['jumlah']) : _toInt(order['total_harga']);

  int get _subtotalAmount {
    final items = _receiptItems;
    if (items.isNotEmpty) {
      return items.fold<int>(
        0,
        (sum, it) => sum + (_toInt(it['harga']) * _toInt(it['qty'], fallback: 1)),
      );
    }
    return _totalAmount;
  }

  int get _taxOrAdjustment {
    if (_isSplit) {
      if (_receiptItems.isEmpty) return 0;
      final diff = _totalAmount - _subtotalAmount;
      return diff < 0 ? diff : 0;
    }
    return 0;
  }

  int get _discountAmount => _isSplit ? 0 : _toInt(order['potongan']);

  String get _payerName => _isSplit
      ? (split!['label']?.toString() ?? 'Split Bill')
      : (order['nama_pemesan']?.toString() ?? 'Tamu');

  String get _paymentMethod => _isSplit
      ? _metodeLabel(split!['metode_pembayaran']?.toString())
      : _metodeLabel(order['metode_pembayaran']?.toString());

  bool get _isPaid => _isSplit
      ? (split!['status']?.toString().toLowerCase() == 'lunas')
      : (order['status']?.toString().toLowerCase() == 'selesai');

  String get _paidTime {
    final t = _isSplit ? split!['paid_at']?.toString() : order['waktu']?.toString();
    final date = DateFormat('dd/MM/yyyy').format(DateTime.now());
    return t != null && t.isNotEmpty ? '$date $t WIB' : DateFormat('dd/MM/yyyy HH:mm').format(DateTime.now());
  }

  String _buildPlainText() {
    final meja = order['nomor_meja']?.toString() ?? '-';
    final kode = order['kode_pesanan']?.toString() ?? '-';
    final items = _receiptItems;
    final buf = StringBuffer();
    buf.writeln('==============================');
    buf.writeln('     WARKOP RUMA SIDIKALANG   ');
    buf.writeln(_isSplit ? '       STRUK SPLIT BILL       ' : '       STRUK PEMBAYARAN       ');
    buf.writeln('==============================');
    buf.writeln('Kode   : $kode${_isSplit ? ' (SPLIT #${split!['id']})' : ''}');
    buf.writeln('Meja   : Meja $meja');
    buf.writeln('Nama   : $_payerName');
    buf.writeln('Waktu  : $_paidTime');
    buf.writeln('Metode : $_paymentMethod');
    buf.writeln('Status : ${_isPaid ? 'LUNAS' : 'BELUM LUNAS'}');
    buf.writeln('------------------------------');
    if (items.isNotEmpty) {
      for (final it in items) {
        final qty = _toInt(it['qty'], fallback: 1);
        final harga = _toInt(it['harga']);
        final nama = it['nama_menu']?.toString() ?? '-';
        buf.writeln('${qty}x $nama');
        buf.writeln('   @ ${_formatCurrency(harga)} = ${_formatCurrency(harga * qty)}');
      }
    } else if (_isSplit) {
      buf.writeln('Pembayaran Split Bill ($_payerName)');
    }
    buf.writeln('------------------------------');
    if (_receiptItems.isNotEmpty) {
      buf.writeln('Subtotal : ${_formatCurrency(_subtotalAmount)}');
      if (_discountAmount > 0) {
        buf.writeln('Diskon   : -${_formatCurrency(_discountAmount)}');
      }
      if (_taxOrAdjustment < 0) {
        buf.writeln('Diskon Prop. : -${_formatCurrency(-_taxOrAdjustment)}');
      }
    }
    buf.writeln('TOTAL    : ${_formatCurrency(_totalAmount)}');
    buf.writeln('==============================');
    buf.writeln('  Terima kasih atas kunjungan ');
    buf.writeln('         Warkop Ruma!         ');
    buf.writeln('==============================');
    return buf.toString();
  }

  String _escapeHtml(String s) => s
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;');

  String _buildPrintableHtml() {
    final meja = _escapeHtml(order['nomor_meja']?.toString() ?? '-');
    final kode = _escapeHtml(order['kode_pesanan']?.toString() ?? '-');
    final payer = _escapeHtml(_payerName);
    final metode = _escapeHtml(_paymentMethod);
    final waktu = _escapeHtml(_paidTime);
    final items = _receiptItems;

    final itemsHtml = items.isNotEmpty
        ? items.map((it) {
            final qty = _toInt(it['qty'], fallback: 1);
            final harga = _toInt(it['harga']);
            final nama = _escapeHtml(it['nama_menu']?.toString() ?? '-');
            return '''
              <div class="item-row">
                <div><b>${qty}x $nama</b><div class="muted">@ ${_formatCurrency(harga)}</div></div>
                <div><b>${_formatCurrency(harga * qty)}</b></div>
              </div>
            ''';
          }).join()
        : '<div class="item-row"><div>Bagian Split Bill ($payer)</div><div><b>${_formatCurrency(_totalAmount)}</b></div></div>';

    final taxRow = _receiptItems.isNotEmpty && _taxOrAdjustment < 0
        ? '<div class="row"><span>Diskon Proporsional</span><span>-${_formatCurrency(-_taxOrAdjustment)}</span></div>'
        : '';

    final discRow = _discountAmount > 0
        ? '<div class="row"><span>Diskon Promo</span><span>-${_formatCurrency(_discountAmount)}</span></div>'
        : '';

    return '''
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Struk $kode</title>
<style>
  body { font-family: 'Courier New', monospace; background: #f3f4f6; margin: 0; padding: 16px; display: flex; justify-content: center; }
  .receipt { width: 300px; background: #fff; padding: 18px; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,0,0,0.12); color: #111; font-size: 12px; }
  .center { text-align: center; }
  .title { font-size: 15px; font-weight: bold; letter-spacing: 1px; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; background: ${_isPaid ? '#dcfce7' : '#fef3c7'}; color: ${_isPaid ? '#15803d' : '#b45309'}; font-weight: bold; font-size: 11px; margin-top: 4px; }
  .dash { border-top: 1px dashed #999; margin: 10px 0; }
  .row, .item-row { display: flex; justify-content: space-between; margin: 4px 0; gap: 8px; }
  .muted { color: #555; font-size: 10.5px; }
  .total { font-size: 15px; font-weight: bold; }
  @media print {
    body { background: #fff; padding: 0; }
    .receipt { box-shadow: none; width: 100%; padding: 6px; }
  }
</style>
</head>
<body onload="window.print()">
  <div class="receipt">
    <div class="center">
      <div class="title">WARKOP RUMA</div>
      <div class="muted">Sidikalang • POS &amp; Barista</div>
      <div class="badge">${_isSplit ? 'STRUK SPLIT BILL' : 'STRUK PEMBAYARAN'} • ${_isPaid ? 'LUNAS' : 'BELUM LUNAS'}</div>
    </div>
    <div class="dash"></div>
    <div class="row"><span>Kode</span><span>$kode${_isSplit ? ' #${split!['id']}' : ''}</span></div>
    <div class="row"><span>Meja</span><span>Meja $meja</span></div>
    <div class="row"><span>Nama</span><span>$payer</span></div>
    <div class="row"><span>Waktu</span><span>$waktu</span></div>
    <div class="row"><span>Metode</span><span>$metode</span></div>
    <div class="dash"></div>
    $itemsHtml
    <div class="dash"></div>
    ${_receiptItems.isNotEmpty ? '<div class="row"><span>Subtotal</span><span>${_formatCurrency(_subtotalAmount)}</span></div>' : ''}
    $discRow
    $taxRow
    <div class="row total"><span>TOTAL BAYAR</span><span>${_formatCurrency(_totalAmount)}</span></div>
    <div class="dash"></div>
    <div class="center muted">Terima kasih atas kunjungan Anda!<br/>Simpan struk ini sebagai bukti pembayaran.</div>
  </div>
</body>
</html>
''';
  }

  @override
  Widget build(BuildContext context) {
    final meja = order['nomor_meja']?.toString() ?? '-';
    final kode = order['kode_pesanan']?.toString() ?? '-';
    final items = _receiptItems;

    return Dialog(
      backgroundColor: Colors.transparent,
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 380, maxHeight: 660),
        child: Container(
          decoration: BoxDecoration(
            color: const Color(0xFFFAFAFA),
            borderRadius: BorderRadius.circular(14),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.45),
                blurRadius: 24,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Top bar
              Container(
                padding: const EdgeInsets.fromLTRB(16, 12, 8, 10),
                decoration: const BoxDecoration(
                  color: Color(0xFF1E293B),
                  borderRadius: BorderRadius.vertical(top: Radius.circular(14)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.receipt_long, color: AppColors.primary, size: 18),
                        const SizedBox(width: 8),
                        Text(
                          _isSplit ? 'Struk Split Bill' : 'Struk Pembayaran',
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                            fontFamily: 'Epilogue',
                          ),
                        ),
                      ],
                    ),
                    IconButton(
                      icon: const Icon(Icons.close, color: Colors.white70, size: 18),
                      onPressed: () => Navigator.pop(context),
                    ),
                  ],
                ),
              ),

              // Paper Body
              Flexible(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(18),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const Text(
                        'WARKOP RUMA',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: Color(0xFF0F172A),
                          fontSize: 17,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 1.5,
                          fontFamily: 'monospace',
                        ),
                      ),
                      const Text(
                        'Sidikalang • POS & Barista Station',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontFamily: 'monospace'),
                      ),
                      const SizedBox(height: 8),
                      Center(
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                          decoration: BoxDecoration(
                            color: _isPaid ? const Color(0xFFDCFCE7) : const Color(0xFFFEF3C7),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            '${_isSplit ? 'SPLIT BILL' : 'PEMBAYARAN'} • ${_isPaid ? 'LUNAS' : 'BELUM LUNAS'}',
                            style: TextStyle(
                              color: _isPaid ? const Color(0xFF15803D) : const Color(0xFFB45309),
                              fontSize: 10.5,
                              fontWeight: FontWeight.bold,
                              fontFamily: 'monospace',
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),
                      _dashedDivider(),
                      const SizedBox(height: 8),
                      _metaRow('Kode', '$kode${_isSplit ? ' #${split!['id']}' : ''}'),
                      _metaRow('Meja', 'Meja $meja'),
                      _metaRow('Nama', _payerName),
                      _metaRow('Waktu', _paidTime),
                      _metaRow('Metode', _paymentMethod),
                      const SizedBox(height: 8),
                      _dashedDivider(),
                      const SizedBox(height: 8),

                      // Items
                      if (items.isNotEmpty)
                        ...items.map((it) {
                          final qty = _toInt(it['qty'], fallback: 1);
                          final harga = _toInt(it['harga']);
                          final nama = it['nama_menu']?.toString() ?? '-';
                          return Padding(
                            padding: const EdgeInsets.only(bottom: 6),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        '${qty}x $nama',
                                        style: const TextStyle(
                                          color: Color(0xFF0F172A),
                                          fontSize: 12,
                                          fontWeight: FontWeight.bold,
                                          fontFamily: 'monospace',
                                        ),
                                      ),
                                      Text(
                                        '@ ${_formatCurrency(harga)}',
                                        style: const TextStyle(
                                          color: Color(0xFF64748B),
                                          fontSize: 10.5,
                                          fontFamily: 'monospace',
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                Text(
                                  _formatCurrency(harga * qty),
                                  style: const TextStyle(
                                    color: Color(0xFF0F172A),
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                    fontFamily: 'monospace',
                                  ),
                                ),
                              ],
                            ),
                          );
                        })
                      else
                        Padding(
                          padding: const EdgeInsets.only(bottom: 6),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                'Bagian Split ($_payerName)',
                                style: const TextStyle(
                                  color: Color(0xFF0F172A),
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  fontFamily: 'monospace',
                                ),
                              ),
                              Text(
                                _formatCurrency(_totalAmount),
                                style: const TextStyle(
                                  color: Color(0xFF0F172A),
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  fontFamily: 'monospace',
                                ),
                              ),
                            ],
                          ),
                        ),

                      const SizedBox(height: 6),
                      _dashedDivider(),
                      const SizedBox(height: 8),

                      if (items.isNotEmpty) ...[
                        _metaRow('Subtotal', _formatCurrency(_subtotalAmount)),
                        if (_discountAmount > 0)
                          _metaRow('Diskon Promo', '-${_formatCurrency(_discountAmount)}'),
                        if (_taxOrAdjustment < 0)
                          _metaRow(
                            'Diskon Prop.',
                            '-${_formatCurrency(-_taxOrAdjustment)}',
                          ),
                        const SizedBox(height: 4),
                      ],
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'TOTAL BAYAR',
                            style: TextStyle(
                              color: Color(0xFF0F172A),
                              fontSize: 14,
                              fontWeight: FontWeight.w900,
                              fontFamily: 'monospace',
                            ),
                          ),
                          Text(
                            _formatCurrency(_totalAmount),
                            style: const TextStyle(
                              color: Color(0xFF0F172A),
                              fontSize: 15,
                              fontWeight: FontWeight.w900,
                              fontFamily: 'monospace',
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 12),
                      _dashedDivider(),
                      const SizedBox(height: 8),
                      const Text(
                        'Terima kasih atas kunjungan Anda!\nSimpan struk ini sebagai bukti pembayaran.',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Color(0xFF64748B), fontSize: 10.5, fontFamily: 'monospace'),
                      ),
                    ],
                  ),
                ),
              ),

              // Action Buttons
              Container(
                padding: const EdgeInsets.fromLTRB(14, 10, 14, 12),
                decoration: const BoxDecoration(
                  color: Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.vertical(bottom: Radius.circular(14)),
                  border: Border(top: BorderSide(color: Color(0xFFE2E8F0))),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          foregroundColor: const Color(0xFF334155),
                          side: const BorderSide(color: Color(0xFFCBD5E1)),
                          padding: const EdgeInsets.symmetric(vertical: 10),
                        ),
                        icon: const Icon(Icons.copy, size: 15),
                        label: const Text('Salin Teks', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold)),
                        onPressed: () {
                          Clipboard.setData(ClipboardData(text: _buildPlainText()));
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Teks struk berhasil disalin ke clipboard!'),
                              behavior: SnackBarBehavior.floating,
                            ),
                          );
                        },
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0F172A),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 10),
                        ),
                        icon: const Icon(Icons.print, size: 15),
                        label: const Text('Cetak Struk', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold)),
                        onPressed: () {
                          final printed = print_helper.printHtmlReceipt(_buildPrintableHtml());
                          if (!printed) {
                            Clipboard.setData(ClipboardData(text: _buildPlainText()));
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text('Struk siap dicetak & teks struk disalin ke clipboard.'),
                                behavior: SnackBarBehavior.floating,
                              ),
                            );
                          }
                        },
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _metaRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: Color(0xFF64748B), fontSize: 11, fontFamily: 'monospace')),
          Flexible(
            child: Text(
              value,
              textAlign: TextAlign.right,
              style: const TextStyle(
                color: Color(0xFF0F172A),
                fontSize: 11,
                fontWeight: FontWeight.w600,
                fontFamily: 'monospace',
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _dashedDivider() {
    return LayoutBuilder(
      builder: (_, constraints) {
        final count = (constraints.maxWidth / 7).floor();
        return Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: List.generate(
            count,
            (_) => const SizedBox(
              width: 4,
              height: 1,
              child: DecoratedBox(decoration: BoxDecoration(color: Color(0xFF94A3B8))),
            ),
          ),
        );
      },
    );
  }
}
