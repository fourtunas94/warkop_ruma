import 'dart:async';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../services/api_service.dart';
import '../auth/login_screen.dart';
import 'kategori_screen.dart';
import 'menu_screen.dart';
import 'stok_screen.dart';
import 'meja_screen.dart';
import 'users_screen.dart';
import 'riwayat_screen.dart';

class AdminDashboardScreen extends StatefulWidget {
  const AdminDashboardScreen({super.key, this.cashierMode = false});

  final bool cashierMode;

  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen> {
  // 0: Dashboard, 1: Kategori, 2: Menu, 3: Stok, 4: Meja, 5: Users, 6: Riwayat
  int _selectedNavIndex = 0;

  bool _loading = false;
  String _userName = 'Admin Manajemen';

  // Stats for Dashboard
  int _totalPesanan = 0;
  dynamic _totalPendapatan = 0;
  int _totalMenu = 0;
  int _totalMeja = 0;
  List<dynamic> _recentOrders = [];

  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();

  @override
  void initState() {
    super.initState();
    _loadUserName();
    _fetchDashboardData();
  }

  Future<void> _loadUserName() async {
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      setState(() {
        _userName = prefs.getString('user_name') ?? 'Admin Manajemen';
      });
    }
  }

  Future<void> _fetchDashboardData() async {
    setState(() => _loading = true);
    try {
      final res = await ApiService.kasirPosDashboard();
      if (res['success'] == true && mounted) {
        final list = (res['pesanan_terbaru'] as List<dynamic>?) ??
            (res['pesanans'] as List<dynamic>?) ??
            [];
        setState(() {
          _recentOrders = list;
          _totalPesanan = res['total_pesanan'] ?? list.length;
          _totalPendapatan = res['pendapatan'] ?? 0;
          _totalMenu = res['total_menu'] ?? 0;
          _totalMeja = res['total_meja'] ?? 0;
          _loading = false;
        });
      } else if (mounted) {
        // Fallback: fetch menus and mejas directly if pos-dashboard did not return them
        _fetchFallbackStats();
      }
    } catch (_) {
      if (mounted) _fetchFallbackStats();
    }
  }

  Future<void> _fetchFallbackStats() async {
    try {
      final results = await Future.wait([
        ApiService.getMenus(),
        ApiService.getMejas(),
        ApiService.adminRiwayat(),
      ]);
      final menusRes = results[0];
      final mejasRes = results[1];
      final riwayatRes = results[2];

      if (mounted) {
        setState(() {
          if (menusRes['success'] == true) {
            _totalMenu = (menusRes['menus'] as List<dynamic>?)?.length ?? 0;
          }
          if (mejasRes['success'] == true) {
            _totalMeja = (mejasRes['mejas'] as List<dynamic>?)?.length ?? 0;
          }
          if (riwayatRes['success'] == true) {
            _totalPesanan = riwayatRes['total_pesanan'] ?? 0;
            _totalPendapatan = riwayatRes['total_pendapatan'] ?? 0;
            _recentOrders = (riwayatRes['riwayat'] as List<dynamic>?) ?? [];
          }
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
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
    final num = (value is int) ? value.toDouble() : (value as double? ?? 0.0);
    return NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0).format(num);
  }

  static final ThemeData _adminTheme = ThemeData(
    useMaterial3: true,
    brightness: Brightness.light,
    scaffoldBackgroundColor: const Color(0xFFF4F6FA),
    colorScheme: ColorScheme.fromSeed(
      seedColor: const Color(0xFF008CFF),
      brightness: Brightness.light,
    ),
    cardColor: Colors.white,
    dividerColor: const Color(0xFFF1F5F9),
  );

  @override
  Widget build(BuildContext context) {
    final isDesktop = MediaQuery.of(context).size.width >= 900;

    return Theme(
      data: _adminTheme,
      child: Scaffold(
        key: _scaffoldKey,
        backgroundColor: const Color(0xFFF4F6FA),
        drawer: isDesktop ? null : _buildDrawer(),
        body: Row(
          children: [
            // Desktop Fixed Left Sidebar (matches Arzana)
            if (isDesktop)
              SizedBox(
                width: 250,
                child: _buildSidebarContent(),
              ),

            // Main Content
            Expanded(
              child: Column(
                children: [
                  _buildTopBar(isDesktop),
                  Expanded(
                    child: _buildMainView(),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ─── TOPBAR ─────────────────────────────────────────────────────────────────
  Widget _buildTopBar(bool isDesktop) {
    return Container(
      height: 60,
      padding: const EdgeInsets.symmetric(horizontal: 24),
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(
          bottom: BorderSide(color: Color(0xFFE2E8F0), width: 1),
        ),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              if (!isDesktop) ...[
                IconButton(
                  icon: const Icon(Icons.menu, color: Color(0xFF1E293B)),
                  onPressed: () => _scaffoldKey.currentState?.openDrawer(),
                ),
                const SizedBox(width: 8),
              ],
              Container(
                width: 32,
                height: 32,
                decoration: const BoxDecoration(
                  color: Color(0xFFF0F9FF),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.person, color: Color(0xFF008CFF), size: 16),
              ),
              const SizedBox(width: 10),
              Text(
                'Selamat datang, $_userName',
                style: const TextStyle(
                  color: Color(0xFF1E293B),
                  fontWeight: FontWeight.w600,
                  fontSize: 14,
                ),
              ),
            ],
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
            decoration: BoxDecoration(
              color: const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Text(
              'Admin',
              style: TextStyle(
                color: Color(0xFF64748B),
                fontWeight: FontWeight.w600,
                fontSize: 12,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ─── SIDEBAR (ARZANA DESIGN) ────────────────────────────────────────────────
  Widget _buildDrawer() {
    return Drawer(
      backgroundColor: const Color(0xFF0B132B),
      child: _buildSidebarContent(),
    );
  }

  Widget _buildSidebarContent() {
    return Container(
      color: const Color(0xFF0B132B),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Logo & Title
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 24, 20, 20),
            child: Row(
              children: [
                Container(
                  width: 36,
                  height: 36,
                  decoration: BoxDecoration(
                    color: const Color(0xFF008CFF),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.storefront, color: Colors.white, size: 20),
                ),
                const SizedBox(width: 12),
                const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'WARKOP RUMA',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 15,
                        letterSpacing: 0.5,
                      ),
                    ),
                    Text(
                      'Admin Panel',
                      style: TextStyle(
                        color: Color(0xFF94A3B8),
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const Divider(color: Color(0xFF1E293B), height: 1),

          // Menu Items
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 12),
              children: [
                // UTAMA
                _buildSectionHeader('UTAMA'),
                _buildNavItem(0, Icons.speed_outlined, 'Dashboard'),

                const SizedBox(height: 16),

                // MANAJEMEN
                _buildSectionHeader('MANAJEMEN'),
                _buildNavItem(1, Icons.label_outline, 'Kelola Kategori'),
                _buildNavItem(2, Icons.restaurant_menu_outlined, 'Kelola Menu'),
                _buildNavItem(3, Icons.inventory_2_outlined, 'Kelola Stok Barang'),
                _buildNavItem(4, Icons.table_restaurant_outlined, 'Meja & QR Code'),
                _buildNavItem(5, Icons.people_outline, 'Manajemen Kasir'),

                const SizedBox(height: 16),

                // TRANSAKSI
                _buildSectionHeader('TRANSAKSI'),
                _buildNavItem(6, Icons.history, 'Riwayat Transaksi'),
              ],
            ),
          ),

          // Logout Button
          Padding(
            padding: const EdgeInsets.all(16),
            child: InkWell(
              onTap: _logout,
              borderRadius: BorderRadius.circular(10),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 14),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(10),
                  color: const Color(0x1AF43F5E),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.logout, color: Color(0xFFF43F5E), size: 18),
                    SizedBox(width: 12),
                    Text(
                      'Keluar',
                      style: TextStyle(
                        color: Color(0xFFF43F5E),
                        fontWeight: FontWeight.w600,
                        fontSize: 13,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 4, 12, 8),
      child: Text(
        title,
        style: const TextStyle(
          color: Color(0xFF64748B),
          fontSize: 10,
          fontWeight: FontWeight.bold,
          letterSpacing: 1.2,
        ),
      ),
    );
  }

  Widget _buildNavItem(int index, IconData icon, String label) {
    final active = _selectedNavIndex == index;

    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: InkWell(
        onTap: () {
          if (_scaffoldKey.currentState?.isDrawerOpen ?? false) {
            Navigator.pop(context);
          }
          setState(() => _selectedNavIndex = index);
        },
        borderRadius: BorderRadius.circular(8),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
          decoration: BoxDecoration(
            color: active ? const Color(0xFF152243) : Colors.transparent,
            borderRadius: BorderRadius.circular(8),
            border: active
                ? const Border(left: BorderSide(color: Color(0xFF008CFF), width: 3))
                : null,
          ),
          child: Row(
            children: [
              Icon(
                icon,
                color: active ? const Color(0xFF008CFF) : const Color(0xFF94A3B8),
                size: 18,
              ),
              const SizedBox(width: 12),
              Text(
                label,
                style: TextStyle(
                  color: active ? Colors.white : const Color(0xFF94A3B8),
                  fontWeight: active ? FontWeight.w600 : FontWeight.normal,
                  fontSize: 13,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ─── MAIN WORKSPACE SWITCHER ────────────────────────────────────────────────
  Widget _buildMainView() {
    switch (_selectedNavIndex) {
      case 0:
        return _buildRUMADashboard();
      case 1:
        return const KategoriScreen();
      case 2:
        return const MenuScreen();
      case 3:
        return const StokScreen();
      case 4:
        return const MejaScreen();
      case 5:
        return const UsersScreen();
      case 6:
        return const AdminRiwayatScreen();
      default:
        return _buildRUMADashboard();
    }
  }

  // ─── ARZANA DASHBOARD (SCREENSHOT 1) ─────────────────────────────────────────
  Widget _buildRUMADashboard() {
    return RefreshIndicator(
      onRefresh: _fetchDashboardData,
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Page Header
            const Text(
              'Dashboard Admin',
              style: TextStyle(
                color: Color(0xFF1E293B),
                fontSize: 22,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 4),
            const Text(
              'Ringkasan operasional sistem hari ini.',
              style: TextStyle(
                color: Color(0xFF64748B),
                fontSize: 13,
              ),
            ),
            const SizedBox(height: 24),

            // 4 Stat Cards
            LayoutBuilder(
              builder: (ctx, constraints) {
                final isWide = constraints.maxWidth >= 720;
                final cardWidth = isWide
                    ? (constraints.maxWidth - 48) / 4
                    : (constraints.maxWidth - 16) / 2;

                return Wrap(
                  spacing: 16,
                  runSpacing: 16,
                  children: [
                    _buildStatCard(
                      title: 'PESANAN HARI INI',
                      value: '$_totalPesanan',
                      subtitle: 'Total transaksi masuk',
                      icon: Icons.receipt_long,
                      iconBg: const Color(0xFFF0F9FF),
                      iconColor: const Color(0xFF008CFF),
                      width: cardWidth,
                    ),
                    _buildStatCard(
                      title: 'PENDAPATAN (HARI INI)',
                      value: _formatCurrency(_totalPendapatan),
                      subtitle: 'Hanya transaksi selesai',
                      icon: Icons.savings_outlined,
                      iconBg: const Color(0xFFF0FDF4),
                      iconColor: const Color(0xFF16A34A),
                      width: cardWidth,
                    ),
                    _buildStatCard(
                      title: 'MENU AKTIF',
                      value: '$_totalMenu',
                      subtitle: 'Item menu tersedia',
                      icon: Icons.restaurant,
                      iconBg: const Color(0xFFFFFBEB),
                      iconColor: const Color(0xFFD97706),
                      width: cardWidth,
                    ),
                    _buildStatCard(
                      title: 'TOTAL MEJA',
                      value: '$_totalMeja',
                      subtitle: 'Meja terdaftar',
                      icon: Icons.table_restaurant,
                      iconBg: const Color(0xFFFAF5FF),
                      iconColor: const Color(0xFF7C3AED),
                      width: cardWidth,
                    ),
                  ],
                );
              },
            ),

            const SizedBox(height: 24),

            // Two Columns: Pesanan Terbaru & Akses Cepat
            LayoutBuilder(
              builder: (ctx, constraints) {
                final isWide = constraints.maxWidth >= 850;

                if (isWide) {
                  return Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        flex: 3,
                        child: _buildRecentOrdersCard(),
                      ),
                      const SizedBox(width: 24),
                      Expanded(
                        flex: 2,
                        child: _buildQuickAccessCard(),
                      ),
                    ],
                  );
                }

                return Column(
                  children: [
                    _buildRecentOrdersCard(),
                    const SizedBox(height: 20),
                    _buildQuickAccessCard(),
                  ],
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard({
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color iconBg,
    required Color iconColor,
    required double width,
  }) {
    return Container(
      width: width,
      padding: const EdgeInsets.all(18),
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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                title,
                style: const TextStyle(
                  color: Color(0xFF94A3B8),
                  fontSize: 10.5,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 0.5,
                ),
              ),
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: iconBg,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, color: iconColor, size: 16),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            value,
            style: const TextStyle(
              color: Color(0xFF1E293B),
              fontSize: 22,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: const TextStyle(
              color: Color(0xFF94A3B8),
              fontSize: 11,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRecentOrdersCard() {
    return Container(
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
      child: Column(
        children: [
          // Header
          Padding(
            padding: const EdgeInsets.all(18),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Pesanan Terbaru',
                  style: TextStyle(
                    color: Color(0xFF1E293B),
                    fontWeight: FontWeight.bold,
                    fontSize: 15,
                  ),
                ),
                InkWell(
                  onTap: () => setState(() => _selectedNavIndex = 6),
                  child: const Row(
                    children: [
                      Text(
                        'Lihat semua',
                        style: TextStyle(
                          color: Color(0xFF008CFF),
                          fontWeight: FontWeight.w600,
                          fontSize: 12,
                        ),
                      ),
                      SizedBox(width: 4),
                      Icon(Icons.arrow_forward, color: Color(0xFF008CFF), size: 14),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const Divider(color: Color(0xFFF1F5F9), height: 1),

          // List
          if (_loading)
            const Padding(
              padding: EdgeInsets.all(40),
              child: Center(child: CircularProgressIndicator(color: Color(0xFF008CFF))),
            )
          else if (_recentOrders.isEmpty)
            const Padding(
              padding: EdgeInsets.all(40),
              child: Text(
                'Belum ada transaksi terbaru.',
                style: TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
              ),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: _recentOrders.length > 5 ? 5 : _recentOrders.length,
              separatorBuilder: (_, __) => const Divider(color: Color(0xFFF8FAFC), height: 1),
              itemBuilder: (ctx, i) {
                final order = _recentOrders[i];
                final nomorMeja = (order['nomor_meja'] ?? '-').toString();
                final kode = (order['kode_pesanan'] ?? '-').toString();
                final nama = (order['nama_pemesan'] ?? 'Pelanggan').toString();
                final total = order['total_harga'] ?? 0;
                final createdAt = order['created_at']?.toString() ?? '';
                final jam = createdAt.length >= 16 ? createdAt.substring(11, 16) : '';

                return Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
                  child: Row(
                    children: [
                      Container(
                        width: 36,
                        height: 36,
                        decoration: BoxDecoration(
                          color: const Color(0xFFF0F9FF),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(Icons.receipt_outlined, color: Color(0xFF008CFF), size: 18),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Text(
                                  'Meja NO $nomorMeja',
                                  style: const TextStyle(
                                    color: Color(0xFF1E293B),
                                    fontWeight: FontWeight.bold,
                                    fontSize: 13,
                                  ),
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  '#$kode',
                                  style: const TextStyle(
                                    color: Color(0xFF94A3B8),
                                    fontFamily: 'monospace',
                                    fontSize: 11,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 3),
                            Row(
                              children: [
                                const Icon(Icons.person_outline, size: 12, color: Color(0xFF94A3B8)),
                                const SizedBox(width: 4),
                                Text(
                                  '$nama  $jam',
                                  style: const TextStyle(
                                    color: Color(0xFF94A3B8),
                                    fontSize: 11,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            _formatCurrency(total),
                            style: const TextStyle(
                              color: Color(0xFF1E293B),
                              fontWeight: FontWeight.bold,
                              fontSize: 13,
                            ),
                          ),
                          const SizedBox(height: 3),
                          const Text(
                            'SELESAI',
                            style: TextStyle(
                              color: Color(0xFF16A34A),
                              fontWeight: FontWeight.bold,
                              fontSize: 10.5,
                              letterSpacing: 0.5,
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
    );
  }

  Widget _buildQuickAccessCard() {
    return Container(
      padding: const EdgeInsets.all(18),
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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Akses Cepat',
            style: TextStyle(
              color: Color(0xFF1E293B),
              fontWeight: FontWeight.bold,
              fontSize: 15,
            ),
          ),
          const SizedBox(height: 16),
          _buildQuickAccessItem(
            icon: Icons.restaurant,
            iconColor: const Color(0xFFD97706),
            iconBg: const Color(0xFFFFFBEB),
            title: 'Tambah Menu Baru',
            onTap: () => setState(() => _selectedNavIndex = 2),
          ),
          _buildQuickAccessItem(
            icon: Icons.inventory_2_outlined,
            iconColor: const Color(0xFF0284C7),
            iconBg: const Color(0xFFF0F9FF),
            title: 'Kelola Stok Barang',
            onTap: () => setState(() => _selectedNavIndex = 3),
          ),
          _buildQuickAccessItem(
            icon: Icons.table_restaurant,
            iconColor: const Color(0xFF7C3AED),
            iconBg: const Color(0xFFFAF5FF),
            title: 'Kelola QR Meja',
            onTap: () => setState(() => _selectedNavIndex = 4),
          ),
          _buildQuickAccessItem(
            icon: Icons.person_add_alt,
            iconColor: const Color(0xFF008CFF),
            iconBg: const Color(0xFFF0F9FF),
            title: 'Tambah Akun Kasir',
            onTap: () => setState(() => _selectedNavIndex = 5),
          ),
          _buildQuickAccessItem(
            icon: Icons.description_outlined,
            iconColor: const Color(0xFF16A34A),
            iconBg: const Color(0xFFF0FDF4),
            title: 'Riwayat Transaksi',
            onTap: () => setState(() => _selectedNavIndex = 6),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickAccessItem({
    required IconData icon,
    required Color iconColor,
    required Color iconBg,
    required String title,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
        child: Row(
          children: [
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: iconBg,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: iconColor, size: 16),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                title,
                style: const TextStyle(
                  color: Color(0xFF334155),
                  fontWeight: FontWeight.w500,
                  fontSize: 13,
                ),
              ),
            ),
            const Icon(Icons.chevron_right, color: Color(0xFFCBD5E1), size: 18),
          ],
        ),
      ),
    );
  }
}
