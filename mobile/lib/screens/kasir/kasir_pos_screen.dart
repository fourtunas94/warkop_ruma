import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../services/api_service.dart';
import '../../widgets/app_widgets.dart' hide AppColors;
import 'kasir_colors.dart';
import '../auth/login_screen.dart';
import '../admin/menu_screen.dart';
import '../admin/meja_screen.dart';
import '../admin/riwayat_screen.dart';
import '../admin/pengaturan_screen.dart';
import 'split_bill_dialog.dart';
import 'struk_dialog.dart';

class KasirPosScreen extends StatefulWidget {
  const KasirPosScreen({super.key, this.cashierMode = true});

  final bool cashierMode;

  @override
  State<KasirPosScreen> createState() => _KasirPosScreenState();
}

class _KasirPosScreenState extends State<KasirPosScreen> {
  int _selectedNavIndex = 0;

  List<dynamic> _orders = [];
  int? _selectedOrderId;
  Map<String, dynamic> _stats = {};
  Map<String, dynamic> _counts = {
    'baru_masuk': 0,
    'sedang_dibuat': 0,
    'siap_antar': 0,
    'belum_bayar': 0,
    'semua': 0,
  };
  bool _loading = true;
  bool _isFetching = false;
  String _lastDataFingerprint = '';
  String _userName = 'Admin';
  String _currentTime = '';
  String _currentShift = 'Shift Pagi';
  Timer? _pollingTimer;
  Timer? _clockTimer;

  String _filterStatus = 'semua';
  String _filterChannel = 'semua';
  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    if (!widget.cashierMode) _selectedNavIndex = 1;
    _loadUserName();
    _updateClock();
    if (widget.cashierMode) _fetchDashboard();
    _startTimers();
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    _clockTimer?.cancel();
    _searchController.dispose();
    super.dispose();
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

  void _startTimers() {
    _clockTimer = Timer.periodic(const Duration(seconds: 30), (_) {
      if (mounted) _updateClock();
    });
    if (widget.cashierMode) {
      _pollingTimer = Timer.periodic(const Duration(seconds: 12), (_) {
        if (mounted && _selectedNavIndex == 0 && !_isFetching) {
          _fetchDashboard(silent: true);
        }
      });
    }
  }

  void _updateClock() {
    final now = DateTime.now();
    final hour = now.hour;
    String shift = 'Shift Malam';
    if (hour >= 6 && hour < 14) {
      shift = 'Shift Pagi';
    } else if (hour >= 14 && hour < 19) {
      shift = 'Shift Siang';
    }
    final newTime = '${DateFormat('HH:mm').format(now)} WIB';
    if (newTime == _currentTime && shift == _currentShift) return;
    setState(() {
      _currentTime = newTime;
      _currentShift = shift;
    });
  }

  Future<void> _loadUserName() async {
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      setState(() => _userName = prefs.getString('user_name') ?? _roleLabel);
    }
  }

  String get _roleLabel => widget.cashierMode ? 'Kasir' : 'Admin';

  Future<void> _fetchDashboard({bool silent = false}) async {
    if (_isFetching) return;
    _isFetching = true;
    if (!silent) setState(() => _loading = true);
    try {
      final result = await ApiService.kasirPosDashboard();
      if (result['success'] == true && mounted) {
        final rawList = (result['pesanans'] is List ? result['pesanans'] : null) ??
            (result['pesanan_terbaru'] is List ? result['pesanan_terbaru'] : null) ??
            [];
        final list = rawList.where((o) {
          final sp = (o['status_pesanan'] ?? 'menunggu').toString().toLowerCase();
          final st = (o['status'] ?? '').toString().toLowerCase();
          return !(sp == 'selesai' && st == 'selesai');
        }).toList();

        final fingerprint = jsonEncode([
          result['total_pesanan'],
          result['pendapatan'],
          result['counts'],
          list.map((o) {
            final splitsLen = o['splits'] is List ? (o['splits'] as List).length : 0;
            final itemsLen = o['items'] is List ? (o['items'] as List).length : 0;
            return [
              o['id'],
              o['status'],
              o['status_pesanan'],
              o['total_harga'],
              o['total_terbayar'],
              splitsLen,
              itemsLen,
            ];
          }).toList(),
        ]);

        if (silent && fingerprint == _lastDataFingerprint && !_loading) {
          return;
        }
        _lastDataFingerprint = fingerprint;

        setState(() {
          _orders = list;
          _stats = {
            'total_pesanan': _toInt(result['total_pesanan']),
            'pendapatan': result['pendapatan'] ?? 0,
            'total_menu': _toInt(result['total_menu']),
            'total_meja': _toInt(result['total_meja']),
          };
          if (result['counts'] != null && result['counts'] is Map) {
            final rawCounts = Map<String, dynamic>.from(result['counts'] as Map);
            _counts = {
              'baru_masuk': _toInt(rawCounts['baru_masuk']),
              'sedang_dibuat': _toInt(rawCounts['sedang_dibuat']),
              'siap_antar': _toInt(rawCounts['siap_antar']),
              'belum_bayar': _toInt(rawCounts['belum_bayar']),
              'semua': _toInt(rawCounts['semua']),
            };
          } else {
            _calculateLocalCounts(list);
          }
          _loading = false;

          if (_orders.isNotEmpty) {
            final exists = _orders.any((o) => _toInt(o['id']) == _selectedOrderId);
            if (!exists) {
              _selectedOrderId = _toInt(_orders.first['id']);
            }
          } else {
            _selectedOrderId = null;
          }
        });
      } else if (mounted && _loading) {
        setState(() => _loading = false);
      }
    } catch (e) {
      if (mounted && _loading) setState(() => _loading = false);
    } finally {
      _isFetching = false;
    }
  }

  void _calculateLocalCounts(List list) {
    int baru = 0;
    int proses = 0;
    int siap = 0;
    int belumBayar = 0;
    for (var o in list) {
      final sp = (o['status_pesanan'] ?? 'menunggu').toString().toLowerCase();
      final st = (o['status'] ?? '').toString().toLowerCase();
      if (sp == 'menunggu') {
        baru++;
      } else if (sp == 'proses') {
        proses++;
      } else if (sp == 'siap') {
        siap++;
      }
      if (st != 'selesai') {
        belumBayar++;
      }
    }
    _counts = {
      'baru_masuk': baru,
      'sedang_dibuat': proses,
      'siap_antar': siap,
      'belum_bayar': belumBayar,
      'semua': list.length,
    };
  }

  Future<void> _konfirmasiBayar(int orderId, {String? metodePembayaran}) async {
    final res = await ApiService.telahBayar(orderId, metodePembayaran: metodePembayaran);
    if (!mounted) return;
    _showSnack(
      res['success'] == true
          ? (res['message']?.toString() ?? 'Pembayaran berhasil dikonfirmasi!')
          : (res['message']?.toString() ?? 'Gagal konfirmasi.'),
      res['success'] == true,
    );
    _fetchDashboard(silent: true);
  }

  Future<void> _showValidasiBayarDialog(Map<String, dynamic> order) async {
    final id = _toInt(order['id']);
    final meja = order['nomor_meja']?.toString() ?? '-';
    final nama = order['nama_pemesan']?.toString() ?? 'Tamu';
    final total = order['total_harga'] ?? 0;
    String selectedMetode = (order['metode_pembayaran'] ?? 'tunai').toString().toLowerCase();
    if (!['tunai', 'qris'].contains(selectedMetode)) {
      selectedMetode = 'tunai';
    }

    await showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) => AlertDialog(
          backgroundColor: AppColors.surfaceContainer,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: BorderSide(color: AppColors.primary.withValues(alpha: 0.35)),
          ),
          title: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Validasi Pembayaran Kasir',
                style: TextStyle(
                  color: AppColors.onSurface,
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  fontFamily: 'Epilogue',
                ),
              ),
              const SizedBox(height: 2),
              Text(
                'Meja $meja — $nama',
                style: const TextStyle(
                  color: AppColors.onSurfaceVariant,
                  fontSize: 11.5,
                  fontFamily: 'SpaceGrotesk',
                ),
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.surfaceContainerLow,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'TOTAL TAGIHAN',
                      style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 10),
                    ),
                    Text(
                      _formatCurrency(total),
                      style: const TextStyle(
                        color: AppColors.primary,
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        fontFamily: 'Epilogue',
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              const Text(
                'Pilih Metode Pembayaran Diterima:',
                style: TextStyle(color: AppColors.onSurface, fontSize: 12, fontWeight: FontWeight.w600),
              ),
              const SizedBox(height: 8),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  ('tunai', 'Tunai', Icons.payments_outlined),
                  ('qris', 'QRIS', Icons.qr_code_2),
                ].map((m) {
                  final active = selectedMetode == m.$1;
                  return ChoiceChip(
                    avatar: Icon(
                      m.$3,
                      size: 16,
                      color: active ? AppColors.onPrimary : AppColors.onSurfaceVariant,
                    ),
                    label: Text(
                      m.$2,
                      style: TextStyle(
                        color: active ? AppColors.onPrimary : AppColors.onSurface,
                        fontSize: 12,
                      ),
                    ),
                    selected: active,
                    selectedColor: AppColors.primary,
                    backgroundColor: AppColors.surfaceContainerHigh,
                    onSelected: (_) => setDlgState(() => selectedMetode = m.$1),
                  );
                }).toList(),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.pop(ctx);
                _openSplitBillDialog(order);
              },
              child: const Text('Split Bill?', style: TextStyle(color: AppColors.tertiary, fontSize: 12)),
            ),
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Batal', style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 12)),
            ),
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: AppColors.onPrimary,
              ),
              onPressed: () {
                Navigator.pop(ctx);
                _konfirmasiBayar(id, metodePembayaran: selectedMetode);
              },
              icon: const Icon(Icons.verified, size: 16),
              label: const Text('Tandai Lunas', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _prosesPesanan(Map<String, dynamic> order) async {
    final orderId = _toInt(order['id']);
    final res = await ApiService.updateStatusPesanan(orderId, 'proses');
    if (!mounted) return;

    final success = res['success'] == true;
    _showSnack(
      success
          ? 'Pesanan dikirim ke dapur & tiket dapur dicetak!'
          : (res['message']?.toString() ?? 'Gagal'),
      success,
    );

    if (success) {
      await _fetchDashboard(silent: true);
      if (!mounted) return;
      Map<String, dynamic> updatedOrder = {
        ...order,
        'status_pesanan': 'proses',
      };
      for (final o in _orders) {
        if (o is Map && _toInt(o['id']) == orderId) {
          updatedOrder = Map<String, dynamic>.from(o);
          break;
        }
      }
      StrukDialog.printKitchenTicket(updatedOrder);
    }
  }

  void _cetakTiketDapur(Map<String, dynamic> order) {
    StrukDialog.printKitchenTicket(order);
    _showSnack('Tiket dapur dikirim ke printer!', true);
  }

  Future<void> _siapPesanan(int orderId) async {
    final res = await ApiService.siapPesanan(orderId);
    if (!mounted) return;
    _showSnack(
      res['success'] == true
          ? 'Pesanan selesai & siap disajikan!'
          : (res['message']?.toString() ?? 'Gagal'),
      res['success'] == true,
    );
    _fetchDashboard(silent: true);
  }

  Future<void> _selesaiPesanan(int orderId) async {
    final res = await ApiService.updateStatusPesanan(orderId, 'selesai');
    if (!mounted) return;
    _showSnack(
      res['success'] == true
          ? 'Pesanan telah selesai diantar ke meja.'
          : (res['message']?.toString() ?? 'Gagal'),
      res['success'] == true,
    );
    _fetchDashboard(silent: true);
  }

  void _cetakStruk(Map<String, dynamic> order) {
    StrukDialog.showOrderReceipt(context, order: order);
  }

  void _openSplitBillDialog(Map<String, dynamic> order) {
    SplitBillDialog.show(
      context,
      order: order,
      onUpdated: () => _fetchDashboard(silent: true),
    );
  }

  List _getMergeableOrders(Map<String, dynamic> currentOrder) {
    final currentId = _toInt(currentOrder['id']);
    final currentMejaId = '${currentOrder['meja_id']}';
    final candidates = _orders.where((o) {
      if (_toInt(o['id']) == currentId) return false;
      final st = (o['status'] ?? 'pending').toString().toLowerCase();
      if (st == 'selesai') return false;
      final sp = o['splits'] is List ? (o['splits'] as List) : const [];
      if (sp.any((s) => s['status'] == 'lunas')) return false;
      return true;
    }).map((o) => Map<String, dynamic>.from(o as Map)).toList();

    candidates.sort((a, b) {
      final aSame = '${a['meja_id']}' == currentMejaId ? 0 : 1;
      final bSame = '${b['meja_id']}' == currentMejaId ? 0 : 1;
      return aSame.compareTo(bSame);
    });
    return candidates;
  }

  Future<void> _showMergeBillDialog(Map<String, dynamic> primaryOrder) async {
    final candidates = _getMergeableOrders(primaryOrder);
    if (candidates.isEmpty) {
      _showSnack('Tidak ada pesanan lain yang belum lunas untuk digabungkan.', false);
      return;
    }

    final primaryId = _toInt(primaryOrder['id']);
    final primaryMeja = primaryOrder['nomor_meja']?.toString() ?? '-';
    final primaryMejaId = '${primaryOrder['meja_id']}';
    final primaryNama = primaryOrder['nama_pemesan']?.toString() ?? 'Tamu';
    final primaryTotal = _toDouble(primaryOrder['total_harga']);

    final Set<int> selectedIds = candidates
        .where((c) => '${c['meja_id']}' == primaryMejaId)
        .map((c) => _toInt(c['id']))
        .toSet();
    bool submitting = false;

    await showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlgState) {
          double combinedTotal = primaryTotal;
          for (final c in candidates) {
            if (selectedIds.contains(_toInt(c['id']))) {
              combinedTotal += _toDouble(c['total_harga']);
            }
          }

          return AlertDialog(
            backgroundColor: AppColors.surfaceContainer,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: BorderSide(color: AppColors.primary.withValues(alpha: 0.35)),
            ),
            title: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(7),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.16),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.call_merge, color: AppColors.primary, size: 18),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Gabung Pesanan (Merge Bill)',
                        style: TextStyle(
                          color: AppColors.onSurface,
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          fontFamily: 'Epilogue',
                        ),
                      ),
                      Text(
                        'Tagihan Utama: Meja $primaryMeja — $primaryNama',
                        style: const TextStyle(
                          color: AppColors.onSurfaceVariant,
                          fontSize: 10.5,
                          fontFamily: 'SpaceGrotesk',
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            content: SizedBox(
              width: 440,
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Pilih pesanan yang akan digabung ke tagihan ini:',
                      style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 11.5),
                    ),
                    const SizedBox(height: 10),
                    ...candidates.map((c) {
                      final cid = _toInt(c['id']);
                      final checked = selectedIds.contains(cid);
                      final sameTable = '${c['meja_id']}' == primaryMejaId;
                      final cItems = c['items'] is List ? (c['items'] as List) : const [];
                      final itemPreview = cItems
                          .take(2)
                          .map((it) => '${it['qty']}x ${it['nama_menu']}')
                          .join(', ');

                      return Container(
                        margin: const EdgeInsets.only(bottom: 8),
                        decoration: BoxDecoration(
                          color: AppColors.surfaceContainerLow,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: checked
                                ? AppColors.primary
                                : AppColors.outlineVariant.withValues(alpha: 0.3),
                          ),
                        ),
                        child: CheckboxListTile(
                          value: checked,
                          activeColor: AppColors.primary,
                          checkColor: AppColors.onPrimary,
                          contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
                          onChanged: submitting
                              ? null
                              : (val) {
                                  setDlgState(() {
                                    if (val == true) {
                                      selectedIds.add(cid);
                                    } else {
                                      selectedIds.remove(cid);
                                    }
                                  });
                                },
                          title: Row(
                            children: [
                              Expanded(
                                child: Text(
                                  'Meja ${c['nomor_meja'] ?? '-'} — ${c['nama_pemesan'] ?? 'Tamu'}',
                                  style: const TextStyle(
                                    color: AppColors.onSurface,
                                    fontSize: 12.5,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                              if (sameTable)
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: AppColors.primary.withValues(alpha: 0.2),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: const Text(
                                    'Meja Sama',
                                    style: TextStyle(
                                      color: AppColors.primary,
                                      fontSize: 9.5,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                            ],
                          ),
                          subtitle: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              if (itemPreview.isNotEmpty)
                                Text(
                                  itemPreview + (cItems.length > 2 ? ' (+${cItems.length - 2} lagi)' : ''),
                                  style: const TextStyle(color: AppColors.onSurfaceVariant, fontSize: 10.5),
                                ),
                              Text(
                                '${c['kode_pesanan']} • ${_formatCurrency(c['total_harga'] ?? 0)}',
                                style: const TextStyle(
                                  color: AppColors.primary,
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    }),
                    const SizedBox(height: 8),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: AppColors.surfaceContainerLowest,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'Total Gabungan (${selectedIds.length + 1} pesanan)',
                            style: const TextStyle(color: AppColors.onSurfaceVariant, fontSize: 11.5),
                          ),
                          Text(
                            _formatCurrency(combinedTotal),
                            style: const TextStyle(
                              color: AppColors.primary,
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                              fontFamily: 'Epilogue',
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            actions: [
              TextButton(
                onPressed: submitting ? null : () => Navigator.pop(ctx),
                child: const Text('Batal', style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 12)),
              ),
              ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: AppColors.onPrimary,
                ),
                onPressed: (submitting || selectedIds.isEmpty)
                    ? null
                    : () async {
                        setDlgState(() => submitting = true);
                        final res = await ApiService.mergeBill(primaryId, selectedIds.toList());
                        if (!mounted) return;
                        if (ctx.mounted) Navigator.pop(ctx);
                        _showSnack(
                          res['message']?.toString() ??
                              (res['success'] == true
                                  ? 'Pesanan berhasil digabungkan!'
                                  : 'Gagal menggabungkan pesanan.'),
                          res['success'] == true,
                        );
                        if (res['success'] == true) {
                          _fetchDashboard(silent: true);
                        }
                      },
                icon: submitting
                    ? const SizedBox(
                        width: 14,
                        height: 14,
                        child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.onPrimary),
                      )
                    : const Icon(Icons.call_merge, size: 16),
                label: Text(
                  submitting ? 'Menggabungkan...' : 'Gabungkan (${selectedIds.length})',
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  void _showSnack(String msg, bool success) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(
              success ? Icons.check_circle : Icons.error_outline,
              color: success ? AppColors.primary : AppColors.error,
              size: 20,
            ),
            const SizedBox(width: 10),
            Expanded(child: Text(msg, style: const TextStyle(color: AppColors.onSurface))),
          ],
        ),
        backgroundColor: AppColors.surfaceContainerHigh,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        duration: const Duration(seconds: 3),
      ),
    );
  }

  Future<void> _logout() async {
    await ApiService.logout();
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
    if (!mounted) return;
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => const LoginScreen()),
    );
  }

  String _formatCurrency(dynamic value) {
    final amount = _toDouble(value);
    return NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0).format(amount);
  }

  Map<String, dynamic>? _findSelectedOrder() {
    if (_orders.isEmpty) return null;
    for (final o in _orders) {
      if (o is Map && _toInt(o['id']) == _selectedOrderId) {
        return Map<String, dynamic>.from(o);
      }
    }
    return _orders.first is Map ? Map<String, dynamic>.from(_orders.first as Map) : null;
  }

  List _getFilteredOrders() {
    return _orders.where((o) {
      final sp = (o['status_pesanan'] ?? 'menunggu').toString().toLowerCase();
      final st = (o['status'] ?? '').toString().toLowerCase();
      final ch = (o['channel'] ?? 'dine_in').toString().toLowerCase();

      if (_filterStatus == 'baru') {
        if (sp != 'menunggu') return false;
      } else if (_filterStatus == 'proses') {
        if (sp != 'proses') return false;
      } else if (_filterStatus == 'siap') {
        if (sp != 'siap') return false;
      } else if (_filterStatus == 'belum_bayar') {
        if (st == 'selesai') return false;
      }

      if (_filterChannel == 'dine_in' && ch != 'dine_in') return false;
      if (_filterChannel == 'takeaway' && ch != 'takeaway') return false;

      if (_searchQuery.trim().isNotEmpty) {
        final query = _searchQuery.toLowerCase();
        final nama = (o['nama_pemesan'] ?? '').toString().toLowerCase();
        final meja = (o['nomor_meja'] ?? '').toString().toLowerCase();
        final kode = (o['kode_pesanan'] ?? '').toString().toLowerCase();
        final antrian = (o['nomor_antrian'] ?? '').toString().toLowerCase();
        if (!nama.contains(query) &&
            !meja.contains(query) &&
            !kode.contains(query) &&
            !antrian.contains(query)) {
          return false;
        }
      }

      return true;
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Theme(
      data: AppColors.lightTheme,
      child: Scaffold(
        backgroundColor: AppColors.background,
        drawer: _buildDrawer(),
        body: SafeArea(
          child: Column(
            children: [
              _buildTopHeader(),
              Expanded(
                child: _selectedNavIndex == 0
                    ? _buildKdsWorkspace()
                    : _buildSubScreen(_selectedNavIndex),
              ),
              _buildStitchFooter(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTopHeader() {
    final screenWidth = MediaQuery.of(context).size.width;

    return Container(
      height: 60,
      padding: const EdgeInsets.symmetric(horizontal: 12),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest.withOpacity(0.95),
        border: Border(
          bottom: BorderSide(color: AppColors.outlineVariant.withOpacity(0.2), width: 1),
        ),
      ),
      child: Row(
        children: [
          Builder(
            builder: (ctx) => IconButton(
              icon: const Icon(Icons.menu, color: AppColors.primary, size: 22),
              tooltip: 'Menu Navigasi',
              padding: EdgeInsets.zero,
              constraints: const BoxConstraints(minWidth: 36, minHeight: 36),
              onPressed: () => Scaffold.of(ctx).openDrawer(),
            ),
          ),
          const SizedBox(width: 6),
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(9),
              child: Image.asset('assets/images/logo.jpeg', fit: BoxFit.cover),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                FittedBox(
                  fit: BoxFit.scaleDown,
                  alignment: Alignment.centerLeft,
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Text(
                        'Warkop Ruma',
                        style: TextStyle(
                          color: AppColors.primary,
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                          fontFamily: 'Epilogue',
                          letterSpacing: -0.2,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                        decoration: BoxDecoration(
                          color: AppColors.secondaryContainer,
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Text(
                          'SIDIKALANG',
                          style: TextStyle(
                            color: AppColors.primary,
                            fontSize: 8.5,
                            fontWeight: FontWeight.w700,
                            letterSpacing: 0.8,
                            fontFamily: 'SpaceGrotesk',
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const Text(
                  'POS Kasir & Barista Station',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: AppColors.onSurfaceVariant,
                    fontSize: 10.5,
                    fontFamily: 'SpaceGrotesk',
                  ),
                ),
              ],
            ),
          ),
          if (screenWidth >= 760) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLow,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.outlineVariant.withOpacity(0.2)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 6,
                    height: 6,
                    decoration: const BoxDecoration(shape: BoxShape.circle, color: AppColors.primary),
                  ),
                  const SizedBox(width: 6),
                  Text(
                    '$_roleLabel: $_userName | $_currentShift',
                    style: const TextStyle(
                      color: AppColors.onSurface,
                      fontSize: 11,
                      fontFamily: 'SpaceGrotesk',
                    ),
                  ),
                  const SizedBox(width: 6),
                  const Icon(Icons.schedule, size: 12, color: AppColors.primary),
                  const SizedBox(width: 3),
                  Text(
                    _currentTime,
                    style: const TextStyle(
                      color: AppColors.primary,
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      fontFamily: 'SpaceGrotesk',
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
          ],
          if (screenWidth >= 620) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: AppColors.surfaceContainerLow,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.outlineVariant.withOpacity(0.2)),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.print, size: 12, color: AppColors.secondary),
                  SizedBox(width: 4),
                  Text('Mesin: ', style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 10)),
                  Text(
                    'AKTIF',
                    style: TextStyle(
                      color: AppColors.primary,
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                      fontFamily: 'SpaceGrotesk',
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 6),
          ],
          if (widget.cashierMode)
            IconButton(
              icon: const Icon(Icons.sync, color: AppColors.onSurfaceVariant, size: 20),
              tooltip: 'Segarkan Antrean',
              padding: EdgeInsets.zero,
              constraints: const BoxConstraints(minWidth: 36, minHeight: 36),
              onPressed: () => _fetchDashboard(),
            ),
          PopupMenuButton<String>(
            color: AppColors.surfaceContainerHigh,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            icon: Container(
              width: 30,
              height: 30,
              decoration: const BoxDecoration(shape: BoxShape.circle, color: AppColors.primary),
              child: const Icon(Icons.person, color: AppColors.onPrimary, size: 17),
            ),
            onSelected: (val) {
              if (val == 'logout') {
                ConfirmDialog.show(
                  context,
                  title: 'Keluar',
                  message: 'Yakin ingin keluar dari akun $_roleLabel?',
                  confirmLabel: 'Keluar',
                  confirmColor: AppColors.error,
                  onConfirm: _logout,
                );
              } else if (val == 'refresh') {
                _fetchDashboard();
              }
            },
            itemBuilder: (ctx) => [
              PopupMenuItem(
                enabled: false,
                child: Text(
                  'Halo, $_userName ($_roleLabel)',
                  style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold),
                ),
              ),
              const PopupMenuDivider(),
              if (widget.cashierMode)
                const PopupMenuItem(
                  value: 'refresh',
                  child: Row(
                    children: [
                      Icon(Icons.refresh, size: 18, color: AppColors.onSurface),
                      SizedBox(width: 8),
                      Text('Segarkan Antrean', style: TextStyle(color: AppColors.onSurface)),
                    ],
                  ),
                ),
              const PopupMenuItem(
                value: 'logout',
                child: Row(
                  children: [
                    Icon(Icons.logout, size: 18, color: AppColors.error),
                    SizedBox(width: 8),
                    Text('Keluar Akun', style: TextStyle(color: AppColors.error)),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildKdsWorkspace() {
    final selectedOrder = _findSelectedOrder();
    return LoadingOverlay(
      isLoading: _loading,
      child: Column(
        children: [
          _buildSubHeaderPulse(),
          Expanded(
            child: LayoutBuilder(
              builder: (context, constraints) {
                final isWide = constraints.maxWidth >= 960;
                if (isWide) {
                  return Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(flex: 68, child: _buildQueueFeed(isWide: true)),
                      Container(width: 1, color: AppColors.outlineVariant.withOpacity(0.2)),
                      Expanded(flex: 32, child: _buildTicketDetailPanel()),
                    ],
                  );
                } else {
                  return Stack(
                    children: [
                      _buildQueueFeed(isWide: false),
                      if (selectedOrder != null)
                        Positioned(
                          left: 12,
                          right: 12,
                          bottom: 10,
                          child: _buildMobileTicketBar(),
                        ),
                    ],
                  );
                }
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSubHeaderPulse() {
    final activeOrdersCount =
        _orders.where((o) => (o['status_pesanan'] ?? 'menunggu') != 'selesai').length;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest.withOpacity(0.6),
        border: Border(bottom: BorderSide(color: AppColors.outlineVariant.withOpacity(0.15), width: 1)),
      ),
      child: Wrap(
        alignment: WrapAlignment.spaceBetween,
        crossAxisAlignment: WrapCrossAlignment.center,
        spacing: 12,
        runSpacing: 8,
        children: [
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.local_fire_department, size: 15, color: AppColors.tertiary),
              const SizedBox(width: 4),
              RichText(
                text: TextSpan(
                  style: const TextStyle(
                    fontSize: 11.5,
                    fontFamily: 'SpaceGrotesk',
                    color: AppColors.onSurfaceVariant,
                  ),
                  children: [
                    const TextSpan(text: 'Beban: '),
                    TextSpan(
                      text: activeOrdersCount > 5
                          ? 'Tinggi '
                          : (activeOrdersCount > 2 ? 'Sedang ' : 'Normal '),
                      style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold),
                    ),
                    TextSpan(text: '($activeOrdersCount Aktif)'),
                  ],
                ),
              ),
              if (MediaQuery.of(context).size.width >= 700 && _stats['pendapatan'] != null) ...[
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 6),
                  child: Text('•', style: TextStyle(color: AppColors.outlineVariant)),
                ),
                const Icon(Icons.monetization_on_outlined, size: 14, color: AppColors.primary),
                const SizedBox(width: 4),
                Text(
                  'Omzet: ${_formatCurrency(_stats['pendapatan'])}',
                  style: const TextStyle(
                    fontSize: 11.5,
                    fontFamily: 'SpaceGrotesk',
                    color: AppColors.primary,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ],
          ),
          SizedBox(
            width: 210,
            height: 34,
            child: TextField(
              controller: _searchController,
              style: const TextStyle(color: AppColors.onSurface, fontSize: 11),
              onChanged: (val) => setState(() => _searchQuery = val),
              decoration: InputDecoration(
                hintText: 'Cari Meja, Struk, Tamu...',
                hintStyle: const TextStyle(color: AppColors.onSurfaceVariant, fontSize: 10.5),
                prefixIcon: const Icon(Icons.search, size: 15, color: AppColors.onSurfaceVariant),
                filled: true,
                fillColor: AppColors.surfaceContainer,
                contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 8),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(18),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQueueFeed({required bool isWide}) {
    final filtered = _getFilteredOrders();

    return RefreshIndicator(
      color: AppColors.primary,
      backgroundColor: AppColors.surfaceContainer,
      onRefresh: () => _fetchDashboard(),
      child: ListView(
        padding: const EdgeInsets.all(12),
        children: [
          _buildFilterAndChannelBar(),
          const SizedBox(height: 12),
          if (filtered.isEmpty && !_loading)
            _buildEmptyOrdersState()
          else
            _buildOrdersGrid(filtered, isWide),
          const SizedBox(height: 16),
          _buildStationHealthCard(),
          const SizedBox(height: 50),
        ],
      ),
    );
  }

  Widget _buildFilterAndChannelBar() {
    return Container(
      padding: const EdgeInsets.all(6),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLow,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.outlineVariant.withOpacity(0.2)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: double.infinity,
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              physics: const BouncingScrollPhysics(),
              child: Row(
                children: [
                  _buildStatusPill(
                    id: 'baru',
                    label: 'Baru Masuk',
                    count: _toInt(_counts['baru_masuk']),
                    icon: Icons.fiber_manual_record,
                    activeColor: AppColors.primary,
                    activeTextColor: AppColors.onPrimary,
                  ),
                  const SizedBox(width: 5),
                  _buildStatusPill(
                    id: 'proses',
                    label: 'Sedang Dibuat',
                    count: _toInt(_counts['sedang_dibuat']),
                    icon: Icons.blender,
                    activeColor: AppColors.tertiary,
                    activeTextColor: AppColors.onPrimaryContainer,
                  ),
                  const SizedBox(width: 5),
                  _buildStatusPill(
                    id: 'siap',
                    label: 'Siap Antar',
                    count: _toInt(_counts['siap_antar']),
                    icon: Icons.check_circle_outline,
                    activeColor: AppColors.secondary,
                    activeTextColor: AppColors.onSecondary,
                  ),
                  const SizedBox(width: 5),
                  _buildStatusPill(
                    id: 'belum_bayar',
                    label: 'Belum Bayar',
                    count: _toInt(_counts['belum_bayar']),
                    icon: Icons.payments_outlined,
                    activeColor: AppColors.red,
                    activeTextColor: Colors.white,
                  ),
                  const SizedBox(width: 5),
                  _buildStatusPill(
                    id: 'semua',
                    label: 'Semua',
                    count: _counts['semua'] != null ? _toInt(_counts['semua']) : _orders.length,
                    icon: Icons.list_alt,
                    activeColor: AppColors.surfaceContainerHighest,
                    activeTextColor: AppColors.onSurface,
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.all(2),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLowest,
              borderRadius: BorderRadius.circular(16),
            ),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _buildChannelButton('semua', 'Semua', Icons.all_inbox),
                  _buildChannelButton('dine_in', 'Dine-In', Icons.table_restaurant),
                  _buildChannelButton('takeaway', 'Bungkus', Icons.shopping_bag_outlined),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusPill({
    required String id,
    required String label,
    required int count,
    required IconData icon,
    required Color activeColor,
    required Color activeTextColor,
  }) {
    final active = _filterStatus == id;

    return InkWell(
      onTap: () => setState(() => _filterStatus = id),
      borderRadius: BorderRadius.circular(18),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
        decoration: BoxDecoration(
          color: active ? activeColor : AppColors.surfaceContainerHigh,
          borderRadius: BorderRadius.circular(18),
          boxShadow: active
              ? [
                  BoxShadow(
                    color: activeColor.withOpacity(0.35),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 11, color: active ? activeTextColor : AppColors.onSurfaceVariant),
            const SizedBox(width: 4),
            Text(
              label,
              style: TextStyle(
                color: active ? activeTextColor : AppColors.onSurface,
                fontSize: 10.5,
                fontWeight: active ? FontWeight.bold : FontWeight.w500,
                fontFamily: 'SpaceGrotesk',
              ),
            ),
            const SizedBox(width: 5),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 4.5, vertical: 1),
              decoration: BoxDecoration(
                color: active
                    ? (activeTextColor == AppColors.onPrimary
                        ? Colors.black.withOpacity(0.2)
                        : Colors.white.withOpacity(0.2))
                    : AppColors.surfaceContainerLowest,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                '$count',
                style: TextStyle(
                  color: active ? activeTextColor : AppColors.onSurfaceVariant,
                  fontSize: 9.5,
                  fontWeight: FontWeight.bold,
                  fontFamily: 'SpaceGrotesk',
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildChannelButton(String channel, String label, IconData icon) {
    final active = _filterChannel == channel;

    return InkWell(
      onTap: () => setState(() => _filterChannel = channel),
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3.5),
        decoration: BoxDecoration(
          color: active ? AppColors.surfaceContainerHigh : Colors.transparent,
          borderRadius: BorderRadius.circular(14),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 11, color: active ? AppColors.primary : AppColors.onSurfaceVariant),
            const SizedBox(width: 3),
            Text(
              label,
              style: TextStyle(
                color: active ? AppColors.primary : AppColors.onSurfaceVariant,
                fontSize: 9.5,
                fontWeight: active ? FontWeight.bold : FontWeight.normal,
                fontFamily: 'SpaceGrotesk',
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildOrdersGrid(List list, bool isWide) {
    return LayoutBuilder(
      builder: (context, box) {
        final useTwoColumns = box.maxWidth >= 640;

        if (useTwoColumns) {
          return GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: list.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              mainAxisExtent: 385,
            ),
            itemBuilder: (context, i) => _buildOrderCard(list[i]),
          );
        } else {
          return ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: list.length,
            separatorBuilder: (_, __) => const SizedBox(height: 12),
            itemBuilder: (context, i) => _buildOrderCard(list[i]),
          );
        }
      },
    );
  }

  Widget _buildOrderCard(dynamic order) {
    final id = _toInt(order['id']);
    final isSelected = id == _selectedOrderId;
    final meja = order['nomor_meja']?.toString() ?? '-';
    final nama = order['nama_pemesan']?.toString() ?? 'Tamu';
    final kode = order['kode_pesanan']?.toString() ?? '#WR-$id';
    final waktu = order['waktu']?.toString() ?? 'Baru saja';
    final totalHarga = order['total_harga'] ?? 0;
    final statusBayar = (order['status'] ?? 'pending').toString().toLowerCase();
    final statusPesanan = (order['status_pesanan'] ?? 'menunggu').toString().toLowerCase();
    final items = order['items'] is List ? (order['items'] as List) : const [];
    final channel = (order['channel'] ?? 'dine_in') == 'dine_in' ? 'Dine-In' : 'Bungkus';

    final itemsSummary = items.take(2).map((it) => it['nama_menu']).join(' + ');

    return GestureDetector(
      onTap: () {
        setState(() => _selectedOrderId = id);
        if (MediaQuery.of(context).size.width < 960) {
          _openMobileTicketSheet();
        }
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.surfaceContainer : AppColors.surfaceContainerLow,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: isSelected ? AppColors.primary : AppColors.outlineVariant.withOpacity(0.3),
            width: isSelected ? 2 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 12,
              offset: const Offset(0, 3),
            ),
            if (isSelected)
              BoxShadow(
                color: AppColors.primary.withValues(alpha: 0.16),
                blurRadius: 14,
                offset: const Offset(0, 3),
              ),
          ],
        ),
        child: Stack(
          children: [
            if (isSelected)
              Positioned(
                top: 8,
                right: 8,
                child: Container(
                  width: 7,
                  height: 7,
                  decoration: const BoxDecoration(
                    shape: BoxShape.circle,
                    color: AppColors.primary,
                    boxShadow: [BoxShadow(color: AppColors.primary, blurRadius: 8, spreadRadius: 1.5)],
                  ),
                ),
              ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3.5),
                                  decoration: BoxDecoration(
                                    color: isSelected ? AppColors.primary : AppColors.surfaceContainerHigh,
                                    borderRadius: BorderRadius.circular(7),
                                  ),
                                  child: Text(
                                    'MEJA $meja',
                                    style: TextStyle(
                                      color: isSelected ? AppColors.onPrimary : AppColors.primary,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w700,
                                      fontFamily: 'SpaceGrotesk',
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
                                          color: AppColors.onSurface,
                                          fontSize: 13,
                                          fontWeight: FontWeight.bold,
                                          fontFamily: 'Epilogue',
                                        ),
                                      ),
                                      Text(
                                        '$kode • $channel',
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                        style: const TextStyle(
                                          color: AppColors.onSurfaceVariant,
                                          fontSize: 9.5,
                                          fontFamily: 'SpaceGrotesk',
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: AppColors.primary.withOpacity(0.12),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Container(
                                  width: 4.5,
                                  height: 4.5,
                                  decoration: const BoxDecoration(shape: BoxShape.circle, color: AppColors.primary),
                                ),
                                const SizedBox(width: 3.5),
                                Text(
                                  waktu,
                                  style: const TextStyle(
                                    color: AppColors.primary,
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.w600,
                                    fontFamily: 'SpaceGrotesk',
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Container(
                        height: 54,
                        width: double.infinity,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(8),
                          gradient: const LinearGradient(
                            colors: [Color(0xFFF0F9FF), Color(0xFFE0F2FE), Color(0xFFBAE6FD)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          border: Border.all(color: AppColors.primary.withOpacity(0.18)),
                        ),
                        child: Stack(
                          children: [
                            Positioned(
                              right: -8,
                              bottom: -8,
                              child: Icon(
                                Icons.coffee,
                                size: 70,
                                color: AppColors.primary.withOpacity(0.08),
                              ),
                            ),
                            Positioned(
                              bottom: 6,
                              left: 6,
                              right: 6,
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                                decoration: BoxDecoration(
                                  color: AppColors.surfaceContainerLowest.withOpacity(0.75),
                                  borderRadius: BorderRadius.circular(16),
                                ),
                                child: Text(
                                  itemsSummary.isNotEmpty ? itemsSummary : 'Pesanan Warkop Ruma',
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                    color: AppColors.primary,
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.w600,
                                    fontFamily: 'SpaceGrotesk',
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 8),
                      Container(
                        padding: const EdgeInsets.all(7),
                        decoration: BoxDecoration(
                          color: AppColors.surfaceContainerLowest.withOpacity(0.5),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Column(
                          children: items.take(2).map((item) {
                            final qty = _toInt(item['qty'] ?? 1);
                            final namaMenu = item['nama_menu']?.toString() ?? '-';
                            final harga = item['subtotal'] ?? (item['harga'] ?? 0);
                            final catatan = item['catatan']?.toString();

                            return Padding(
                              padding: const EdgeInsets.symmetric(vertical: 1.5),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                                        decoration: BoxDecoration(
                                          color: AppColors.primary.withOpacity(0.2),
                                          borderRadius: BorderRadius.circular(3),
                                        ),
                                        child: Text(
                                          '${qty}x',
                                          style: const TextStyle(
                                            color: AppColors.primary,
                                            fontSize: 9,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 5),
                                      Expanded(
                                        child: Text(
                                          namaMenu,
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                          style: const TextStyle(
                                            color: AppColors.onSurface,
                                            fontSize: 10.5,
                                            fontWeight: FontWeight.w500,
                                          ),
                                        ),
                                      ),
                                      Text(
                                        _formatCurrency(harga),
                                        style: const TextStyle(
                                          color: AppColors.onSurfaceVariant,
                                          fontSize: 9.5,
                                          fontFamily: 'SpaceGrotesk',
                                        ),
                                      ),
                                    ],
                                  ),
                                  if (catatan != null && catatan.isNotEmpty)
                                    Padding(
                                      padding: const EdgeInsets.only(left: 18, top: 1),
                                      child: Text(
                                        catatan,
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                        style: const TextStyle(
                                          color: AppColors.tertiary,
                                          fontSize: 8.5,
                                          fontStyle: FontStyle.italic,
                                        ),
                                      ),
                                    ),
                                ],
                              ),
                            );
                          }).toList(),
                        ),
                      ),
                    ],
                  ),
                  Column(
                    children: [
                      const Divider(color: AppColors.surfaceContainerHigh, height: 14),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Total Tagihan',
                            style: TextStyle(
                              color: AppColors.onSurfaceVariant,
                              fontSize: 10.5,
                              fontFamily: 'SpaceGrotesk',
                            ),
                          ),
                          Text(
                            _formatCurrency(totalHarga),
                            style: const TextStyle(
                              color: AppColors.primary,
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                              fontFamily: 'Epilogue',
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          Expanded(
                            child: ElevatedButton(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: statusPesanan == 'selesai' && statusBayar != 'selesai'
                                    ? AppColors.primary
                                    : (statusPesanan == 'selesai'
                                        ? AppColors.green
                                        : (statusPesanan == 'siap'
                                            ? AppColors.secondary
                                            : AppColors.primaryContainer)),
                                foregroundColor: AppColors.onPrimary,
                                padding: const EdgeInsets.symmetric(vertical: 7),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
                                elevation: 0,
                              ),
                              onPressed: () {
                                final isSplit = order['is_split_bill'] == true;
                                if (statusPesanan == 'menunggu') {
                                  _prosesPesanan(Map<String, dynamic>.from(order as Map));
                                } else if (statusPesanan == 'proses') {
                                  _siapPesanan(id);
                                } else if (statusPesanan == 'siap') {
                                  _selesaiPesanan(id);
                                } else if (statusBayar != 'selesai' && isSplit) {
                                  _openSplitBillDialog(Map<String, dynamic>.from(order as Map));
                                } else if (statusBayar != 'selesai') {
                                  _showValidasiBayarDialog(Map<String, dynamic>.from(order as Map));
                                }
                              },
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(
                                    statusPesanan == 'menunggu'
                                        ? Icons.outdoor_grill
                                        : (statusPesanan == 'proses'
                                            ? Icons.check_circle_outline
                                            : (statusPesanan == 'siap'
                                                ? Icons.room_service_outlined
                                                : (statusBayar != 'selesai'
                                                    ? (order['is_split_bill'] == true
                                                        ? Icons.call_split
                                                        : Icons.payments_outlined)
                                                    : Icons.verified))),
                                    size: 13,
                                    color: AppColors.onPrimary,
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    statusPesanan == 'menunggu'
                                        ? 'Mulai Buat'
                                        : (statusPesanan == 'proses'
                                            ? 'Siap Saji'
                                            : (statusPesanan == 'siap'
                                                ? 'Selesai Antar'
                                                : (statusBayar != 'selesai'
                                                    ? (order['is_split_bill'] == true
                                                        ? 'Bayar Split'
                                                        : 'Validasi Bayar')
                                                    : 'Lunas & Selesai'))),
                                    style: const TextStyle(
                                      fontSize: 9.5,
                                      fontWeight: FontWeight.bold,
                                      fontFamily: 'SpaceGrotesk',
                                      letterSpacing: 0.4,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                          IconButton(
                            style: IconButton.styleFrom(
                              backgroundColor: isSelected
                                  ? AppColors.primary.withOpacity(0.15)
                                  : AppColors.surfaceContainerHigh,
                              padding: const EdgeInsets.all(7),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                            ),
                            icon: Icon(
                              isSelected ? Icons.visibility : Icons.chevron_right,
                              size: 16,
                              color: isSelected ? AppColors.primary : AppColors.onSurfaceVariant,
                            ),
                            onPressed: () {
                              setState(() => _selectedOrderId = id);
                              if (MediaQuery.of(context).size.width < 960) {
                                _openMobileTicketSheet();
                              }
                            },
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTicketDetailPanel() {
    final order = _findSelectedOrder();

    if (order == null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.receipt_long_outlined, size: 44, color: AppColors.outline.withOpacity(0.5)),
            const SizedBox(height: 10),
            const Text(
              'Pilih pesanan untuk melihat tiket KDS',
              style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 12),
            ),
          ],
        ),
      );
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(12),
      child: _buildKitchenTicketContent(order),
    );
  }

  Widget _buildKitchenTicketContent(Map<String, dynamic> order) {
    final id = _toInt(order['id']);
    final meja = order['nomor_meja']?.toString() ?? '-';
    final nama = order['nama_pemesan']?.toString() ?? 'Tamu';
    final kode = order['kode_pesanan']?.toString() ?? '#WR-$id';
    final statusBayar = (order['status'] ?? 'pending').toString().toLowerCase();
    final statusPesanan = (order['status_pesanan'] ?? 'menunggu').toString().toLowerCase();
    final metodeLabel = order['metode_label']?.toString() ?? 'Tunai';
    final items = order['items'] is List ? (order['items'] as List) : const [];
    final splits = order['splits'] is List ? (order['splits'] as List) : const [];
    final totalTerbayarVal = _toDouble(order['total_terbayar']);
    final sisaTagihan = order['sisa_tagihan'] ?? 0;
    final totalHargaVal = _toDouble(order['total_harga']);
    final potonganVal = _toDouble(order['potongan']);
    final subtotal = totalHargaVal + potonganVal;

    return Container(
      decoration: BoxDecoration(
        color: AppColors.surfaceContainer,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.outlineVariant.withOpacity(0.3)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.07),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Container(
            height: 3.5,
            decoration: const BoxDecoration(
              borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
              gradient: LinearGradient(
                colors: [AppColors.primary, AppColors.secondary, AppColors.primary],
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                      decoration: BoxDecoration(
                        color: AppColors.secondaryContainer.withOpacity(0.25),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width: 5,
                            height: 5,
                            decoration: const BoxDecoration(shape: BoxShape.circle, color: AppColors.secondary),
                          ),
                          const SizedBox(width: 4),
                          const Text(
                            'TIKET BARISTA AKTIF',
                            style: TextStyle(
                              color: AppColors.secondary,
                              fontSize: 8.5,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 0.8,
                              fontFamily: 'SpaceGrotesk',
                            ),
                          ),
                        ],
                      ),
                    ),
                    Text(
                      _currentTime,
                      style: const TextStyle(
                        color: AppColors.onSurfaceVariant,
                        fontSize: 9.5,
                        fontFamily: 'SpaceGrotesk',
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  'Meja $meja — $nama',
                  style: const TextStyle(
                    color: AppColors.onSurface,
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    fontFamily: 'Epilogue',
                    letterSpacing: -0.2,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  kode,
                  style: const TextStyle(
                    color: AppColors.onSurfaceVariant,
                    fontSize: 10.5,
                    fontFamily: 'SpaceGrotesk',
                  ),
                ),
                const SizedBox(height: 10),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                  decoration: BoxDecoration(
                    color: AppColors.surfaceContainerLow,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: AppColors.outlineVariant.withOpacity(0.2)),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(5),
                        decoration: BoxDecoration(
                          color: statusBayar == 'selesai'
                              ? AppColors.secondaryContainer.withOpacity(0.2)
                              : AppColors.primaryContainer.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Icon(
                          statusBayar == 'selesai' ? Icons.qr_code_2 : Icons.payments_outlined,
                          size: 15,
                          color: statusBayar == 'selesai' ? AppColors.secondary : AppColors.primary,
                        ),
                      ),
                      const SizedBox(width: 7),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              statusBayar == 'selesai'
                                  ? '$metodeLabel (LUNAS)'
                                  : 'MENUNGGU PEMBAYARAN ($metodeLabel)',
                              style: TextStyle(
                                color: statusBayar == 'selesai' ? AppColors.secondary : AppColors.primary,
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                fontFamily: 'SpaceGrotesk',
                              ),
                            ),
                            Text(
                              'REF: TRX-${id.toString().padLeft(6, '0')}',
                              style: const TextStyle(
                                color: AppColors.onSurfaceVariant,
                                fontSize: 8.5,
                                fontFamily: 'SpaceGrotesk',
                              ),
                            ),
                          ],
                        ),
                      ),
                      if (statusBayar == 'selesai')
                        const Icon(Icons.verified, size: 16, color: AppColors.secondary)
                      else if (splits.isNotEmpty)
                        TextButton(
                          onPressed: () => _openSplitBillDialog(order),
                          style: TextButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                            minimumSize: const Size(52, 22),
                          ),
                          child: const Text(
                            'Split',
                            style: TextStyle(color: AppColors.onPrimary, fontSize: 9.5, fontWeight: FontWeight.bold),
                          ),
                        )
                      else
                        TextButton(
                          onPressed: () => _showValidasiBayarDialog(order),
                          style: TextButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                            minimumSize: const Size(46, 22),
                          ),
                          child: const Text(
                            'Bayar',
                            style: TextStyle(color: AppColors.onPrimary, fontSize: 9.5, fontWeight: FontWeight.bold),
                          ),
                        ),
                    ],
                  ),
                ),
                if (splits.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.all(9),
                    decoration: BoxDecoration(
                      color: AppColors.surfaceContainerLow,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: AppColors.primary.withValues(alpha: 0.35)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Row(
                              children: [
                                const Icon(Icons.call_split, size: 13, color: AppColors.primary),
                                const SizedBox(width: 5),
                                Text(
                                  'SPLIT BILL (${splits.where((s) => s['status'] == 'lunas').length}/${splits.length} Lunas)',
                                  style: const TextStyle(
                                    color: AppColors.primary,
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.bold,
                                    fontFamily: 'SpaceGrotesk',
                                  ),
                                ),
                              ],
                            ),
                            Text(
                              'Sisa: ${_formatCurrency(sisaTagihan)}',
                              style: const TextStyle(
                                color: AppColors.onSurfaceVariant,
                                fontSize: 9.5,
                                fontFamily: 'SpaceGrotesk',
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        ...splits.map((sp) {
                          final lunas = sp['status'] == 'lunas';
                          return Padding(
                            padding: const EdgeInsets.only(bottom: 3),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Expanded(
                                  child: Text(
                                    '${sp['label']} (${sp['metode_label'] ?? sp['metode_pembayaran']})',
                                    style: TextStyle(
                                      color: lunas ? AppColors.green : AppColors.onSurface,
                                      fontSize: 10,
                                      fontFamily: 'SpaceGrotesk',
                                    ),
                                  ),
                                ),
                                Text(
                                  '${_formatCurrency(sp['jumlah'] ?? 0)} • ${lunas ? 'LUNAS' : 'PENDING'}',
                                  style: TextStyle(
                                    color: lunas ? AppColors.green : AppColors.primary,
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    fontFamily: 'SpaceGrotesk',
                                  ),
                                ),
                              ],
                            ),
                          );
                        }),
                        if (totalTerbayarVal > 0 && statusBayar != 'selesai') ...[
                          const SizedBox(height: 4),
                          Text(
                            'Terbayar: ${_formatCurrency(totalTerbayarVal)}',
                            style: const TextStyle(
                              color: AppColors.green,
                              fontSize: 9.5,
                              fontFamily: 'SpaceGrotesk',
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
                const SizedBox(height: 12),
                const Text(
                  'Rincian Menu & Catatan Barista:',
                  style: TextStyle(
                    color: AppColors.onSurfaceVariant,
                    fontSize: 10.5,
                    fontWeight: FontWeight.bold,
                    fontFamily: 'SpaceGrotesk',
                  ),
                ),
                const SizedBox(height: 6),
                ...items.map((it) {
                  final qty = _toInt(it['qty'] ?? 1);
                  final namaMenu = it['nama_menu']?.toString() ?? '-';
                  final harga = it['subtotal'] ?? (it['harga'] ?? 0);
                  final kategori = it['kategori']?.toString() ?? 'Kopi & Minuman';
                  final catatan = it['catatan']?.toString();

                  return Container(
                    margin: const EdgeInsets.only(bottom: 6),
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppColors.surfaceContainerLow,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                              decoration: BoxDecoration(
                                color: AppColors.primary,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                '${qty}x',
                                style: const TextStyle(
                                  color: AppColors.onPrimary,
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                            const SizedBox(width: 7),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    namaMenu,
                                    style: const TextStyle(
                                      color: AppColors.onSurface,
                                      fontSize: 12,
                                      fontWeight: FontWeight.bold,
                                      fontFamily: 'Epilogue',
                                    ),
                                  ),
                                  Text(
                                    kategori,
                                    style: const TextStyle(
                                      color: AppColors.onSurfaceVariant,
                                      fontSize: 9.5,
                                      fontFamily: 'SpaceGrotesk',
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            Text(
                              _formatCurrency(harga),
                              style: const TextStyle(
                                color: AppColors.primary,
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                fontFamily: 'SpaceGrotesk',
                              ),
                            ),
                          ],
                        ),
                        if (catatan != null && catatan.isNotEmpty) ...[
                          const SizedBox(height: 5),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                            decoration: BoxDecoration(
                              color: AppColors.surfaceContainerHighest.withOpacity(0.6),
                              borderRadius: BorderRadius.circular(5),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.coffee_maker_outlined, size: 11, color: AppColors.tertiary),
                                const SizedBox(width: 4),
                                Expanded(
                                  child: Text(
                                    'Catatan: $catatan',
                                    style: const TextStyle(
                                      color: AppColors.tertiary,
                                      fontSize: 9.5,
                                      fontFamily: 'SpaceGrotesk',
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ],
                    ),
                  );
                }),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.surfaceContainerLow.withOpacity(0.5),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'Subtotal (${items.length} Menu)',
                            style: const TextStyle(color: AppColors.onSurfaceVariant, fontSize: 10.5),
                          ),
                          Text(
                            _formatCurrency(subtotal),
                            style: const TextStyle(
                              color: AppColors.onSurface,
                              fontSize: 10.5,
                              fontFamily: 'SpaceGrotesk',
                            ),
                          ),
                        ],
                      ),
                      if (potonganVal > 0) ...[
                        const SizedBox(height: 3),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Diskon Promo', style: TextStyle(color: AppColors.secondary, fontSize: 10.5)),
                            Text(
                              '- ${_formatCurrency(potonganVal)}',
                              style: const TextStyle(
                                color: AppColors.secondary,
                                fontSize: 10.5,
                                fontFamily: 'SpaceGrotesk',
                              ),
                            ),
                          ],
                        ),
                      ],
                      const Divider(color: AppColors.surfaceContainerHigh, height: 14),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'TOTAL TAGIHAN',
                                style: TextStyle(
                                  color: AppColors.onSurfaceVariant,
                                  fontSize: 8.5,
                                  letterSpacing: 0.8,
                                  fontFamily: 'SpaceGrotesk',
                                ),
                              ),
                              Text(
                                statusBayar == 'selesai' ? 'Lunas via $metodeLabel' : 'Belum Lunas',
                                style: TextStyle(
                                  color: statusBayar == 'selesai' ? AppColors.secondary : AppColors.primary,
                                  fontSize: 9.5,
                                  fontFamily: 'SpaceGrotesk',
                                ),
                              ),
                            ],
                          ),
                          Text(
                            _formatCurrency(totalHargaVal),
                            style: const TextStyle(
                              color: AppColors.primary,
                              fontSize: 19,
                              fontWeight: FontWeight.bold,
                              fontFamily: 'Epilogue',
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: statusPesanan == 'selesai'
                        ? AppColors.surfaceContainerHighest
                        : AppColors.primaryContainer,
                    foregroundColor: statusPesanan == 'selesai' ? AppColors.green : AppColors.onPrimary,
                    padding: const EdgeInsets.symmetric(vertical: 11),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    elevation: statusPesanan == 'selesai' ? 0 : 2,
                  ),
                  onPressed: statusPesanan == 'selesai'
                      ? null
                      : () {
                          if (statusPesanan == 'menunggu') {
                            _prosesPesanan(order);
                          } else if (statusPesanan == 'proses') {
                            _siapPesanan(id);
                          } else if (statusPesanan == 'siap') {
                            _selesaiPesanan(id);
                          }
                        },
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        statusPesanan == 'selesai'
                            ? Icons.check_circle
                            : (statusPesanan == 'siap'
                                ? Icons.room_service_outlined
                                : Icons.outdoor_grill),
                        size: 16,
                      ),
                      const SizedBox(width: 7),
                      Text(
                        statusPesanan == 'menunggu'
                            ? 'Kirim ke Dapur / Mulai Buat'
                            : (statusPesanan == 'proses'
                                ? 'Tandai Siap Saji'
                                : (statusPesanan == 'siap'
                                    ? 'Selesaikan Antaran ke Meja'
                                    : 'Pesanan Sudah Diantar')),
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          fontFamily: 'SpaceGrotesk',
                          letterSpacing: 0.4,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 8),
                if (statusBayar != 'selesai') ...[
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton.icon(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: AppColors.onPrimary,
                            padding: const EdgeInsets.symmetric(vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(9)),
                            elevation: 2,
                          ),
                          icon: Icon(
                            splits.isNotEmpty ? Icons.call_split : Icons.payments_outlined,
                            size: 15,
                          ),
                          label: Text(
                            splits.isNotEmpty
                                ? 'Bayar Split (${_formatCurrency(sisaTagihan)})'
                                : 'Validasi Bayar',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              fontFamily: 'SpaceGrotesk',
                            ),
                          ),
                          onPressed: () {
                            if (splits.isNotEmpty) {
                              _openSplitBillDialog(order);
                            } else {
                              _showValidasiBayarDialog(order);
                            }
                          },
                        ),
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: OutlinedButton.icon(
                          style: OutlinedButton.styleFrom(
                            foregroundColor: AppColors.tertiary,
                            side: BorderSide(color: AppColors.tertiary.withValues(alpha: 0.55)),
                            padding: const EdgeInsets.symmetric(vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(9)),
                          ),
                          icon: const Icon(Icons.call_split, size: 15),
                          label: Text(
                            splits.isNotEmpty ? 'Kelola Split' : 'Split Bill',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              fontFamily: 'SpaceGrotesk',
                            ),
                          ),
                          onPressed: () => _openSplitBillDialog(order),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 7),
                  if (!splits.any((s) => s['status'] == 'lunas') && _getMergeableOrders(order).isNotEmpty) ...[
                    Builder(
                      builder: (_) {
                        final mergeables = _getMergeableOrders(order);
                        final sameTableCount =
                            mergeables.where((m) => '${m['meja_id']}' == '${order['meja_id']}').length;
                        return SizedBox(
                          width: double.infinity,
                          child: OutlinedButton.icon(
                            style: OutlinedButton.styleFrom(
                              foregroundColor: AppColors.secondary,
                              side: BorderSide(color: AppColors.secondary.withValues(alpha: 0.5)),
                              padding: const EdgeInsets.symmetric(vertical: 9),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(9)),
                            ),
                            icon: const Icon(Icons.call_merge, size: 15),
                            label: Text(
                              sameTableCount > 0
                                  ? 'Gabung Pesanan ($sameTableCount lagi di Meja $meja)'
                                  : 'Gabung Pesanan (Merge Bill)',
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                fontFamily: 'SpaceGrotesk',
                              ),
                            ),
                            onPressed: () => _showMergeBillDialog(order),
                          ),
                        );
                      },
                    ),
                    const SizedBox(height: 7),
                  ],
                ] else if (splits.isNotEmpty) ...[
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.green,
                        side: BorderSide(color: AppColors.green.withValues(alpha: 0.5)),
                        padding: const EdgeInsets.symmetric(vertical: 9),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      icon: const Icon(Icons.call_split, size: 15),
                      label: const Text(
                        'Lihat Rincian Split Bill (Lunas)',
                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, fontFamily: 'SpaceGrotesk'),
                      ),
                      onPressed: () => _openSplitBillDialog(order),
                    ),
                  ),
                  const SizedBox(height: 7),
                ],
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.primary,
                          side: BorderSide(color: AppColors.primary.withOpacity(0.4)),
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                        icon: const Icon(Icons.receipt_long, size: 14),
                        label: const Text(
                          'Cetak Struk',
                          style: TextStyle(fontSize: 10.5, fontFamily: 'SpaceGrotesk'),
                        ),
                        onPressed: () => _cetakStruk(order),
                      ),
                    ),
                    const SizedBox(width: 6),
                    Expanded(
                      child: OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.secondary,
                          side: BorderSide(color: AppColors.secondary.withOpacity(0.4)),
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                        icon: const Icon(Icons.check_circle_outline, size: 14),
                        label: const Text(
                          'Siap Saji',
                          style: TextStyle(fontSize: 10.5, fontFamily: 'SpaceGrotesk'),
                        ),
                        onPressed: () => _siapPesanan(id),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
            decoration: BoxDecoration(
              color: AppColors.surfaceContainerLowest.withOpacity(0.8),
              borderRadius: const BorderRadius.vertical(bottom: Radius.circular(16)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Icon(Icons.bluetooth, size: 13, color: AppColors.secondary),
                    SizedBox(width: 4),
                    Text(
                      'Bluetooth Struk POS: Ready',
                      style: TextStyle(
                        color: AppColors.onSurfaceVariant,
                        fontSize: 8.5,
                        fontFamily: 'SpaceGrotesk',
                      ),
                    ),
                  ],
                ),
                PopupMenuButton<String>(
                  tooltip: 'Cetak Dokumen',
                  onSelected: (val) {
                    if (val == 'struk') {
                      _cetakStruk(order);
                    } else if (val == 'dapur') {
                      _cetakTiketDapur(order);
                    }
                  },
                  itemBuilder: (_) => const [
                    PopupMenuItem(
                      value: 'dapur',
                      height: 32,
                      child: Row(
                        children: [
                          Icon(Icons.restaurant, size: 14, color: AppColors.primary),
                          SizedBox(width: 8),
                          Text('Cetak Tiket Dapur', style: TextStyle(fontSize: 11)),
                        ],
                      ),
                    ),
                    PopupMenuItem(
                      value: 'struk',
                      height: 32,
                      child: Row(
                        children: [
                          Icon(Icons.receipt_long, size: 14, color: AppColors.secondary),
                          SizedBox(width: 8),
                          Text('Cetak Struk Bayar', style: TextStyle(fontSize: 11)),
                        ],
                      ),
                    ),
                  ],
                  child: const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.print, size: 12, color: AppColors.primary),
                        SizedBox(width: 3),
                        Text(
                          'Cetak',
                          style: TextStyle(color: AppColors.primary, fontSize: 9.5, fontWeight: FontWeight.bold),
                        ),
                        Icon(Icons.arrow_drop_down, size: 12, color: AppColors.primary),
                      ],
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

  Widget _buildMobileTicketBar() {
    final order = _findSelectedOrder();
    if (order == null) return const SizedBox.shrink();

    final meja = order['nomor_meja']?.toString() ?? '-';
    final nama = order['nama_pemesan']?.toString() ?? 'Tamu';
    final total = _formatCurrency(order['total_harga'] ?? 0);

    return InkWell(
      onTap: _openMobileTicketSheet,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        decoration: BoxDecoration(
          color: AppColors.surfaceContainerHigh,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.primary.withOpacity(0.5)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.10),
              blurRadius: 12,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(7),
              decoration: BoxDecoration(color: AppColors.primary, borderRadius: BorderRadius.circular(7)),
              child: const Icon(Icons.receipt_long, color: AppColors.onPrimary, size: 16),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Tiket Aktif: Meja $meja ($nama)',
                    style: const TextStyle(color: AppColors.onSurface, fontWeight: FontWeight.bold, fontSize: 11.5),
                  ),
                  Text(
                    'Total Tagihan: $total',
                    style: const TextStyle(color: AppColors.primary, fontSize: 10.5, fontFamily: 'SpaceGrotesk'),
                  ),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
              decoration: BoxDecoration(
                color: AppColors.primaryContainer,
                borderRadius: BorderRadius.circular(16),
              ),
              child: const Row(
                children: [
                  Text(
                    'Buka Tiket',
                    style: TextStyle(color: AppColors.onPrimaryContainer, fontWeight: FontWeight.bold, fontSize: 9.5),
                  ),
                  Icon(Icons.keyboard_arrow_up, size: 15, color: AppColors.onPrimaryContainer),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _openMobileTicketSheet() {
    final order = _findSelectedOrder();
    if (order == null) return;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        height: MediaQuery.of(context).size.height * 0.85,
        padding: const EdgeInsets.only(top: 8),
        decoration: const BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        child: Column(
          children: [
            Container(
              width: 36,
              height: 4,
              decoration: BoxDecoration(
                color: AppColors.outlineVariant,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(12),
                child: _buildKitchenTicketContent(order),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStationHealthCard() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest.withOpacity(0.8),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.outlineVariant.withOpacity(0.15)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          SizedBox(
            width: 60,
            height: 28,
            child: Stack(
              children: [
                _buildAvatarCircle('RM', AppColors.primary, 0),
                _buildAvatarCircle('DN', AppColors.tertiary, 17),
                _buildAvatarCircle('AG', AppColors.secondary, 34),
              ],
            ),
          ),
          const SizedBox(width: 8),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'TIM BARISTA AKTIF',
                  style: TextStyle(
                    color: AppColors.onSurfaceVariant,
                    fontSize: 8.5,
                    fontWeight: FontWeight.bold,
                    letterSpacing: 0.6,
                    fontFamily: 'SpaceGrotesk',
                  ),
                ),
                Text(
                  'Rama (Barista), Doni (Manual), Agus (Kitchen)',
                  style: TextStyle(
                    color: AppColors.onSurface,
                    fontSize: 10.5,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAvatarCircle(String initials, Color color, double left) {
    return Positioned(
      left: left,
      child: Container(
        width: 26,
        height: 26,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          color: color.withOpacity(0.2),
          border: Border.all(color: AppColors.background, width: 2),
        ),
        child: Center(
          child: Text(
            initials,
            style: TextStyle(
              color: color,
              fontSize: 9,
              fontWeight: FontWeight.bold,
              fontFamily: 'SpaceGrotesk',
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyOrdersState() {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 20),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLow,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Center(
        child: Column(
          children: [
            Container(
              width: 50,
              height: 50,
              decoration: BoxDecoration(shape: BoxShape.circle, color: AppColors.primary.withOpacity(0.1)),
              child: const Icon(Icons.coffee, size: 24, color: AppColors.primary),
            ),
            const SizedBox(height: 12),
            const Text(
              'Tidak Ada Antrean Pesanan',
              style: TextStyle(
                color: AppColors.onSurface,
                fontSize: 15,
                fontWeight: FontWeight.bold,
                fontFamily: 'Epilogue',
              ),
            ),
            const SizedBox(height: 5),
            const Text(
              'Pesanan masuk dari pelanggan scan QR atau kasir akan langsung tampil di sini secara real-time.',
              textAlign: TextAlign.center,
              style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 11.5),
            ),
            const SizedBox(height: 14),
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.surfaceContainerHigh,
                foregroundColor: AppColors.primary,
              ),
              icon: const Icon(Icons.refresh, size: 15),
              label: const Text('Segarkan Antrean', style: TextStyle(fontSize: 11)),
              onPressed: () => _fetchDashboard(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStitchFooter() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
      decoration: BoxDecoration(
        color: AppColors.surfaceContainerLowest.withOpacity(0.9),
        border: Border(top: BorderSide(color: AppColors.outlineVariant.withOpacity(0.15), width: 1)),
      ),
      child: const Wrap(
        alignment: WrapAlignment.spaceBetween,
        crossAxisAlignment: WrapCrossAlignment.center,
        spacing: 12,
        children: [
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                'Warkop Ruma Sidikalang',
                style: TextStyle(color: AppColors.primary, fontSize: 9.5, fontWeight: FontWeight.w600),
              ),
              Padding(
                padding: EdgeInsets.symmetric(horizontal: 4),
                child: Text('•', style: TextStyle(color: AppColors.outline, fontSize: 9)),
              ),
              Text('POS & KDS v2.4', style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 9.5)),
            ],
          ),
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text('Terminal #01', style: TextStyle(color: AppColors.onSurfaceVariant, fontSize: 9.5)),
              Padding(
                padding: EdgeInsets.symmetric(horizontal: 4),
                child: Text('•', style: TextStyle(color: AppColors.outline, fontSize: 9)),
              ),
              Text(
                'Printer POS: Terhubung',
                style: TextStyle(color: AppColors.secondary, fontSize: 9.5, fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSubScreen(int index) {
    switch (index) {
      case 1:
        return const MenuScreen();
      case 2:
        return const MejaScreen();
      case 3:
        return const AdminRiwayatScreen();
      case 4:
        return const PengaturanScreen();
      default:
        return const SizedBox.shrink();
    }
  }

  Widget _buildDrawer() {
    return Drawer(
      backgroundColor: AppColors.sidebarBg,
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.fromLTRB(16, 28, 16, 20),
            decoration: const BoxDecoration(
              color: AppColors.sidebarBg,
              border: Border(bottom: BorderSide(color: Color(0xFF1E293B), width: 1)),
            ),
            child: Row(
              children: [
                Container(
                  width: 42,
                  height: 42,
                  decoration: BoxDecoration(
                    color: AppColors.primaryContainer,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Center(
                    child: Icon(Icons.storefront_rounded, color: Colors.white, size: 22),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'WARKOP RUMA',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          fontFamily: 'Epilogue',
                          letterSpacing: 0.3,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '$_roleLabel Panel • $_userName',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: AppColors.sidebarText,
                          fontSize: 11,
                          fontFamily: 'SpaceGrotesk',
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const Padding(
            padding: EdgeInsets.fromLTRB(18, 16, 16, 6),
            child: Align(
              alignment: Alignment.centerLeft,
              child: Text(
                'UTAMA',
                style: TextStyle(
                  color: Color(0xFF64748B),
                  fontSize: 10,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 1.0,
                ),
              ),
            ),
          ),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
              children: [
                if (widget.cashierMode)
                  _buildDrawerTile(0, Icons.view_kanban, 'Antrean Pesanan (KDS POS)'),
                _buildDrawerTile(3, Icons.history, 'Riwayat Transaksi'),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.all(12),
            child: ListTile(
              tileColor: const Color(0xFF2A121A),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              leading: const Icon(Icons.logout, color: Color(0xFFF87171), size: 20),
              title: const Text(
                'Keluar',
                style: TextStyle(color: Color(0xFFF87171), fontWeight: FontWeight.bold, fontSize: 12.5),
              ),
              onTap: () {
                Navigator.pop(context);
                ConfirmDialog.show(
                  context,
                  title: 'Keluar',
                  message: 'Yakin ingin keluar dari akun $_roleLabel?',
                  confirmLabel: 'Keluar',
                  confirmColor: AppColors.error,
                  onConfirm: _logout,
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDrawerTile(int index, IconData icon, String label) {
    final active = _selectedNavIndex == index;

    return Container(
      margin: const EdgeInsets.only(bottom: 4),
      decoration: BoxDecoration(
        color: active ? AppColors.sidebarActiveBg : Colors.transparent,
        borderRadius: BorderRadius.circular(10),
        border: active ? Border.all(color: AppColors.primaryContainer.withOpacity(0.45)) : null,
      ),
      child: ListTile(
        dense: true,
        leading: Icon(
          icon,
          color: active ? const Color(0xFF38BDF8) : AppColors.sidebarText,
          size: 20,
        ),
        title: Text(
          label,
          style: TextStyle(
            color: active ? Colors.white : AppColors.sidebarText,
            fontWeight: active ? FontWeight.bold : FontWeight.w500,
            fontSize: 12.5,
            fontFamily: 'SpaceGrotesk',
          ),
        ),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        onTap: () {
          Navigator.pop(context);
          setState(() => _selectedNavIndex = index);
        },
      ),
    );
  }
}