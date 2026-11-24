import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import '../../services/api_service.dart';
import 'kasir_colors.dart';
import 'struk_dialog.dart';

class SplitBillDialog extends StatefulWidget {
  final Map<String, dynamic> order;
  final VoidCallback onUpdated;

  const SplitBillDialog({
    super.key,
    required this.order,
    required this.onUpdated,
  });

  static Future<void> show(
    BuildContext context, {
    required Map<String, dynamic> order,
    required VoidCallback onUpdated,
  }) {
    return showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => SplitBillDialog(order: order, onUpdated: onUpdated),
    );
  }

  @override
  State<SplitBillDialog> createState() => _SplitBillDialogState();
}

class _SplitBillDialogState extends State<SplitBillDialog> {
  late int _orderId;
  late int _totalHarga;
  late int _potongan;
  late String _statusBayar;
  late List<dynamic> _items;
  late List<Map<String, dynamic>> _existingSplits;
  late List<Map<String, dynamic>> _unpaidItems;
  bool _hasPartialItems = false;

  // Mode setup: 'bayar_item', 'rata', 'item', 'nominal'
  String _mode = 'bayar_item';
  bool _submitting = false;
  int? _payingSplitId;

  // Mode 0: Bayar Item (Pulang Duluan)
  final TextEditingController _partialNameCtrl = TextEditingController();
  String _partialMetode = 'tunai';
  final Map<int, int> _partialSelectedQty = {};

  // Mode 1: Bagi Rata
  int _equalCount = 2;
  List<String> _equalMethods = ['tunai', 'tunai'];

  // Mode 2: Pilih Item
  int _itemGroupCount = 2;
  List<String> _itemGroupMethods = ['tunai', 'tunai'];
  // Flat list of unit items: {nama, harga, groupIdx}
  List<Map<String, dynamic>> _unitItems = [];

  // Mode 3: Nominal Bebas
  final List<_CustomSplitRow> _customRows = [];

  @override
  void initState() {
    super.initState();
    _loadOrderData(widget.order);
    _initSetupStates();
  }

  void _loadOrderData(Map<String, dynamic> order) {
    _orderId = order['id'] as int;
    _totalHarga = _toInt(order['total_harga']);
    _potongan = _toInt(order['potongan']);
    _statusBayar = (order['status'] ?? 'pending').toString().toLowerCase();
    _items = (order['items'] as List<dynamic>?) ?? [];
    final rawSplits = (order['splits'] as List<dynamic>?) ?? [];
    _existingSplits = rawSplits
        .map((e) => Map<String, dynamic>.from(e as Map))
        .toList();
    _hasPartialItems = order['has_partial_items'] == true ||
        _existingSplits.any((s) => s['tipe'] == 'bayar_item');

    final rawUnpaid = (order['unpaid_items'] as List<dynamic>?) ?? [];
    if (rawUnpaid.isNotEmpty) {
      _unpaidItems = rawUnpaid
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
    } else {
      _unpaidItems = _items.map((it) {
        final q = _toInt(it['qty'], fallback: 1);
        return {
          'item_id': _toInt(it['id']),
          'nama_menu': (it['nama_menu'] ?? '-').toString(),
          'harga': _toInt(it['harga']),
          'qty_total': q,
          'qty_paid': 0,
          'qty_remaining': q,
        };
      }).toList();
    }
    _partialSelectedQty.clear();
  }

  void _initSetupStates() {
    _equalCount = 2;
    _equalMethods = ['tunai', 'tunai'];

    _itemGroupCount = 2;
    _itemGroupMethods = ['tunai', 'tunai'];
    _unitItems = [];
    for (final it in _items) {
      final qty = _toInt(it['qty'], fallback: 1);
      final harga = _toInt(it['harga']);
      final nama = (it['nama_menu'] ?? '-').toString();
      for (int q = 0; q < qty; q++) {
        _unitItems.add({
          'nama_menu': nama,
          'harga': harga,
          'groupIdx': 0,
        });
      }
    }

    for (final row in _customRows) {
      row.dispose();
    }
    _customRows.clear();
    final half = _totalHarga ~/ 2;
    _customRows.add(_CustomSplitRow(label: 'Orang 1', amount: half, metode: 'tunai'));
    _customRows.add(_CustomSplitRow(label: 'Orang 2', amount: _totalHarga - half, metode: 'qris'));
  }

  @override
  void dispose() {
    _partialNameCtrl.dispose();
    for (final row in _customRows) {
      row.dispose();
    }
    super.dispose();
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

  String _metodeLabel(String m) {
    switch (m) {
      case 'qris':
        return 'QRIS';
      case 'transfer':
        return 'Transfer';
      default:
        return 'Tunai';
    }
  }

  void _showSnack(String msg, bool success) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg, style: const TextStyle(color: AppColors.onSurface)),
        backgroundColor: success ? AppColors.surfaceContainerHigh : AppColors.errorContainer,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  Future<void> _reloadOrderFromServer() async {
    final res = await ApiService.kasirPosDashboard();
    if (res['success'] == true && mounted) {
      final list = (res['pesanans'] as List<dynamic>?) ?? [];
      final match = list.where((o) => o['id'] == _orderId).toList();
      if (match.isNotEmpty) {
        setState(() {
          _loadOrderData(Map<String, dynamic>.from(match.first as Map));
        });
      }
    }
    widget.onUpdated();
  }

  // ─── CALCULATIONS ──────────────────────────────────────────────────────────

  List<Map<String, dynamic>> _buildEqualSplitsPayload() {
    final base = _totalHarga ~/ _equalCount;
    final rem = _totalHarga - (base * _equalCount);
    return List.generate(_equalCount, (i) {
      final amount = base + (i == _equalCount - 1 ? rem : 0);
      return {
        'label': 'Orang ${i + 1}',
        'jumlah': amount,
        'metode_pembayaran': _equalMethods[i],
      };
    });
  }

  List<Map<String, dynamic>> _buildItemSplitsPayload() {
    // Group unit items by groupIdx
    final Map<int, List<Map<String, dynamic>>> grouped = {};
    for (int i = 0; i < _itemGroupCount; i++) {
      grouped[i] = [];
    }
    for (final u in _unitItems) {
      final idx = (u['groupIdx'] as int).clamp(0, _itemGroupCount - 1);
      grouped[idx]!.add(u);
    }

    final int subtotalAll = _unitItems.fold<int>(0, (sum, u) => sum + (u['harga'] as int));
    final List<int> activeGroups = grouped.entries
        .where((e) => e.value.isNotEmpty)
        .map((e) => e.key)
        .toList();

    if (activeGroups.isEmpty) return [];

    final List<Map<String, dynamic>> result = [];
    int allocated = 0;

    for (int idxPos = 0; idxPos < activeGroups.length; idxPos++) {
      final gIdx = activeGroups[idxPos];
      final units = grouped[gIdx]!;
      final gSub = units.fold<int>(0, (s, u) => s + (u['harga'] as int));

      int gTotal;
      if (idxPos == activeGroups.length - 1) {
        gTotal = _totalHarga - allocated;
      } else if (subtotalAll > 0) {
        gTotal = ((_totalHarga * gSub) / subtotalAll).round();
        allocated += gTotal;
      } else {
        gTotal = 0;
      }

      // Collapse identical menu names into qty counts
      final Map<String, Map<String, dynamic>> collapsed = {};
      for (final u in units) {
        final key = '${u['nama_menu']}_${u['harga']}';
        if (collapsed.containsKey(key)) {
          collapsed[key]!['qty'] = (collapsed[key]!['qty'] as int) + 1;
        } else {
          collapsed[key] = {
            'nama_menu': u['nama_menu'],
            'harga': u['harga'],
            'qty': 1,
          };
        }
      }

      result.add({
        'label': 'Orang ${gIdx + 1}',
        'jumlah': gTotal,
        'metode_pembayaran': _itemGroupMethods[gIdx],
        'items': collapsed.values.toList(),
      });
    }

    return result;
  }

  List<Map<String, dynamic>> _buildCustomSplitsPayload() {
    return List.generate(_customRows.length, (i) {
      final row = _customRows[i];
      final amount = int.tryParse(row.amountCtrl.text.replaceAll(RegExp(r'[^0-9]'), '')) ?? 0;
      final label = row.labelCtrl.text.trim().isEmpty ? 'Orang ${i + 1}' : row.labelCtrl.text.trim();
      return {
        'label': label,
        'jumlah': amount,
        'metode_pembayaran': row.metode,
      };
    });
  }

  Future<void> _submitCreateSplit() async {
    List<Map<String, dynamic>> payload;
    if (_mode == 'rata') {
      payload = _buildEqualSplitsPayload();
    } else if (_mode == 'item') {
      payload = _buildItemSplitsPayload();
      if (payload.length < 2) {
        _showSnack('Minimal 2 orang harus memiliki item agar bisa split bill.', false);
        return;
      }
    } else {
      payload = _buildCustomSplitsPayload();
      final total = payload.fold<int>(0, (s, e) => s + (e['jumlah'] as int));
      if (total != _totalHarga) {
        _showSnack(
          'Total pembagian (${_formatCurrency(total)}) harus sama dengan tagihan (${_formatCurrency(_totalHarga)}).',
          false,
        );
        return;
      }
    }

    if (payload.any((e) => (e['jumlah'] as int) <= 0)) {
      _showSnack('Setiap bagian harus memiliki nominal lebih dari Rp 0.', false);
      return;
    }

    setState(() => _submitting = true);
    final res = await ApiService.createSplitBill(
      _orderId,
      tipe: _mode,
      splits: payload,
    );
    if (!mounted) return;
    setState(() => _submitting = false);

    if (res['success'] == true) {
      _showSnack(res['message']?.toString() ?? 'Split bill berhasil dibuat!', true);
      await _reloadOrderFromServer();
    } else {
      _showSnack(res['message']?.toString() ?? 'Gagal membuat split bill.', false);
    }
  }

  Future<void> _paySplit(Map<String, dynamic> split) async {
    final splitId = split['id'] as int;
    final metode = (split['metode_pembayaran'] ?? 'tunai').toString();

    setState(() => _payingSplitId = splitId);
    final res = await ApiService.bayarSplitBill(
      _orderId,
      splitId,
      metodePembayaran: metode,
    );
    if (!mounted) return;
    setState(() => _payingSplitId = null);

    if (res['success'] == true) {
      _showSnack(res['message']?.toString() ?? 'Pembayaran bagian berhasil!', true);
      await _reloadOrderFromServer();
    } else {
      _showSnack(res['message']?.toString() ?? 'Gagal memproses pembayaran.', false);
    }
  }

  Future<void> _resetSplit() async {
    setState(() => _submitting = true);
    final res = await ApiService.resetSplitBill(_orderId);
    if (!mounted) return;
    setState(() => _submitting = false);

    if (res['success'] == true) {
      _showSnack('Split bill dibatalkan. Anda dapat mengatur ulang pembagian.', true);
      _initSetupStates();
      await _reloadOrderFromServer();
    } else {
      _showSnack(res['message']?.toString() ?? 'Gagal membatalkan split bill.', false);
    }
  }

  // ─── BAYAR ITEM (PULANG DULUAN) HELPERS ────────────────────────────────────

  int get _totalTerbayarNow => _existingSplits
      .where((s) => s['status'] == 'lunas')
      .fold<int>(0, (sum, s) => sum + _toInt(s['jumlah']));

  int get _sisaTagihanNow => (_totalHarga - _totalTerbayarNow).clamp(0, _totalHarga);

  bool _isPayingAllRemaining() {
    bool hasAnyRemaining = false;
    for (final u in _unpaidItems) {
      final rem = _toInt(u['qty_remaining']);
      if (rem > 0) {
        hasAnyRemaining = true;
        final sel = _partialSelectedQty[_toInt(u['item_id'])] ?? 0;
        if (sel < rem) return false;
      }
    }
    return hasAnyRemaining;
  }

  int _calcPartialEstimate() {
    int remSubtotalAll = 0;
    int selSubtotal = 0;
    for (final u in _unpaidItems) {
      final itemId = _toInt(u['item_id']);
      final harga = _toInt(u['harga']);
      final rem = _toInt(u['qty_remaining']);
      final sel = (_partialSelectedQty[itemId] ?? 0).clamp(0, rem);
      remSubtotalAll += harga * rem;
      selSubtotal += harga * sel;
    }
    if (selSubtotal <= 0) return 0;
    final sisa = _sisaTagihanNow;
    if (_isPayingAllRemaining()) return sisa;
    if (remSubtotalAll <= 0) return 0;
    final est = ((sisa * selSubtotal) / remSubtotalAll).round();
    if (sisa > 1) return est.clamp(1, sisa - 1);
    return est;
  }

  void _selectAllRemainingItems() {
    setState(() {
      for (final u in _unpaidItems) {
        final itemId = _toInt(u['item_id']);
        final rem = _toInt(u['qty_remaining']);
        if (rem > 0) {
          _partialSelectedQty[itemId] = rem;
        }
      }
    });
  }

  Future<void> _submitPartialItemPayment() async {
    final selectedItems = <Map<String, dynamic>>[];
    for (final u in _unpaidItems) {
      final itemId = _toInt(u['item_id']);
      final rem = _toInt(u['qty_remaining']);
      final sel = (_partialSelectedQty[itemId] ?? 0).clamp(0, rem);
      if (sel > 0) {
        selectedItems.add({'item_id': itemId, 'qty': sel});
      }
    }

    if (selectedItems.isEmpty) {
      _showSnack('Pilih minimal 1 menu yang akan dibayar.', false);
      return;
    }

    final label = _partialNameCtrl.text.trim().isEmpty
        ? 'Bayar Item #${_existingSplits.length + 1}'
        : _partialNameCtrl.text.trim();

    setState(() => _submitting = true);
    final res = await ApiService.bayarSebagianItem(
      _orderId,
      label: label,
      metodePembayaran: _partialMetode,
      items: selectedItems,
    );
    if (!mounted) return;
    setState(() => _submitting = false);

    if (res['success'] == true) {
      _partialNameCtrl.clear();
      _partialSelectedQty.clear();
      _showSnack(res['message']?.toString() ?? 'Pembayaran item berhasil dicatat!', true);
      await _reloadOrderFromServer();
    } else {
      _showSnack(res['message']?.toString() ?? 'Gagal memproses pembayaran item.', false);
    }
  }

  // ─── BUILD ─────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final meja = widget.order['nomor_meja']?.toString() ?? '-';
    final nama = widget.order['nama_pemesan']?.toString() ?? 'Tamu';
    final kode = widget.order['kode_pesanan']?.toString() ?? '#WR-$_orderId';

    return Theme(
      data: AppColors.lightTheme,
      child: Dialog(
        backgroundColor: AppColors.surfaceContainer,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: BorderSide(color: AppColors.outlineVariant),
        ),
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 560, maxHeight: 720),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Header
            Container(
              padding: const EdgeInsets.fromLTRB(18, 16, 12, 14),
              decoration: const BoxDecoration(
                color: AppColors.surfaceContainerLow,
                borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
                border: Border(bottom: BorderSide(color: AppColors.surfaceContainerHigh)),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withValues(alpha: 0.16),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.call_split, color: AppColors.primary, size: 20),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Split Bill Pesanan',
                          style: TextStyle(
                            color: AppColors.onSurface,
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            fontFamily: 'Epilogue',
                          ),
                        ),
                        Text(
                          'Meja $meja — $nama ($kode)',
                          style: const TextStyle(
                            color: AppColors.onSurfaceVariant,
                            fontSize: 11,
                            fontFamily: 'SpaceGrotesk',
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: AppColors.surfaceContainerHighest,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      _formatCurrency(_totalHarga),
                      style: const TextStyle(
                        color: AppColors.primary,
                        fontWeight: FontWeight.bold,
                        fontSize: 13,
                        fontFamily: 'SpaceGrotesk',
                      ),
                    ),
                  ),
                  const SizedBox(width: 4),
                  IconButton(
                    icon: const Icon(Icons.close, color: AppColors.onSurfaceVariant, size: 20),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
            ),

            // Body
            Flexible(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: _existingSplits.isNotEmpty
                    ? (_hasPartialItems ? _buildPartialActiveView() : _buildActiveSplitsView())
                    : _buildSetupSplitView(),
              ),
            ),

            // Footer
            Container(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 14),
              decoration: const BoxDecoration(
                color: AppColors.surfaceContainerLow,
                borderRadius: BorderRadius.vertical(bottom: Radius.circular(16)),
                border: Border(top: BorderSide(color: AppColors.surfaceContainerHigh)),
              ),
              child: _existingSplits.isNotEmpty
                  ? (_hasPartialItems ? _buildPartialActiveFooter() : _buildActiveFooter())
                  : _buildSetupFooter(),
            ),
          ],
        ),
      ),
      ),
    );
  }

  // ─── ACTIVE SPLITS VIEW ────────────────────────────────────────────────────

  Widget _buildActiveSplitsView() {
    final totalTerbayar = _existingSplits
        .where((s) => s['status'] == 'lunas')
        .fold<int>(0, (sum, s) => sum + _toInt(s['jumlah']));
    final sisa = (_totalHarga - totalTerbayar).clamp(0, _totalHarga);
    final progress = _totalHarga > 0 ? (totalTerbayar / _totalHarga).clamp(0.0, 1.0) : 1.0;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Progress Card
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLow,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.3)),
          ),
          child: Column(
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('TERBAYAR', style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 9.5, fontFamily: 'SpaceGrotesk')),
                      Text(
                        _formatCurrency(totalTerbayar),
                        style: const TextStyle(color: AppColors.green, fontSize: 15, fontWeight: FontWeight.bold, fontFamily: 'Epilogue'),
                      ),
                    ],
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      const Text('SISA TAGIHAN', style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 9.5, fontFamily: 'SpaceGrotesk')),
                      Text(
                        _formatCurrency(sisa),
                        style: TextStyle(
                          color: sisa == 0 ? AppColors.green : AppColors.primary,
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          fontFamily: 'Epilogue',
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 10),
              ClipRRect(
                borderRadius: BorderRadius.circular(6),
                child: LinearProgressIndicator(
                  value: progress,
                  minHeight: 7,
                  backgroundColor: AppColors.surfaceContainerHighest,
                  valueColor: AlwaysStoppedAnimation<Color>(sisa == 0 ? AppColors.green : AppColors.primary),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),

        const Text(
          'Daftar Pembayaran Split Bill',
          style: TextStyle(
            color: AppColors.onSurface,
            fontSize: 12.5,
            fontWeight: FontWeight.bold,
            fontFamily: 'SpaceGrotesk',
          ),
        ),
        const SizedBox(height: 8),

        ..._existingSplits.map((split) {
          final isLunas = split['status'] == 'lunas';
          final isPaying = _payingSplitId == split['id'];
          final splitItems = (split['items'] as List<dynamic>?) ?? [];
          final metode = (split['metode_pembayaran'] ?? 'tunai').toString();

          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLow,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: isLunas
                    ? AppColors.green.withValues(alpha: 0.45)
                    : AppColors.outlineVariant.withValues(alpha: 0.35),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      isLunas ? Icons.check_circle : Icons.receipt_outlined,
                      color: isLunas ? AppColors.green : AppColors.primary,
                      size: 18,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            split['label']?.toString() ?? 'Bagian',
                            style: const TextStyle(
                              color: AppColors.onSurface,
                              fontWeight: FontWeight.bold,
                              fontSize: 13,
                              fontFamily: 'Epilogue',
                            ),
                          ),
                          Text(
                            isLunas
                                ? 'Lunas via ${_metodeLabel(metode)}${split['paid_at'] != null ? ' • ${split['paid_at']}' : ''}'
                                : 'Menunggu pembayaran',
                            style: TextStyle(
                              color: isLunas ? AppColors.green : AppColors.onSurfaceVariant,
                              fontSize: 10.5,
                              fontFamily: 'SpaceGrotesk',
                            ),
                          ),
                        ],
                      ),
                    ),
                    Text(
                      _formatCurrency(_toInt(split['jumlah'])),
                      style: TextStyle(
                        color: isLunas ? AppColors.green : AppColors.primary,
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                        fontFamily: 'SpaceGrotesk',
                      ),
                    ),
                    if (isLunas) ...[
                      const SizedBox(width: 8),
                      OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.primary,
                          side: BorderSide(color: AppColors.primary.withValues(alpha: 0.5)),
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          minimumSize: Size.zero,
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                        icon: const Icon(Icons.receipt_long, size: 13),
                        label: const Text('Struk', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold)),
                        onPressed: () => StrukDialog.showSplitReceipt(
                          context,
                          order: widget.order,
                          split: split,
                        ),
                      ),
                    ],
                  ],
                ),

                if (splitItems.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 6,
                    runSpacing: 4,
                    children: splitItems.map((it) {
                      return Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                        decoration: BoxDecoration(
                          color: AppColors.surfaceContainerHighest,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          '${it['qty']}x ${it['nama_menu']}',
                          style: const TextStyle(color: AppColors.onSurfaceVariant, fontSize: 10),
                        ),
                      );
                    }).toList(),
                  ),
                ],

                if (!isLunas) ...[
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Expanded(
                        child: _buildMethodSelector(
                          selected: metode,
                          onChanged: (val) => setState(() => split['metode_pembayaran'] = val),
                        ),
                      ),
                      const SizedBox(width: 10),
                      ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: AppColors.onPrimary,
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                        onPressed: isPaying ? null : () => _paySplit(split),
                        icon: isPaying
                            ? const SizedBox(
                                width: 14,
                                height: 14,
                                child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.onPrimary),
                              )
                            : const Icon(Icons.payments_outlined, size: 15),
                        label: Text(
                          isPaying ? 'Memproses...' : 'Bayar',
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          );
        }),
      ],
    );
  }

  Widget _buildActiveFooter() {
    final hasPaid = _existingSplits.any((s) => s['status'] == 'lunas');
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        if (!hasPaid && _statusBayar != 'selesai')
          TextButton.icon(
            onPressed: _submitting ? null : _resetSplit,
            icon: const Icon(Icons.restart_alt, size: 16, color: AppColors.error),
            label: const Text(
              'Reset Split',
              style: TextStyle(color: AppColors.error, fontSize: 12, fontWeight: FontWeight.w600),
            ),
          )
        else
          const SizedBox.shrink(),
        ElevatedButton(
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.surfaceContainerHighest,
            foregroundColor: AppColors.onSurface,
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          ),
          onPressed: () => Navigator.pop(context),
          child: const Text('Tutup', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
        ),
      ],
    );
  }

  // ─── PARTIAL ITEM ACTIVE VIEW (PULANG DULUAN) ─────────────────────────────

  Widget _buildPartialActiveView() {
    final totalTerbayar = _totalTerbayarNow;
    final sisa = _sisaTagihanNow;
    final progress = _totalHarga > 0 ? (totalTerbayar / _totalHarga).clamp(0.0, 1.0) : 1.0;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Progress Card
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLow,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.3)),
          ),
          child: Column(
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('TERBAYAR', style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 9.5, fontFamily: 'SpaceGrotesk')),
                      Text(
                        _formatCurrency(totalTerbayar),
                        style: const TextStyle(color: AppColors.green, fontSize: 15, fontWeight: FontWeight.bold, fontFamily: 'Epilogue'),
                      ),
                    ],
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      const Text('SISA TAGIHAN', style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 9.5, fontFamily: 'SpaceGrotesk')),
                      Text(
                        _formatCurrency(sisa),
                        style: TextStyle(
                          color: sisa == 0 ? AppColors.green : AppColors.primary,
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          fontFamily: 'Epilogue',
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 10),
              ClipRRect(
                borderRadius: BorderRadius.circular(6),
                child: LinearProgressIndicator(
                  value: progress,
                  minHeight: 7,
                  backgroundColor: AppColors.surfaceContainerHighest,
                  valueColor: AlwaysStoppedAnimation<Color>(sisa == 0 ? AppColors.green : AppColors.primary),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),

        const Text(
          'Sudah Dibayar',
          style: TextStyle(color: AppColors.green, fontSize: 12, fontWeight: FontWeight.bold, fontFamily: 'SpaceGrotesk'),
        ),
        const SizedBox(height: 6),

        ..._existingSplits.map((split) {
          final splitItems = (split['items'] as List<dynamic>?) ?? [];
          final metode = (split['metode_pembayaran'] ?? 'tunai').toString();
          return Container(
            margin: const EdgeInsets.only(bottom: 8),
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLow,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: AppColors.green.withValues(alpha: 0.4)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.check_circle, color: AppColors.green, size: 16),
                    const SizedBox(width: 7),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            split['label']?.toString() ?? 'Bayar Item',
                            style: const TextStyle(color: AppColors.onSurface, fontWeight: FontWeight.bold, fontSize: 12.5),
                          ),
                          Text(
                            'Lunas via ${_metodeLabel(metode)}${split['paid_at'] != null ? ' • ${split['paid_at']}' : ''}',
                            style: const TextStyle(color: AppColors.green, fontSize: 10),
                          ),
                        ],
                      ),
                    ),
                    Text(
                      _formatCurrency(_toInt(split['jumlah'])),
                      style: const TextStyle(color: AppColors.green, fontWeight: FontWeight.bold, fontSize: 13),
                    ),
                    const SizedBox(width: 8),
                    OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.primary,
                        side: BorderSide(color: AppColors.primary.withValues(alpha: 0.5)),
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        minimumSize: Size.zero,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      ),
                      icon: const Icon(Icons.receipt_long, size: 13),
                      label: const Text('Struk', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold)),
                      onPressed: () => StrukDialog.showSplitReceipt(
                        context,
                        order: widget.order,
                        split: split,
                      ),
                    ),
                  ],
                ),
                if (splitItems.isNotEmpty) ...[
                  const SizedBox(height: 6),
                  Wrap(
                    spacing: 6,
                    runSpacing: 4,
                    children: splitItems.map((it) {
                      return Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                        decoration: BoxDecoration(
                          color: AppColors.surfaceContainerHighest,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          '${it['qty']}x ${it['nama_menu']}',
                          style: const TextStyle(color: AppColors.onSurfaceVariant, fontSize: 10),
                        ),
                      );
                    }).toList(),
                  ),
                ],
              ],
            ),
          );
        }),

        if (_statusBayar != 'selesai' && sisa > 0) ...[
          const Divider(color: AppColors.surfaceContainerHigh, height: 22),
          const Text(
            'Bayar Sisa Menu Belum Lunas',
            style: TextStyle(color: AppColors.primary, fontSize: 12.5, fontWeight: FontWeight.bold, fontFamily: 'SpaceGrotesk'),
          ),
          const SizedBox(height: 8),
          _buildPartialItemPicker(),
        ],
      ],
    );
  }

  Widget _buildPartialActiveFooter() {
    if (_statusBayar == 'selesai' || _sisaTagihanNow <= 0) {
      return Row(
        mainAxisAlignment: MainAxisAlignment.end,
        children: [
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.green,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () => Navigator.pop(context),
            child: const Text('Selesai (Lunas)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
          ),
        ],
      );
    }

    final est = _calcPartialEstimate();
    final payAll = _isPayingAllRemaining();
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        TextButton(
          onPressed: _submitting ? null : () => Navigator.pop(context),
          child: const Text('Tutup', style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 12)),
        ),
        ElevatedButton.icon(
          style: ElevatedButton.styleFrom(
            backgroundColor: payAll ? AppColors.green : AppColors.primary,
            foregroundColor: payAll ? Colors.white : AppColors.onPrimary,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(9)),
          ),
          onPressed: (_submitting || est <= 0) ? null : _submitPartialItemPayment,
          icon: _submitting
              ? const SizedBox(
                  width: 14,
                  height: 14,
                  child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.onPrimary),
                )
              : const Icon(Icons.payments_outlined, size: 15),
          label: Text(
            _submitting
                ? 'Memproses...'
                : (payAll
                    ? 'Lunasi Semua Sisa (${_formatCurrency(est)})'
                    : (est > 0 ? 'Bayar Item (${_formatCurrency(est)})' : 'Pilih Menu Dulu')),
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
          ),
        ),
      ],
    );
  }

  Widget _buildPartialItemPicker() {
    final est = _calcPartialEstimate();
    final remainingList = _unpaidItems.where((u) => _toInt(u['qty_remaining']) > 0).toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: AppColors.primary.withValues(alpha: 0.08),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: AppColors.primary.withValues(alpha: 0.25)),
          ),
          child: const Row(
            children: [
              Icon(Icons.info_outline, size: 15, color: AppColors.primary),
              SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Pilih menu yang mau dibayar sekarang (misal ada yang pulang duluan). Menu lain tetap terbuka untuk dibayar nanti.',
                  style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 10.5),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 10),

        // Nama Pembayar + Tombol Pilih Semua Sisa
        Row(
          children: [
            Expanded(
              child: TextField(
                controller: _partialNameCtrl,
                style: const TextStyle(color: AppColors.onSurface, fontSize: 12.5),
                decoration: const InputDecoration(
                  labelText: 'Nama Pembayar (opsional, misal: Karan)',
                  isDense: true,
                  contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 9),
                ),
              ),
            ),
            const SizedBox(width: 8),
            OutlinedButton(
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.secondary,
                side: BorderSide(color: AppColors.secondary.withValues(alpha: 0.5)),
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
              ),
              onPressed: remainingList.isEmpty ? null : _selectAllRemainingItems,
              child: const Text('Pilih Semua Sisa', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
        const SizedBox(height: 10),

        // List sisa menu yang belum dibayar
        ...remainingList.map((u) {
          final itemId = _toInt(u['item_id']);
          final nama = u['nama_menu']?.toString() ?? '-';
          final harga = _toInt(u['harga']);
          final rem = _toInt(u['qty_remaining']);
          final paid = _toInt(u['qty_paid']);
          final sel = (_partialSelectedQty[itemId] ?? 0).clamp(0, rem);
          final isChecked = sel > 0;

          return Container(
            margin: const EdgeInsets.only(bottom: 7),
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLow,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: isChecked
                    ? AppColors.primary
                    : AppColors.outlineVariant.withValues(alpha: 0.25),
              ),
            ),
            child: Row(
              children: [
                Checkbox(
                  value: isChecked,
                  activeColor: AppColors.primary,
                  checkColor: AppColors.onPrimary,
                  visualDensity: VisualDensity.compact,
                  onChanged: (val) {
                    setState(() {
                      _partialSelectedQty[itemId] = val == true ? rem : 0;
                    });
                  },
                ),
                const SizedBox(width: 4),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        nama,
                        style: const TextStyle(color: AppColors.onSurface, fontSize: 12.5, fontWeight: FontWeight.bold),
                      ),
                      Text(
                        '${_formatCurrency(harga)} / porsi • Sisa $rem${paid > 0 ? ' ($paid sudah lunas)' : ''}',
                        style: const TextStyle(color: AppColors.onSurfaceVariant, fontSize: 10.5),
                      ),
                    ],
                  ),
                ),
                if (rem > 1)
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      _stepperBtn(
                        Icons.remove,
                        sel > 0 ? () => setState(() => _partialSelectedQty[itemId] = sel - 1) : null,
                      ),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 8),
                        child: Text(
                          '$sel',
                          style: TextStyle(
                            color: sel > 0 ? AppColors.primary : AppColors.onSurfaceVariant,
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                          ),
                        ),
                      ),
                      _stepperBtn(
                        Icons.add,
                        sel < rem ? () => setState(() => _partialSelectedQty[itemId] = sel + 1) : null,
                      ),
                    ],
                  )
                else
                  Text(
                    _formatCurrency(harga),
                    style: TextStyle(
                      color: isChecked ? AppColors.primary : AppColors.onSurfaceVariant,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                  ),
              ],
            ),
          );
        }),

        const SizedBox(height: 10),
        // Metode Bayar + Estimasi Total
        Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLowest,
            borderRadius: BorderRadius.circular(10),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('METODE BAYAR', style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 9.5)),
                  const SizedBox(height: 4),
                  _buildMethodSelector(
                    selected: _partialMetode,
                    onChanged: (val) => setState(() => _partialMetode = val),
                  ),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  const Text(
                    'TOTAL BAYAR',
                    style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 9.5),
                  ),
                  Text(
                    _formatCurrency(est),
                    style: const TextStyle(
                      color: AppColors.primary,
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      fontFamily: 'Epilogue',
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ],
    );
  }

  // ─── SETUP SPLIT VIEW ──────────────────────────────────────────────────────

  Widget _buildSetupSplitView() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Mode Tabs
        Row(
          children: [
            _buildModeTab('bayar_item', 'Bayar Item', Icons.shopping_bag_outlined),
            const SizedBox(width: 6),
            _buildModeTab('rata', 'Bagi Rata', Icons.groups_outlined),
            const SizedBox(width: 6),
            _buildModeTab('item', 'Bagi Grup', Icons.checklist_rtl),
            const SizedBox(width: 6),
            _buildModeTab('nominal', 'Nominal', Icons.edit_note),
          ],
        ),
        const SizedBox(height: 14),

        if (_mode == 'bayar_item') _buildPartialItemPicker(),
        if (_mode == 'rata') _buildEqualMode(),
        if (_mode == 'item') _buildItemMode(),
        if (_mode == 'nominal') _buildCustomMode(),
      ],
    );
  }

  Widget _buildModeTab(String key, String label, IconData icon) {
    final active = _mode == key;
    return Expanded(
      child: InkWell(
        onTap: () => setState(() => _mode = key),
        borderRadius: BorderRadius.circular(10),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
          decoration: BoxDecoration(
            color: active ? AppColors.primary.withValues(alpha: 0.16) : AppColors.surfaceContainerLow,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: active ? AppColors.primary : AppColors.outlineVariant.withValues(alpha: 0.35),
            ),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 15, color: active ? AppColors.primary : AppColors.onSurfaceVariant),
              const SizedBox(width: 6),
              Flexible(
                child: Text(
                  label,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: active ? AppColors.primary : AppColors.onSurfaceVariant,
                    fontSize: 11.5,
                    fontWeight: active ? FontWeight.bold : FontWeight.w500,
                    fontFamily: 'SpaceGrotesk',
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ── MODE 1: BAGI RATA ──
  Widget _buildEqualMode() {
    final preview = _buildEqualSplitsPayload();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLow,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Jumlah Orang / Bagian',
                    style: TextStyle(color: AppColors.onSurface, fontSize: 12.5, fontWeight: FontWeight.bold),
                  ),
                  Row(
                    children: [
                      _stepperBtn(Icons.remove, _equalCount > 2 ? () => _setEqualCount(_equalCount - 1) : null),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 14),
                        child: Text(
                          '$_equalCount',
                          style: const TextStyle(color: AppColors.primary, fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                      ),
                      _stepperBtn(Icons.add, _equalCount < 12 ? () => _setEqualCount(_equalCount + 1) : null),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Wrap(
                spacing: 8,
                children: [2, 3, 4, 5, 6].map((n) {
                  final sel = _equalCount == n;
                  return ChoiceChip(
                    label: Text('$n Orang', style: TextStyle(fontSize: 11, color: sel ? AppColors.onPrimary : AppColors.onSurface)),
                    selected: sel,
                    selectedColor: AppColors.primary,
                    backgroundColor: AppColors.surfaceContainerHigh,
                    onSelected: (_) => _setEqualCount(n),
                  );
                }).toList(),
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),

        ...List.generate(preview.length, (i) {
          final item = preview[i];
          return Container(
            margin: const EdgeInsets.only(bottom: 8),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLow,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.25)),
            ),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(item['label'], style: const TextStyle(color: AppColors.onSurface, fontWeight: FontWeight.bold, fontSize: 12.5)),
                      Text(_formatCurrency(item['jumlah']), style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 13)),
                    ],
                  ),
                ),
                _buildMethodSelector(
                  selected: _equalMethods[i],
                  onChanged: (val) => setState(() => _equalMethods[i] = val),
                ),
              ],
            ),
          );
        }),
      ],
    );
  }

  void _setEqualCount(int count) {
    setState(() {
      _equalCount = count;
      while (_equalMethods.length < count) {
        _equalMethods.add('tunai');
      }
      if (_equalMethods.length > count) {
        _equalMethods = _equalMethods.sublist(0, count);
      }
    });
  }

  // ── MODE 2: PILIH ITEM ──
  Widget _buildItemMode() {
    final preview = _buildItemSplitsPayload();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLow,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Jumlah Orang', style: TextStyle(color: AppColors.onSurface, fontSize: 12.5, fontWeight: FontWeight.bold)),
                  Text('Tap nomor orang pada tiap item di bawah', style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 10)),
                ],
              ),
              Row(
                children: [
                  _stepperBtn(Icons.remove, _itemGroupCount > 2 ? () => _setItemGroupCount(_itemGroupCount - 1) : null),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 14),
                    child: Text('$_itemGroupCount', style: const TextStyle(color: AppColors.primary, fontSize: 16, fontWeight: FontWeight.bold)),
                  ),
                  _stepperBtn(Icons.add, _itemGroupCount < 8 ? () => _setItemGroupCount(_itemGroupCount + 1) : null),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),

        if (_unitItems.isEmpty)
          const Padding(
            padding: EdgeInsets.all(16),
            child: Text('Tidak ada item pada pesanan ini.', style: TextStyle(color: AppColors.onSurfaceVariant)),
          )
        else
          ...List.generate(_unitItems.length, (idx) {
            final u = _unitItems[idx];
            final selectedGroup = u['groupIdx'] as int;
            return Container(
              margin: const EdgeInsets.only(bottom: 8),
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLow,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.25)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          '1x ${u['nama_menu']}',
                          style: const TextStyle(color: AppColors.onSurface, fontSize: 12.5, fontWeight: FontWeight.w600),
                        ),
                      ),
                      Text(
                        _formatCurrency(u['harga']),
                        style: const TextStyle(color: AppColors.primary, fontSize: 12, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Wrap(
                    spacing: 6,
                    runSpacing: 4,
                    children: List.generate(_itemGroupCount, (gIdx) {
                      final active = selectedGroup == gIdx;
                      return InkWell(
                        onTap: () => setState(() => _unitItems[idx]['groupIdx'] = gIdx),
                        borderRadius: BorderRadius.circular(6),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                          decoration: BoxDecoration(
                            color: active ? AppColors.primary : AppColors.surfaceContainerHigh,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            'Orang ${gIdx + 1}',
                            style: TextStyle(
                              color: active ? AppColors.onPrimary : AppColors.onSurfaceVariant,
                              fontSize: 10.5,
                              fontWeight: active ? FontWeight.bold : FontWeight.normal,
                            ),
                          ),
                        ),
                      );
                    }),
                  ),
                ],
              ),
            );
          }),

        const SizedBox(height: 10),
        if (_potongan > 0)
          const Padding(
            padding: EdgeInsets.only(bottom: 8),
            child: Text(
              'Catatan: Diskon dibagi secara proporsional mengikuti nilai item.',
              style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 10, fontStyle: FontStyle.italic),
            ),
          ),

        // Ringkasan per orang
        if (preview.isNotEmpty) ...[
          const Text(
            'Ringkasan per Orang',
            style: TextStyle(color: AppColors.onSurface, fontSize: 12, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 6),
          ...preview.map((p) {
            final label = p['label'].toString();
            final groupNum = int.tryParse(label.replaceAll(RegExp(r'[^0-9]'), '')) ?? 1;
            final gIdx = (groupNum - 1).clamp(0, _itemGroupMethods.length - 1);
            return Container(
              margin: const EdgeInsets.only(bottom: 6),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLowest,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(label, style: const TextStyle(color: AppColors.onSurface, fontWeight: FontWeight.bold, fontSize: 12)),
                        Text(_formatCurrency(p['jumlah']), style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 12.5)),
                      ],
                    ),
                  ),
                  _buildMethodSelector(
                    selected: _itemGroupMethods[gIdx],
                    onChanged: (val) => setState(() => _itemGroupMethods[gIdx] = val),
                  ),
                ],
              ),
            );
          }),
        ],
      ],
    );
  }

  void _setItemGroupCount(int count) {
    setState(() {
      _itemGroupCount = count;
      while (_itemGroupMethods.length < count) {
        _itemGroupMethods.add('tunai');
      }
      if (_itemGroupMethods.length > count) {
        _itemGroupMethods = _itemGroupMethods.sublist(0, count);
      }
      for (final u in _unitItems) {
        if ((u['groupIdx'] as int) >= count) {
          u['groupIdx'] = count - 1;
        }
      }
    });
  }

  // ── MODE 3: NOMINAL BEBAS ──
  Widget _buildCustomMode() {
    final totalInput = _customRows.fold<int>(
      0,
      (sum, r) => sum + (int.tryParse(r.amountCtrl.text.replaceAll(RegExp(r'[^0-9]'), '')) ?? 0),
    );
    final diff = _totalHarga - totalInput;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLow,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: diff == 0 ? AppColors.green.withValues(alpha: 0.5) : AppColors.primary.withValues(alpha: 0.4),
            ),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Terbagi: ${_formatCurrency(totalInput)}',
                style: const TextStyle(color: AppColors.onSurface, fontSize: 12, fontWeight: FontWeight.bold),
              ),
              Text(
                diff == 0
                    ? 'Pas (Sesuai Tagihan)'
                    : (diff > 0 ? 'Kurang ${_formatCurrency(diff)}' : 'Lebih ${_formatCurrency(-diff)}'),
                style: TextStyle(
                  color: diff == 0 ? AppColors.green : (diff > 0 ? AppColors.primary : AppColors.error),
                  fontSize: 11.5,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),

        ...List.generate(_customRows.length, (i) {
          final row = _customRows[i];
          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLow,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: AppColors.outlineVariant.withValues(alpha: 0.25)),
            ),
            child: Column(
              children: [
                Row(
                  children: [
                    Expanded(
                      flex: 2,
                      child: TextField(
                        controller: row.labelCtrl,
                        style: const TextStyle(color: AppColors.onSurface, fontSize: 12.5),
                        decoration: const InputDecoration(
                          labelText: 'Nama / Bagian',
                          isDense: true,
                          contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 9),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      flex: 2,
                      child: TextField(
                        controller: row.amountCtrl,
                        keyboardType: TextInputType.number,
                        inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                        onChanged: (_) => setState(() {}),
                        style: const TextStyle(color: AppColors.primary, fontSize: 12.5, fontWeight: FontWeight.bold),
                        decoration: const InputDecoration(
                          labelText: 'Nominal (Rp)',
                          prefixText: 'Rp ',
                          isDense: true,
                          contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 9),
                        ),
                      ),
                    ),
                    if (_customRows.length > 2)
                      IconButton(
                        icon: const Icon(Icons.delete_outline, color: AppColors.error, size: 18),
                        onPressed: () {
                          setState(() {
                            row.dispose();
                            _customRows.removeAt(i);
                          });
                        },
                      ),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    _buildMethodSelector(
                      selected: row.metode,
                      onChanged: (val) => setState(() => row.metode = val),
                    ),
                    if (diff != 0)
                      TextButton(
                        style: TextButton.styleFrom(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          minimumSize: Size.zero,
                        ),
                        onPressed: () {
                          final current = int.tryParse(row.amountCtrl.text.replaceAll(RegExp(r'[^0-9]'), '')) ?? 0;
                          final updated = (current + diff).clamp(0, _totalHarga);
                          setState(() => row.amountCtrl.text = updated.toString());
                        },
                        child: const Text('Isi Sisa', style: TextStyle(color: AppColors.primary, fontSize: 11)),
                      ),
                  ],
                ),
              ],
            ),
          );
        }),

        OutlinedButton.icon(
          style: OutlinedButton.styleFrom(
            foregroundColor: AppColors.primary,
            side: BorderSide(color: AppColors.primary.withValues(alpha: 0.4)),
          ),
          onPressed: () {
            setState(() {
              _customRows.add(
                _CustomSplitRow(
                  label: 'Orang ${_customRows.length + 1}',
                  amount: diff > 0 ? diff : 0,
                  metode: 'tunai',
                ),
              );
            });
          },
          icon: const Icon(Icons.add, size: 16),
          label: const Text('Tambah Bagian', style: TextStyle(fontSize: 12)),
        ),
      ],
    );
  }

  Widget _buildSetupFooter() {
    if (_mode == 'bayar_item') {
      final est = _calcPartialEstimate();
      final payAll = _isPayingAllRemaining();
      return Row(
        mainAxisAlignment: MainAxisAlignment.end,
        children: [
          TextButton(
            onPressed: _submitting ? null : () => Navigator.pop(context),
            child: const Text('Batal', style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 12)),
          ),
          const SizedBox(width: 8),
          ElevatedButton.icon(
            style: ElevatedButton.styleFrom(
              backgroundColor: payAll ? AppColors.green : AppColors.primary,
              foregroundColor: payAll ? Colors.white : AppColors.onPrimary,
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 11),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: (_submitting || est <= 0) ? null : _submitPartialItemPayment,
            icon: _submitting
                ? const SizedBox(
                    width: 14,
                    height: 14,
                    child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.onPrimary),
                  )
                : const Icon(Icons.payments_outlined, size: 16),
            label: Text(
              _submitting
                  ? 'Memproses...'
                  : (payAll
                      ? 'Lunasi Semua (${_formatCurrency(est)})'
                      : (est > 0 ? 'Bayar Item (${_formatCurrency(est)})' : 'Pilih Menu Dulu')),
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
            ),
          ),
        ],
      );
    }

    return Row(
      mainAxisAlignment: MainAxisAlignment.end,
      children: [
        TextButton(
          onPressed: _submitting ? null : () => Navigator.pop(context),
          child: const Text('Batal', style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 12)),
        ),
        const SizedBox(width: 8),
        ElevatedButton.icon(
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.primary,
            foregroundColor: AppColors.onPrimary,
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 11),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
          onPressed: _submitting ? null : _submitCreateSplit,
          icon: _submitting
              ? const SizedBox(
                  width: 14,
                  height: 14,
                  child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.onPrimary),
                )
              : const Icon(Icons.call_split, size: 16),
          label: Text(
            _submitting ? 'Menyimpan...' : 'Simpan Split Bill',
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
          ),
        ),
      ],
    );
  }

  // ─── SMALL HELPERS ─────────────────────────────────────────────────────────

  Widget _stepperBtn(IconData icon, VoidCallback? onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        width: 30,
        height: 30,
        decoration: BoxDecoration(
          color: onTap != null ? AppColors.surfaceContainerHigh : AppColors.surfaceContainerLowest,
          borderRadius: BorderRadius.circular(8),
        ),
        child: Icon(
          icon,
          size: 16,
          color: onTap != null ? AppColors.onSurface : AppColors.outline,
        ),
      ),
    );
  }

  Widget _buildMethodSelector({
    required String selected,
    required ValueChanged<String> onChanged,
  }) {
    const methods = [
      ('tunai', 'Tunai'),
      ('qris', 'QRIS'),
      ('transfer', 'Transfer'),
    ];
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: methods.map((m) {
        final active = selected == m.$1;
        return Padding(
          padding: const EdgeInsets.only(right: 4),
          child: InkWell(
            onTap: () => onChanged(m.$1),
            borderRadius: BorderRadius.circular(6),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: active ? AppColors.secondary.withValues(alpha: 0.22) : AppColors.surfaceContainerHigh,
                borderRadius: BorderRadius.circular(6),
                border: Border.all(
                  color: active ? AppColors.secondary : Colors.transparent,
                ),
              ),
              child: Text(
                m.$2,
                style: TextStyle(
                  color: active ? AppColors.secondary : AppColors.onSurfaceVariant,
                  fontSize: 10.5,
                  fontWeight: active ? FontWeight.bold : FontWeight.normal,
                ),
              ),
            ),
          ),
        );
      }).toList(),
    );
  }
}

class _CustomSplitRow {
  final TextEditingController labelCtrl;
  final TextEditingController amountCtrl;
  String metode;

  _CustomSplitRow({
    required String label,
    required int amount,
    required this.metode,
  })  : labelCtrl = TextEditingController(text: label),
        amountCtrl = TextEditingController(text: amount.toString());

  void dispose() {
    labelCtrl.dispose();
    amountCtrl.dispose();
  }
}
