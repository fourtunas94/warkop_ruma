import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../services/api_service.dart';
import '../../widgets/app_widgets.dart';
import '../auth/login_screen.dart';
import '../admin/kategori_screen.dart';
import '../admin/menu_screen.dart';
import '../admin/meja_screen.dart';
import '../admin/users_screen.dart';
import '../admin/riwayat_screen.dart';
import '../admin/promo_screen.dart';
import '../admin/pengaturan_screen.dart';
import 'kasir_riwayat_screen.dart';

class KasirDashboardScreen extends StatefulWidget {
  const KasirDashboardScreen({super.key});
  @override
  State<KasirDashboardScreen> createState() => _KasirDashboardScreenState();
}

class _KasirDashboardScreenState extends State<KasirDashboardScreen> {
  int _selectedIndex = 0;
  List<dynamic> _pendingOrders = [];
  Map<String, dynamic> _stats = {};
  bool _loading = true;
  bool _isFetching = false;
  String _lastFingerprint = '';
  Timer? _pollingTimer;
  String _userName = '';

  @override
  void initState() {
    super.initState();
    _loadUserName();
    _fetchDashboard();
    _startPolling();
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadUserName() async {
    final prefs = await SharedPreferences.getInstance();
    if (!mounted) return;
    setState(() => _userName = prefs.getString('user_name') ?? 'Kasir');
  }

  void _startPolling() {
    _pollingTimer = Timer.periodic(const Duration(seconds: 12), (_) {
      if (mounted && _selectedIndex == 0 && !_isFetching) {
        _fetchDashboard(silent: true);
      }
    });
  }

  Future<void> _fetchDashboard({bool silent = false}) async {
    if (_isFetching) return;
    _isFetching = true;
    if (!silent) setState(() => _loading = true);
    try {
      final result = await ApiService.kasirDashboard();
      if (result['success'] == true && mounted) {
        final pending = result['pesanan_pending'] ?? [];
        final fingerprint = jsonEncode([
          result['total_pending'],
          result['total_selesai'],
          result['pendapatan'],
          pending,
        ]);
        if (silent && fingerprint == _lastFingerprint && !_loading) {
          return;
        }
        _lastFingerprint = fingerprint;
        setState(() {
          _pendingOrders = pending;
          _stats = {
            'total_pending': result['total_pending'] ?? 0,
            'total_selesai': result['total_selesai'] ?? 0,
            'pendapatan': result['pendapatan'] ?? 0,
          };
          _loading = false;
        });
      } else if (mounted) {
        if (_loading) setState(() => _loading = false);
        if (!silent) _showSnack(result['message']?.toString() ?? 'Gagal memuat data kasir.', false);
      }
    } catch (e) {
      if (mounted) {
        if (_loading) setState(() => _loading = false);
        if (!silent) _showSnack('Tidak dapat terhubung ke server: $e', false);
      }
    } finally {
      _isFetching = false;
    }
  }

  Future<void> _telahBayar(int pesananId) async {
    final result = await ApiService.telahBayar(pesananId);
    if (mounted) {
      _showSnack(
        result['success'] == true ? 'Pembayaran dikonfirmasi!' : (result['message']?.toString() ?? 'Gagal'),
        result['success'] == true,
      );
      if (result['success'] == true) _fetchDashboard(silent: true);
    }
  }

  Future<void> _siapPesanan(int pesananId) async {
    final result = await ApiService.siapPesanan(pesananId);
    if (mounted) {
      _showSnack(
        result['success'] == true ? 'Pesanan selesai!' : (result['message']?.toString() ?? 'Gagal'),
        result['success'] == true,
      );
      if (result['success'] == true) _fetchDashboard(silent: true);
    }
  }

  void _showSnack(String msg, bool success) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: success ? const Color(0xFF22C55E) : const Color(0xFFF43F5E),
        behavior: SnackBarBehavior.floating,
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

  double _toDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    final cleaned = value.toString().replaceAll(RegExp(r'[^0-9.-]'), '');
    return double.tryParse(cleaned) ?? 0.0;
  }

  String _formatCurrency(dynamic value) {
    final amount = _toDouble(value);
    return NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0).format(amount);
  }

  String _formatTime(String? dateStr) {
    if (dateStr == null) return '-';
    try {
      final dt = DateTime.parse(dateStr).toLocal();
      return DateFormat('HH:mm').format(dt);
    } catch (_) {
      return '-';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF020617),
      appBar: AppBar(
        backgroundColor: const Color(0xFF06111F),
        elevation: 0,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Kasir Dashboard', style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
            Text('Halo, $_userName', style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11)),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: Color(0xFF94A3B8)),
            onPressed: () => _fetchDashboard(),
          ),
          IconButton(
            icon: const Icon(Icons.logout, color: Color(0xFF94A3B8)),
            onPressed: () => ConfirmDialog.show(
              context,
              title: 'Keluar',
              message: 'Yakin ingin keluar dari akun?',
              confirmLabel: 'Keluar',
              confirmColor: const Color(0xFFF43F5E),
              onConfirm: _logout,
            ),
          ),
        ],
      ),
      drawer: _buildManagementDrawer(),
      body: _selectedIndex == 0 ? _buildDashboard() : const KasirRiwayatScreen(),
      bottomNavigationBar: NavigationBar(
        backgroundColor: const Color(0xFF06111F),
        selectedIndex: _selectedIndex,
        indicatorColor: const Color(0xFF008CFF).withOpacity(0.2),
        onDestinationSelected: (i) => setState(() => _selectedIndex = i),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.dashboard_outlined, color: Color(0xFF94A3B8)),
            selectedIcon: Icon(Icons.dashboard, color: Color(0xFF008CFF)),
            label: 'Dashboard',
          ),
          NavigationDestination(
            icon: Icon(Icons.history_outlined, color: Color(0xFF94A3B8)),
            selectedIcon: Icon(Icons.history, color: Color(0xFF008CFF)),
            label: 'Riwayat',
          ),
        ],
      ),
    );
  }

  Widget _buildManagementDrawer() {
    return Drawer(
      backgroundColor: const Color(0xFF06111F),
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          DrawerHeader(
            decoration: const BoxDecoration(color: Color(0xFF0F2135)),
            child: Align(
              alignment: Alignment.bottomLeft,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.end,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.point_of_sale, color: Color(0xFF008CFF), size: 32),
                  const SizedBox(height: 8),
                  const Text(
                    'Menu Kasir',
                    style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  Text(_userName, style: const TextStyle(color: Color(0xFF94A3B8))),
                ],
              ),
            ),
          ),
          _managementItem(Icons.category_outlined, 'Kategori Menu', const KategoriScreen()),
          _managementItem(Icons.restaurant_menu, 'Kelola Menu', const MenuScreen()),
          _managementItem(Icons.table_restaurant_outlined, 'Meja & QR Code', const MejaScreen()),
          _managementItem(Icons.history, 'Riwayat Transaksi', const AdminRiwayatScreen()),
          _managementItem(Icons.people_outline, 'Kelola User Kasir', const UsersScreen()),
          _managementItem(Icons.local_offer_outlined, 'Promo', const PromoScreen()),
          _managementItem(Icons.settings_outlined, 'Pengaturan Toko', const PengaturanScreen()),
        ],
      ),
    );
  }

  Widget _managementItem(IconData icon, String label, Widget screen) {
    return ListTile(
      leading: Icon(icon, color: const Color(0xFF94A3B8)),
      title: Text(label, style: const TextStyle(color: Colors.white)),
      onTap: () {
        Navigator.pop(context);
        Navigator.push(context, MaterialPageRoute(builder: (_) => screen));
      },
    );
  }

  Widget _buildDashboard() {
    return LoadingOverlay(
      isLoading: _loading,
      child: RefreshIndicator(
        color: const Color(0xFF008CFF),
        backgroundColor: const Color(0xFF0F2135),
        onRefresh: () => _fetchDashboard(),
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            _buildStatsRow(),
            const SizedBox(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Antrean Pesanan',
                  style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFB923C).withOpacity(0.15),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: const Color(0xFFFB923C).withOpacity(0.4)),
                  ),
                  child: Text(
                    '${_pendingOrders.length} antrean',
                    style: const TextStyle(color: Color(0xFFFB923C), fontSize: 12, fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            if (_pendingOrders.isEmpty && !_loading)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 60),
                child: EmptyState(
                  message: 'Tidak ada pesanan masuk\nSedang menunggu pesanan baru...',
                  icon: Icons.receipt_long_outlined,
                ),
              )
            else
              ..._pendingOrders.map((order) => _buildOrderCard(order)),
          ],
        ),
      ),
    );
  }

  Widget _buildStatsRow() {
    return Row(
      children: [
        Expanded(
          child: StatCard(
            label: 'Pending',
            value: '${_stats['total_pending'] ?? 0}',
            icon: Icons.pending_actions,
            iconColor: const Color(0xFFFB923C),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: StatCard(
            label: 'Selesai',
            value: '${_stats['total_selesai'] ?? 0}',
            icon: Icons.check_circle_outline,
            iconColor: const Color(0xFF22C55E),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: StatCard(
            label: 'Pendapatan',
            value: _formatCurrency(_stats['pendapatan'] ?? 0),
            icon: Icons.payments_outlined,
            iconColor: const Color(0xFF008CFF),
            valueColor: const Color(0xFF22C55E),
          ),
        ),
      ],
    );
  }

  Widget _buildOrderCard(Map<String, dynamic> order) {
    final items = (order['items'] as List? ?? []);
    final metodeBayar = order['metode_pembayaran']?.toString() ?? '';
    final status = order['status']?.toString() ?? '';
    final isTunai = metodeBayar.toLowerCase() == 'tunai';
    final isSelesai = status.toLowerCase() == 'selesai';

    return AppCard(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: const BoxDecoration(
              border: Border(bottom: BorderSide(color: Color(0x1AFFFFFF))),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: const Color(0xFF008CFF).withOpacity(0.15),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    '#${order['nomor_antrian'] ?? '-'}',
                    style: const TextStyle(
                      color: Color(0xFF008CFF),
                      fontWeight: FontWeight.bold,
                      fontSize: 14,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        order['nama_pemesan']?.toString() ?? 'Unknown',
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13),
                      ),
                      Text(
                        'Meja ${order['nomor_meja'] ?? '-'}',
                        style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                      ),
                    ],
                  ),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    StatusBadge(status: status),
                    const SizedBox(height: 4),
                    Text(
                      _formatTime(order['created_at']?.toString()),
                      style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                    ),
                  ],
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                ...items.take(3).map((item) => Padding(
                      padding: const EdgeInsets.only(bottom: 4),
                      child: Row(
                        children: [
                          const Icon(Icons.circle, size: 5, color: Color(0xFF94A3B8)),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              '${item['nama_menu'] ?? item['nama'] ?? '-'}',
                              style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                            ),
                          ),
                          Text(
                            'x${item['jumlah'] ?? item['qty'] ?? 1}',
                            style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                          ),
                        ],
                      ),
                    )),
                if (items.length > 3)
                  Text(
                    '+${items.length - 3} item lainnya',
                    style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                  ),
                const SizedBox(height: 10),
                const Divider(color: Color(0x1AFFFFFF), height: 1),
                const SizedBox(height: 10),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: isTunai
                            ? const Color(0xFF22C55E).withOpacity(0.15)
                            : const Color(0xFF7C3AED).withOpacity(0.15),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(
                          color: isTunai
                              ? const Color(0xFF22C55E).withOpacity(0.4)
                              : const Color(0xFF7C3AED).withOpacity(0.4),
                        ),
                      ),
                      child: Text(
                        metodeBayar.toUpperCase(),
                        style: TextStyle(
                          color: isTunai ? const Color(0xFF22C55E) : const Color(0xFF7C3AED),
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    Text(
                      _formatCurrency(order['total_harga'] ?? 0),
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 15,
                      ),
                    ),
                  ],
                ),
                if (isTunai && status.toLowerCase() == 'pending') ...[
                  const SizedBox(height: 10),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      icon: const Icon(Icons.payments, size: 16),
                      label: const Text('Telah Bayar'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF22C55E),
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        padding: const EdgeInsets.symmetric(vertical: 10),
                      ),
                      onPressed: () => _telahBayar(order['id']),
                    ),
                  ),
                ],
                if (isSelesai) ...[
                  const SizedBox(height: 10),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      icon: const Icon(Icons.check_circle, size: 16),
                      label: const Text('Siap Diambil'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF008CFF),
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        padding: const EdgeInsets.symmetric(vertical: 10),
                      ),
                      onPressed: () => _siapPesanan(order['id']),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}