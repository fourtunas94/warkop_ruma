import 'package:flutter/material.dart';

class AppColors {
  // Primary Brand (Biru Cerah ala Dashboard Admin)
  static const Color primary = Color(0xFF0284C7); // Sky 600
  static const Color primaryContainer = Color(0xFF0EA5E9); // Sky 500
  static const Color onPrimary = Color(0xFFFFFFFF);
  static const Color onPrimaryContainer = Color(0xFFFFFFFF);

  // Secondary (Biru Muda / Info)
  static const Color secondary = Color(0xFF0369A1); // Sky 700
  static const Color secondaryContainer = Color(0xFFE0F2FE); // Sky 100
  static const Color onSecondary = Color(0xFFFFFFFF);

  // Tertiary (Amber Hangat untuk "Sedang Dibuat" / Catatan Barista)
  static const Color tertiary = Color(0xFFD97706); // Amber 600

  // Status Semantic
  static const Color green = Color(0xFF16A34A); // Hijau "SELESAI" di gambar
  static const Color red = Color(0xFFDC2626); // Merah untuk "Belum Bayar"
  static const Color error = Color(0xFFEF4444);
  static const Color errorContainer = Color(0xFFFEE2E2);

  // Background & Surface (Clean Slate Light)
  static const Color background = Color(0xFFF1F5F9); // Slate 100
  static const Color surface = Color(0xFFF8FAFC); // Slate 50
  static const Color surfaceContainerLowest = Color(0xFFFFFFFF); // Putih murni
  static const Color surfaceContainerLow = Color(0xFFF8FAFC); // Abu-abu sangat muda
  static const Color surfaceContainer = Color(0xFFFFFFFF); // Card Putih
  static const Color surfaceContainerHigh = Color(0xFFE2E8F0); // Slate 200
  static const Color surfaceContainerHighest = Color(0xFFCBD5E1); // Slate 300

  // Typography & Outline
  static const Color onSurface = Color(0xFF0F172A); // Slate 900 (Teks utama)
  static const Color onSurfaceVariant = Color(0xFF64748B); // Slate 500 (Teks abu-abu)
  static const Color outline = Color(0xFF94A3B8); // Slate 400
  static const Color outlineVariant = Color(0xFFCBD5E1); // Slate 300

  // Dark Navy Khusus Sidebar Drawer (Sesuai panel kiri gambar)
  static const Color sidebarBg = Color(0xFF0B1120);
  static const Color sidebarActiveBg = Color(0xFF1E293B);
  static const Color sidebarText = Color(0xFF94A3B8);

  static ThemeData get lightTheme => ThemeData(
        useMaterial3: true,
        brightness: Brightness.light,
        scaffoldBackgroundColor: background,
        colorScheme: const ColorScheme.light(
          primary: primary,
          onPrimary: onPrimary,
          primaryContainer: primaryContainer,
          onPrimaryContainer: onPrimaryContainer,
          secondary: secondary,
          onSecondary: onSecondary,
          secondaryContainer: secondaryContainer,
          tertiary: tertiary,
          surface: surface,
          onSurface: onSurface,
          onSurfaceVariant: onSurfaceVariant,
          error: error,
          outline: outline,
          outlineVariant: outlineVariant,
        ),
      );
}