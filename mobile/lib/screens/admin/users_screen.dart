import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class UsersScreen extends StatefulWidget {
  const UsersScreen({super.key});

  @override
  State<UsersScreen> createState() => _UsersScreenState();
}

class _UsersScreenState extends State<UsersScreen> {
  List<dynamic> _users = [];
  bool _loading = false;
  final TextEditingController _namaController = TextEditingController();
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _passController = TextEditingController();
  String _selectedRole = 'kasir';
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();
  bool _saving = false;
  String? _successBanner;

  @override
  void initState() {
    super.initState();
    _fetchUsers();
  }

  @override
  void dispose() {
    _namaController.dispose();
    _usernameController.dispose();
    _passController.dispose();
    super.dispose();
  }

  Future<void> _fetchUsers() async {
    setState(() => _loading = true);
    final res = await ApiService.getUsers();
    if (mounted) {
      setState(() {
        _loading = false;
        if (res['success'] == true) {
          _users = res['users'] ?? [];
        }
      });
    }
  }

  Future<void> _storeUser() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final res = await ApiService.storeUser({
      'nama_lengkap': _namaController.text.trim(),
      'username': _usernameController.text.trim(),
      'password': _passController.text.trim(),
      'role': _selectedRole,
    });

    setState(() => _saving = false);

    if (res['success'] == true) {
      _namaController.clear();
      _usernameController.clear();
      _passController.clear();
      setState(() => _successBanner = 'Akun berhasil ditambahkan.');
      _fetchUsers();
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Gagal menambahkan akun.')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          const Text(
            'Manajemen Kasir & Staf',
            style: TextStyle(
              color: Color(0xFF1E293B),
              fontSize: 22,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            'Kelola akun pengguna yang dapat masuk ke sistem POS/Kasir.',
            style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
          ),
          const SizedBox(height: 20),

          // Banner
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
                  Text(_successBanner!, style: const TextStyle(color: Color(0xFF16A34A), fontSize: 13, fontWeight: FontWeight.w500)),
                ],
              ),
            ),
            const SizedBox(height: 16),
          ],

          // 2 Columns Layout
          LayoutBuilder(
            builder: (ctx, constraints) {
              final isWide = constraints.maxWidth >= 820;

              if (isWide) {
                return Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SizedBox(width: 320, child: _buildAddUserCard()),
                    const SizedBox(width: 24),
                    Expanded(child: _buildUsersTableCard()),
                  ],
                );
              }

              return Column(
                children: [
                  _buildAddUserCard(),
                  const SizedBox(height: 20),
                  _buildUsersTableCard(),
                ],
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildAddUserCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFF1F5F9)),
        boxShadow: const [
          BoxShadow(color: Color(0x08000000), blurRadius: 10, offset: Offset(0, 2)),
        ],
      ),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Tambah Akun Baru',
              style: TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 15),
            ),
            const SizedBox(height: 16),

            const Text('NAMA LENGKAP *', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
            const SizedBox(height: 6),
            TextFormField(
              controller: _namaController,
              style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
              decoration: InputDecoration(
                hintText: 'Contoh: Kasir Utama',
                hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
              ),
              validator: (v) => (v == null || v.trim().isEmpty) ? 'Nama wajib diisi' : null,
            ),
            const SizedBox(height: 14),

            const Text('USERNAME *', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
            const SizedBox(height: 6),
            TextFormField(
              controller: _usernameController,
              style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
              decoration: InputDecoration(
                hintText: 'Contoh: kasir01',
                hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
              ),
              validator: (v) => (v == null || v.trim().isEmpty) ? 'Username wajib diisi' : null,
            ),
            const SizedBox(height: 14),

            const Text('PASSWORD *', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
            const SizedBox(height: 6),
            TextFormField(
              controller: _passController,
              obscureText: true,
              style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
              decoration: InputDecoration(
                hintText: 'Minimal 6 karakter',
                hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 12),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFCBD5E1))),
                focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFF008CFF))),
              ),
              validator: (v) => (v == null || v.trim().length < 6) ? 'Minimal 6 karakter' : null,
            ),
            const SizedBox(height: 14),

            const Text('ROLE AKSES *', style: TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.bold)),
            const SizedBox(height: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFCBD5E1)),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<String>(
                  value: _selectedRole,
                  isExpanded: true,
                  style: const TextStyle(color: Color(0xFF1E293B), fontSize: 13),
                  items: const [
                    DropdownMenuItem(value: 'kasir', child: Text('Kasir')),
                    DropdownMenuItem(value: 'admin', child: Text('Admin')),
                  ],
                  onChanged: (val) {
                    if (val != null) setState(() => _selectedRole = val);
                  },
                ),
              ),
            ),
            const SizedBox(height: 20),

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
                label: Text(_saving ? 'Menyimpan...' : '+ Tambah Akun', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                onPressed: _saving ? null : _storeUser,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildUsersTableCard() {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFF1F5F9)),
        boxShadow: const [
          BoxShadow(color: Color(0x08000000), blurRadius: 10, offset: Offset(0, 2)),
        ],
      ),
      child: _loading
          ? const Padding(
              padding: EdgeInsets.all(40),
              child: Center(child: CircularProgressIndicator(color: Color(0xFF008CFF))),
            )
          : _users.isEmpty
              ? const Padding(
                  padding: EdgeInsets.all(40),
                  child: Center(child: Text('Belum ada akun.', style: TextStyle(color: Color(0xFF94A3B8)))),
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
                        DataColumn(label: Text('PENGGUNA')),
                        DataColumn(label: Text('USERNAME')),
                        DataColumn(label: Text('ROLE')),
                        DataColumn(label: Text('AKSI')),
                      ],
                      rows: List.generate(_users.length, (i) {
                        final u = _users[i];
                        final id = u['id'];
                        final nama = (u['nama_lengkap'] ?? '-').toString();
                        final username = (u['username'] ?? '-').toString();
                        final role = (u['role'] ?? 'kasir').toString();

                        return DataRow(
                          cells: [
                            DataCell(
                              Row(
                                children: [
                                  Container(
                                    width: 28,
                                    height: 28,
                                    decoration: const BoxDecoration(
                                      color: Color(0xFFF1F5F9),
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(Icons.person, size: 15, color: Color(0xFF64748B)),
                                  ),
                                  const SizedBox(width: 10),
                                  Text(nama, style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 13)),
                                ],
                              ),
                            ),
                            DataCell(Text(username, style: const TextStyle(color: Color(0xFF64748B), fontFamily: 'monospace', fontSize: 12))),
                            DataCell(
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: role == 'admin' ? const Color(0xFFFAF5FF) : const Color(0xFFF0F9FF),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  role.toUpperCase(),
                                  style: TextStyle(
                                    color: role == 'admin' ? const Color(0xFF7C3AED) : const Color(0xFF008CFF),
                                    fontSize: 11,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ),
                            DataCell(
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
                                      title: const Text('Hapus Akun'),
                                      content: Text('Yakin ingin menghapus "$nama"?'),
                                      actions: [
                                        TextButton(onPressed: () => Navigator.pop(c, false), child: const Text('Batal')),
                                        TextButton(onPressed: () => Navigator.pop(c, true), child: const Text('Hapus', style: TextStyle(color: Colors.red))),
                                      ],
                                    ),
                                  );
                                  if (confirm == true) {
                                    await ApiService.deleteUser(id);
                                    _fetchUsers();
                                  }
                                },
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
