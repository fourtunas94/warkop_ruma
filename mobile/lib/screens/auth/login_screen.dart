import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../services/api_service.dart';
import '../../widgets/app_widgets.dart' hide AppColors;
import '../kasir/kasir_colors.dart';
import '../admin/admin_dashboard_screen.dart';
import '../kasir/kasir_pos_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> with SingleTickerProviderStateMixin {
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _loading = false;
  bool _obscurePassword = true;
  String? _errorMsg;
  String _selectedRole = 'kasir'; // 'kasir' or 'admin'

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (_usernameController.text.trim().isEmpty || _passwordController.text.isEmpty) {
      setState(() => _errorMsg = 'Username dan password harus diisi.');
      return;
    }
    setState(() {
      _loading = true;
      _errorMsg = null;
    });
    final result = await ApiService.login(
      _usernameController.text.trim(),
      _passwordController.text,
    );
    setState(() => _loading = false);

    if (result['success'] == true) {
      final prefs = await SharedPreferences.getInstance();
      final role = prefs.getString('user_role') ?? '';
      if (!mounted) return;
      if (role == 'admin') {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const AdminDashboardScreen()),
        );
      } else {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const KasirPosScreen()),
        );
      }
    } else {
      setState(() => _errorMsg = result['message'] ?? 'Login gagal.');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Theme(
      data: AppColors.lightTheme,
      child: Scaffold(
        backgroundColor: AppColors.background,
        body: Stack(
          children: [
            // Ambient glow effects (Soft Sky Blue)
            Positioned(
              top: -100,
              left: MediaQuery.of(context).size.width / 2 - 192,
              child: Container(
                width: 384,
                height: 384,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(
                    colors: [
                      AppColors.primaryContainer.withOpacity(0.14),
                      Colors.transparent,
                    ],
                  ),
                ),
              ),
            ),
            Positioned(
              bottom: 40,
              right: 0,
              child: Container(
                width: 320,
                height: 320,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(
                    colors: [
                      AppColors.secondaryContainer.withOpacity(0.5),
                      Colors.transparent,
                    ],
                  ),
                ),
              ),
            ),

            SafeArea(
              child: Center(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 440),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        // Brand Badge Logo
                        Container(
                          width: 96,
                          height: 96,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: AppColors.surfaceContainerLowest,
                            border: Border.all(
                              color: AppColors.primary.withOpacity(0.2),
                              width: 1.5,
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: AppColors.primary.withOpacity(0.12),
                                blurRadius: 24,
                                offset: const Offset(0, 8),
                              ),
                            ],
                          ),
                          child: Container(
                            margin: const EdgeInsets.all(5),
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: AppColors.secondaryContainer.withOpacity(0.45),
                            ),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Container(
                                  width: 40,
                                  height: 40,
                                  decoration: const BoxDecoration(
                                    shape: BoxShape.circle,
                                    color: AppColors.primaryContainer,
                                  ),
                                  child: const Icon(Icons.storefront_rounded, color: Colors.white, size: 22),
                                ),
                                const SizedBox(height: 4),
                                const Text(
                                  'WARKOP RUMA',
                                  style: TextStyle(
                                    color: AppColors.primary,
                                    fontSize: 6.5,
                                    fontWeight: FontWeight.bold,
                                    letterSpacing: 1.5,
                                    fontFamily: 'SpaceGrotesk',
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 14),

                        // Roastery Provenance Chip


                        // Heading
                        const Text(
                          'Selamat Datang Kembali',
                          style: TextStyle(
                            color: AppColors.onSurface,
                            fontSize: 26,
                            fontWeight: FontWeight.w700,
                            fontFamily: 'Epilogue',
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'Sistem Kasir & Operasional Terpadu\n Warkop Ruma Sidikalang Asli',
                          style: TextStyle(
                            color: AppColors.onSurfaceVariant,
                            fontSize: 13,
                            fontFamily: 'PlusJakartaSans',
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 12),

                        // Portal Status Pill
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: AppColors.surfaceContainerLowest,
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: AppColors.outlineVariant.withOpacity(0.6)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Container(
                                width: 8,
                                height: 8,
                                decoration: const BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: AppColors.green,
                                ),
                              ),
                              const SizedBox(width: 8),
                              const Text(
                                'Portal Staf & Pengelola Kedai',
                                style: TextStyle(
                                  color: AppColors.onSurface,
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w600,
                                  letterSpacing: 0.5,
                                  fontFamily: 'SpaceGrotesk',
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Role Selector Switcher
                        Container(
                          padding: const EdgeInsets.all(6),
                          decoration: BoxDecoration(
                            color: AppColors.surfaceContainerLowest,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: AppColors.outlineVariant.withOpacity(0.7)),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.04),
                                blurRadius: 10,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Row(
                            children: [
                              Expanded(
                                child: _buildRoleTab(
                                  icon: Icons.point_of_sale,
                                  label: 'Kasir',
                                  subtitle: 'POS & Pesanan',
                                  isSelected: _selectedRole == 'kasir',
                                  onTap: () => setState(() => _selectedRole = 'kasir'),
                                ),
                              ),
                              const SizedBox(width: 6),
                              Expanded(
                                child: _buildRoleTab(
                                  icon: Icons.admin_panel_settings,
                                  label: 'Admin',
                                  subtitle: 'Manajemen Toko',
                                  isSelected: _selectedRole == 'admin',
                                  onTap: () => setState(() => _selectedRole = 'admin'),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Login Form Card
                        Container(
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            color: AppColors.surfaceContainerLowest,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: AppColors.outlineVariant.withOpacity(0.7)),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.06),
                                blurRadius: 20,
                                offset: const Offset(0, 6),
                              ),
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              // Shift Greeting
                              Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: AppColors.surfaceContainerLow,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: AppColors.outlineVariant.withOpacity(0.5)),
                                ),
                                child: Row(
                                  children: [
                                    Container(
                                      width: 34,
                                      height: 34,
                                      decoration: const BoxDecoration(
                                        shape: BoxShape.circle,
                                        color: AppColors.secondaryContainer,
                                      ),
                                      child: const Icon(Icons.coffee, color: AppColors.primary, size: 18),
                                    ),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            _selectedRole == 'kasir' ? 'Mode Kasir Aktif' : 'Mode Administrator Aktif',
                                            style: const TextStyle(
                                              color: AppColors.onSurface,
                                              fontSize: 12,
                                              fontWeight: FontWeight.w700,
                                              fontFamily: 'SpaceGrotesk',
                                              letterSpacing: 0.3,
                                            ),
                                          ),
                                          const Text(
                                            'Shift Aktif • Meja Bar 01',
                                            style: TextStyle(
                                              color: AppColors.onSurfaceVariant,
                                              fontSize: 11,
                                              fontFamily: 'PlusJakartaSans',
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                      decoration: BoxDecoration(
                                        color: AppColors.green.withOpacity(0.12),
                                        borderRadius: BorderRadius.circular(20),
                                      ),
                                      child: const Text(
                                        'ONLINE',
                                        style: TextStyle(
                                          color: AppColors.green,
                                          fontSize: 9,
                                          fontWeight: FontWeight.bold,
                                          letterSpacing: 1.2,
                                          fontFamily: 'SpaceGrotesk',
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 16),

                              // Error message
                              if (_errorMsg != null) ...[
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                  margin: const EdgeInsets.only(bottom: 16),
                                  decoration: BoxDecoration(
                                    color: AppColors.errorContainer,
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(color: AppColors.red.withOpacity(0.3)),
                                  ),
                                  child: Row(
                                    children: [
                                      const Icon(Icons.error_outline, color: AppColors.red, size: 16),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Text(
                                          _errorMsg!,
                                          style: const TextStyle(color: AppColors.red, fontSize: 13),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],

                              // Username Label
                              const Text(
                                'ID KARYAWAN / EMAIL',
                                style: TextStyle(
                                  color: AppColors.onSurfaceVariant,
                                  fontSize: 10,
                                  fontWeight: FontWeight.w700,
                                  letterSpacing: 1.1,
                                  fontFamily: 'SpaceGrotesk',
                                ),
                              ),
                              const SizedBox(height: 6),
                              TextField(
                                controller: _usernameController,
                                style: const TextStyle(color: AppColors.onSurface, fontSize: 14),
                                textInputAction: TextInputAction.next,
                                decoration: InputDecoration(
                                  hintText: _selectedRole == 'kasir'
                                      ? 'kasir@warkopruma.id atau ID Staf'
                                      : 'admin@warkopruma.id',
                                  hintStyle: const TextStyle(color: AppColors.outline, fontSize: 13),
                                  prefixIcon: const Icon(Icons.badge_outlined, color: AppColors.onSurfaceVariant, size: 20),
                                  filled: true,
                                  fillColor: AppColors.surfaceContainerLow,
                                  enabledBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: BorderSide(color: AppColors.outlineVariant.withOpacity(0.8)),
                                  ),
                                  focusedBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
                                  ),
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                                ),
                              ),
                              const SizedBox(height: 14),

                              // Password Label
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  const Text(
                                    'KATA SANDI',
                                    style: TextStyle(
                                      color: AppColors.onSurfaceVariant,
                                      fontSize: 10,
                                      fontWeight: FontWeight.w700,
                                      letterSpacing: 1.1,
                                      fontFamily: 'SpaceGrotesk',
                                    ),
                                  ),
                                  GestureDetector(
                                    onTap: () {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(content: Text('Silakan hubungi Store Manager untuk reset kredensial shift Anda.')),
                                      );
                                    },
                                    child: const Text(
                                      'Lupa Password?',
                                      style: TextStyle(
                                        color: AppColors.primary,
                                        fontSize: 10.5,
                                        fontWeight: FontWeight.w600,
                                        fontFamily: 'SpaceGrotesk',
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 6),
                              TextField(
                                controller: _passwordController,
                                obscureText: _obscurePassword,
                                style: const TextStyle(color: AppColors.onSurface, fontSize: 14),
                                textInputAction: TextInputAction.done,
                                onSubmitted: (_) => _login(),
                                decoration: InputDecoration(
                                  hintText: '••••••••••••',
                                  hintStyle: const TextStyle(color: AppColors.outline, fontSize: 13),
                                  prefixIcon: const Icon(Icons.lock_outline, color: AppColors.onSurfaceVariant, size: 20),
                                  suffixIcon: IconButton(
                                    icon: Icon(
                                      _obscurePassword ? Icons.visibility_off : Icons.visibility,
                                      color: AppColors.onSurfaceVariant,
                                      size: 20,
                                    ),
                                    onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                                  ),
                                  filled: true,
                                  fillColor: AppColors.surfaceContainerLow,
                                  enabledBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: BorderSide(color: AppColors.outlineVariant.withOpacity(0.8)),
                                  ),
                                  focusedBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
                                  ),
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                                ),
                              ),
                              const SizedBox(height: 20),

                              // Login Button
                              SizedBox(
                                height: 50,
                                child: ElevatedButton(
                                  onPressed: _loading ? null : _login,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: AppColors.primary,
                                    foregroundColor: AppColors.onPrimary,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    elevation: 1,
                                  ),
                                  child: _loading
                                      ? const SizedBox(
                                          height: 20,
                                          width: 20,
                                          child: CircularProgressIndicator(
                                            strokeWidth: 2,
                                            color: AppColors.onPrimary,
                                          ),
                                        )
                                      : Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Text(
                                              _selectedRole == 'kasir' ? 'Masuk ke Sistem POS' : 'Buka Konsol Pengelola',
                                              style: const TextStyle(
                                                fontWeight: FontWeight.bold,
                                                fontSize: 14,
                                                letterSpacing: 0.4,
                                                fontFamily: 'SpaceGrotesk',
                                              ),
                                            ),
                                            const SizedBox(width: 8),
                                            const Icon(Icons.arrow_forward, size: 20),
                                          ],
                                        ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 24),

                        // Footer
                        Column(
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                const Text(
                                  'v1.0.4-PROD',
                                  style: TextStyle(
                                    color: AppColors.onSurfaceVariant,
                                    fontSize: 10,
                                    letterSpacing: 1,
                                    fontFamily: 'SpaceGrotesk',
                                  ),
                                ),
                                const SizedBox(width: 8),
                                const Text('•', style: TextStyle(color: AppColors.outline)),
                                const SizedBox(width: 8),
                                Row(
                                  children: [
                                    Container(
                                      width: 6,
                                      height: 6,
                                      decoration: const BoxDecoration(
                                        shape: BoxShape.circle,
                                        color: AppColors.green,
                                      ),
                                    ),
                                    const SizedBox(width: 4),
                                    const Text(
                                      'Terminal Terkoneksi',
                                      style: TextStyle(
                                        color: AppColors.green,
                                        fontSize: 10,
                                        fontWeight: FontWeight.w600,
                                        fontFamily: 'SpaceGrotesk',
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                            
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRoleTab({
    required IconData icon,
    required String label,
    required String subtitle,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 250),
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.primary : Colors.transparent,
          borderRadius: BorderRadius.circular(8),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: AppColors.primary.withOpacity(0.25),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 18, color: isSelected ? AppColors.onPrimary : AppColors.onSurfaceVariant),
            const SizedBox(width: 8),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    color: isSelected ? AppColors.onPrimary : AppColors.onSurface,
                    fontSize: 13.5,
                    fontWeight: FontWeight.bold,
                    fontFamily: 'SpaceGrotesk',
                  ),
                ),
                Text(
                  subtitle,
                  style: TextStyle(
                    color: isSelected ? AppColors.onPrimary.withOpacity(0.85) : AppColors.onSurfaceVariant,
                    fontSize: 9.5,
                    fontFamily: 'SpaceGrotesk',
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}