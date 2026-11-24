import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';

class ApiService {
  static String? _token;
  static bool _tokenLoaded = false;
  static final http.Client _client = http.Client();
  static const Duration _timeout = Duration(seconds: 15);

  // ─── Auth ─────────────────────────────────────────────────────────────────

  static Future<void> loadToken() async {
    if (_tokenLoaded && _token != null) return;
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    _tokenLoaded = true;
  }

  static Future<void> saveToken(String token) async {
    _token = token;
    _tokenLoaded = true;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }

  static Future<void> clearToken() async {
    _token = null;
    _tokenLoaded = false;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('user_role');
    await prefs.remove('user_name');
    await prefs.remove('user_id');
  }

  static Future<bool> isLoggedIn() async {
    await loadToken();
    return _token != null && _token!.isNotEmpty;
  }

  // ─── HTTP Helpers ──────────────────────────────────────────────────────────

  static Map<String, String> get _headers => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };

  static Future<Map<String, dynamic>> get(String endpoint) async {
    await loadToken();
    final uri = Uri.parse('${ApiConfig.apiBaseUrl}$endpoint');
    final response = await _client.get(uri, headers: _headers).timeout(_timeout);
    return _handleResponse(response);
  }

  static Future<Map<String, dynamic>> post(
    String endpoint,
    Map<String, dynamic> body,
  ) async {
    await loadToken();
    final uri = Uri.parse('${ApiConfig.apiBaseUrl}$endpoint');
    final response = await _client
        .post(uri, headers: _headers, body: jsonEncode(body))
        .timeout(_timeout);
    return _handleResponse(response);
  }

  static Future<Map<String, dynamic>> put(
    String endpoint,
    Map<String, dynamic> body,
  ) async {
    await loadToken();
    final uri = Uri.parse('${ApiConfig.apiBaseUrl}$endpoint');
    final response = await _client
        .put(uri, headers: _headers, body: jsonEncode(body))
        .timeout(_timeout);
    return _handleResponse(response);
  }

  static Future<Map<String, dynamic>> delete(String endpoint) async {
    await loadToken();
    final uri = Uri.parse('${ApiConfig.apiBaseUrl}$endpoint');
    final response = await _client.delete(uri, headers: _headers).timeout(_timeout);
    return _handleResponse(response);
  }

  /// Multipart POST/PUT for file uploads
  static Future<Map<String, dynamic>> postMultipart(
    String endpoint,
    Map<String, String> fields, {
    File? imageFile,
    List<int>? imageBytes,
    String? filename,
    String imageField = 'gambar',
    String method = 'POST',
  }) async {
    await loadToken();
    final uri = Uri.parse('${ApiConfig.apiBaseUrl}$endpoint');
    final request = http.MultipartRequest(method, uri);

    request.headers.addAll({
      'Accept': 'application/json',
      if (_token != null) 'Authorization': 'Bearer $_token',
    });

    fields.forEach((key, value) => request.fields[key] = value);

    if (imageBytes != null && filename != null) {
      request.files.add(
        http.MultipartFile.fromBytes(imageField, imageBytes, filename: filename),
      );
    } else if (imageFile != null) {
      request.files.add(
        await http.MultipartFile.fromPath(imageField, imageFile.path),
      );
    }

    final streamedResponse = await request.send();
    final response = await http.Response.fromStream(streamedResponse);
    return _handleResponse(response);
  }

  static Map<String, dynamic> _handleResponse(http.Response response) {
    try {
      final data = jsonDecode(response.body) as Map<String, dynamic>;
      if (response.statusCode >= 400) {
        return {
          'success': false,
          'message': data['message'] ?? 'Terjadi kesalahan (${response.statusCode})',
          ...data,
        };
      }
      return data;
    } catch (e) {
      return {
        'success': false,
        'message': 'Gagal memproses respons server.',
      };
    }
  }

  // ─── Auth API ──────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> login(
    String username,
    String password,
  ) async {
    final result = await post('/auth/login', {
      'username': username,
      'password': password,
    });

    if (result['success'] == true) {
      final token = result['token'] as String?;
      final user = result['user'] as Map<String, dynamic>?;

      if (token != null) {
        await saveToken(token);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('user_role', user?['role'] ?? '');
        await prefs.setString('user_name', user?['nama_lengkap'] ?? '');
        await prefs.setInt('user_id', user?['id'] ?? 0);
      }
    }

    return result;
  }

  static Future<Map<String, dynamic>> logout() async {
    final result = await post('/auth/logout', {});
    await clearToken();
    return result;
  }

  // ─── Kasir API ─────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> kasirDashboard() => get('/kasir/dashboard');
  static Future<Map<String, dynamic>> kasirPosDashboard() => get('/kasir/pos-dashboard');
  static Future<Map<String, dynamic>> updateStatusPesanan(int id, String statusPesanan) =>
      post('/kasir/pesanan/$id/status', {'status_pesanan': statusPesanan});

  static Future<Map<String, dynamic>> createSplitBill(
    int pesananId, {
    required String tipe,
    required List<Map<String, dynamic>> splits,
  }) =>
      post('/kasir/pesanan/$pesananId/split-bill', {
        'tipe': tipe,
        'splits': splits,
      });

  static Future<Map<String, dynamic>> bayarSplitBill(
    int pesananId,
    int splitId, {
    String? metodePembayaran,
  }) =>
      post('/kasir/pesanan/$pesananId/split-bill/$splitId/bayar', {
        if (metodePembayaran != null) 'metode_pembayaran': metodePembayaran,
      });

  static Future<Map<String, dynamic>> resetSplitBill(int pesananId) =>
      delete('/kasir/pesanan/$pesananId/split-bill');

  static Future<Map<String, dynamic>> bayarSebagianItem(
    int pesananId, {
    required String label,
    required String metodePembayaran,
    required List<Map<String, dynamic>> items,
  }) =>
      post('/kasir/pesanan/$pesananId/split-bill/bayar-item', {
        'label': label,
        'metode_pembayaran': metodePembayaran,
        'items': items,
      });

  static Future<Map<String, dynamic>> mergeBill(int primaryOrderId, List<int> mergeIds) =>
      post('/kasir/pesanan/$primaryOrderId/merge-bill', {
        'merge_ids': mergeIds,
      });

  static Future<Map<String, dynamic>> telahBayar(int id, {String? metodePembayaran}) =>
      post('/kasir/telah-bayar/$id', {
        if (metodePembayaran != null) 'metode_pembayaran': metodePembayaran,
      });

  static Future<Map<String, dynamic>> siapPesanan(int id) =>
      post('/kasir/siap/$id', {});

  static Future<Map<String, dynamic>> cekPesananBaru(int lastId) =>
      get('/kasir/cek-baru/$lastId');

  static Future<Map<String, dynamic>> cekPembayaranBerhasil(String lastTime) =>
      get('/kasir/cek-pembayaran-berhasil?last_time=${Uri.encodeComponent(lastTime)}');

  static Future<Map<String, dynamic>> kasirRiwayat({
    String? startDate,
    String? endDate,
    String? periode,
  }) {
    String endpoint = '/kasir/riwayat';
    final params = <String, String>{};
    if (periode != null) params['periode'] = periode;
    if (startDate != null) params['start_date'] = startDate;
    if (endDate != null) params['end_date'] = endDate;
    if (params.isNotEmpty) {
      endpoint += '?${params.entries.map((e) => '${e.key}=${e.value}').join('&')}';
    }
    return get(endpoint);
  }

  // ─── Admin API ─────────────────────────────────────────────────────────────

  // Kategori
  static Future<Map<String, dynamic>> getKategoris() => get('/admin/kategori');
  static Future<Map<String, dynamic>> getKategori() => get('/admin/kategori');
  static Future<Map<String, dynamic>> storeKategori(Map<String, String> data) =>
      post('/admin/kategori', data.cast<String, dynamic>());
  static Future<Map<String, dynamic>> createKategori(Map<String, dynamic> data) =>
      post('/admin/kategori', data);
  static Future<Map<String, dynamic>> updateKategori(int id, Map<String, dynamic> data) =>
      put('/admin/kategori/$id', data);
  static Future<Map<String, dynamic>> deleteKategori(int id) => delete('/admin/kategori/$id');
  static Future<Map<String, dynamic>> toggleKategori(int id) =>
      post('/admin/kategori/$id/toggle', {});
  static Future<Map<String, dynamic>> toggleKategoriStatus(int id) =>
      post('/admin/kategori/$id/toggle', {});

  // Menu
  static Future<Map<String, dynamic>> getMenus() => get('/admin/menu');
  static Future<Map<String, dynamic>> storeMenu(
    Map<String, String> fields, {
    File? gambar,
    List<int>? imageBytes,
    String? filename,
  }) =>
      postMultipart(
        '/admin/menu',
        fields,
        imageFile: gambar,
        imageBytes: imageBytes,
        filename: filename,
      );
  static Future<Map<String, dynamic>> updateMenu(
    int id,
    Map<String, String> fields, {
    File? gambar,
    List<int>? imageBytes,
    String? filename,
  }) =>
      postMultipart(
        '/admin/menu/$id',
        {...fields, '_method': 'PUT'},
        imageFile: gambar,
        imageBytes: imageBytes,
        filename: filename,
        method: 'POST',
      );
  static Future<Map<String, dynamic>> deleteMenu(int id) => delete('/admin/menu/$id');
  static Future<Map<String, dynamic>> toggleMenu(int id) =>
      post('/admin/menu/$id/toggle', {});
  static Future<Map<String, dynamic>> toggleStokMenu(int id) =>
      post('/admin/menu/$id/toggle-stok', {});

  // Meja
  static Future<Map<String, dynamic>> getMejas() => get('/admin/meja');
  static Future<Map<String, dynamic>> storeMeja(String nomorMeja) =>
      post('/admin/meja', {'nomor_meja': nomorMeja});
  static Future<Map<String, dynamic>> deleteMeja(int id) => delete('/admin/meja/$id');
  static Future<Map<String, dynamic>> toggleMeja(int id) =>
      post('/admin/meja/$id/toggle', {});

  // Users
  static Future<Map<String, dynamic>> getUsers() => get('/admin/users');
  static Future<Map<String, dynamic>> storeUser(Map<String, String> data) =>
      post('/admin/users', data.cast<String, dynamic>());
  static Future<Map<String, dynamic>> deleteUser(int id) => delete('/admin/users/$id');

  // Riwayat
  static Future<Map<String, dynamic>> adminRiwayat({
    String? startDate,
    String? endDate,
    String? periode,
  }) {
    String endpoint = '/admin/riwayat';
    final params = <String, String>{};
    if (periode != null) params['periode'] = periode;
    if (startDate != null) params['start_date'] = startDate;
    if (endDate != null) params['end_date'] = endDate;
    if (params.isNotEmpty) {
      endpoint += '?${params.entries.map((e) => '${e.key}=${e.value}').join('&')}';
    }
    return get(endpoint);
  }

  // Promo
  static Future<Map<String, dynamic>> getPromos() => get('/admin/promo');
  static Future<Map<String, dynamic>> storePromo(Map<String, dynamic> data) =>
      post('/admin/promo', data);
  static Future<Map<String, dynamic>> updatePromo(int id, Map<String, dynamic> data) =>
      put('/admin/promo/$id', data);
  static Future<Map<String, dynamic>> togglePromo(int id) =>
      post('/admin/promo/$id/toggle', {});
  static Future<Map<String, dynamic>> deletePromo(int id) => delete('/admin/promo/$id');

  // Pengaturan
  static Future<Map<String, dynamic>> getPengaturan() => get('/admin/pengaturan');
  static Future<Map<String, dynamic>> updatePengaturan(
    Map<String, String> fields, {
    File? storeLogo,
    File? storeFavicon,
  }) async {
    await loadToken();
    final uri = Uri.parse('${ApiConfig.apiBaseUrl}/admin/pengaturan');
    final request = http.MultipartRequest('POST', uri);

    request.headers.addAll({
      'Accept': 'application/json',
      if (_token != null) 'Authorization': 'Bearer $_token',
    });

    fields.forEach((key, value) => request.fields[key] = value);

    if (storeLogo != null) {
      request.files.add(
        await http.MultipartFile.fromPath('store_logo', storeLogo.path),
      );
    }

    if (storeFavicon != null) {
      request.files.add(
        await http.MultipartFile.fromPath('store_favicon', storeFavicon.path),
      );
    }

    final streamedResponse = await request.send();
    final response = await http.Response.fromStream(streamedResponse);
    return _handleResponse(response);
  }

  // ─── Stok & Pembelian Barang ───────────────────────────────────────────────
  static Future<Map<String, dynamic>> getStok() => get('/admin/stok');
  static Future<Map<String, dynamic>> storeStok(Map<String, dynamic> data) =>
      post('/admin/stok', data);
  static Future<Map<String, dynamic>> updateStok(int id, Map<String, dynamic> data) =>
      put('/admin/stok/$id', data);
  static Future<Map<String, dynamic>> deleteStok(int id) => delete('/admin/stok/$id');
  static Future<Map<String, dynamic>> catatPembelianStok(
    Map<String, String> fields, {
    List<int>? imageBytes,
    String? filename,
    File? imageFile,
  }) =>
      postMultipart(
        '/admin/stok/pembelian',
        fields,
        imageBytes: imageBytes,
        filename: filename,
        imageFile: imageFile,
        imageField: 'foto_nota',
      );
  static Future<Map<String, dynamic>> getRiwayatPembelianStok() =>
      get('/admin/stok/riwayat-pembelian');
}
