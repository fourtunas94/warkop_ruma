class ApiConfig {
  // Ganti dengan URL backend Laravel Anda
  //static const String baseUrl = 'http://10.0.2.2:8000'; // Android emulator → localhost
  static const String baseUrl = 'http://localhost:8000'; // iOS simulator / web
  // static const String baseUrl = 'http://192.168.1.x:8000'; // Physical device (ganti IP)

  static const String apiBaseUrl = '$baseUrl/api';
}
