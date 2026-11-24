import 'package:flutter/material.dart';
import 'dart:ui';

// ─── Warkop Ruma Design System Colors ────────────────────────────────────────
abstract class AppColors {
  // Core surfaces
  static const background = Color(0xFF121317);
  static const surface = Color(0xFF121317);
  static const surfaceContainer = Color(0xFF1F1F24);
  static const surfaceContainerLow = Color(0xFF1A1B20);
  static const surfaceContainerHigh = Color(0xFF292A2E);
  static const surfaceContainerHighest = Color(0xFF343439);
  static const surfaceContainerLowest = Color(0xFF0D0E12);

  // Primary - Gold/Amber
  static const primary = Color(0xFFFFC665);
  static const primaryContainer = Color(0xFFE5A93C);
  static const onPrimary = Color(0xFF432C00);
  static const onPrimaryContainer = Color(0xFF5E4000);

  // Secondary
  static const secondary = Color(0xFFE9C349);
  static const secondaryContainer = Color(0xFFAF8D11);
  static const onSecondary = Color(0xFF3C2F00);

  // Tertiary - Warm terracotta
  static const tertiary = Color(0xFFFDC398);
  static const tertiaryContainer = Color(0xFFDFA87F);

  // Text colors
  static const onSurface = Color(0xFFE3E2E7);
  static const onSurfaceVariant = Color(0xFFD4C4B0);
  static const outline = Color(0xFF9D8F7C);
  static const outlineVariant = Color(0xFF504535);

  // Status colors
  static const error = Color(0xFFFFB4AB);
  static const errorContainer = Color(0xFF93000A);

  // Legacy aliases for compatibility
  static const card = surfaceContainer;
  static const cardDark = surfaceContainerLowest;
  static const border = Color(0x1AFFFFFF);
  static const textPrimary = onSurface;
  static const textMuted = onSurfaceVariant;
  static const cyan = Color(0xFF00E5FF);
  static const purple = Color(0xFF7C3AED);
  static const green = Color(0xFF22C55E);
  static const orange = Color(0xFFFB923C);
  static const red = Color(0xFFF43F5E);

  // Glow shadow color
  static const primaryGlow = Color(0x59E5A93C); // rgba(229,169,60,0.35)
}

// ─── AppCard ────────────────────────────────────────────────────────────────
class AppCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry? padding;
  final EdgeInsetsGeometry? margin;
  final Color? color;
  final double borderRadius;
  final VoidCallback? onTap;
  final bool glowing;

  const AppCard({
    super.key,
    required this.child,
    this.padding,
    this.margin,
    this.color,
    this.borderRadius = 12,
    this.onTap,
    this.glowing = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: margin,
      decoration: BoxDecoration(
        color: color ?? AppColors.surfaceContainer,
        borderRadius: BorderRadius.circular(borderRadius),
        border: Border.all(color: AppColors.outlineVariant.withOpacity(0.3)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.45),
            blurRadius: 20,
            offset: const Offset(0, 4),
          ),
          if (glowing)
            BoxShadow(
              color: AppColors.primary.withOpacity(0.08),
              blurRadius: 24,
              offset: const Offset(0, 8),
            ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(borderRadius),
        child: InkWell(
          borderRadius: BorderRadius.circular(borderRadius),
          onTap: onTap,
          child: Padding(
            padding: padding ?? const EdgeInsets.all(16),
            child: child,
          ),
        ),
      ),
    );
  }
}

// ─── StatCard ────────────────────────────────────────────────────────────────
class StatCard extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  final Color iconColor;
  final Color? valueColor;

  const StatCard({
    super.key,
    required this.label,
    required this.value,
    required this.icon,
    this.iconColor = AppColors.primary,
    this.valueColor,
  });

  @override
  Widget build(BuildContext context) {
    return AppCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: iconColor.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, color: iconColor, size: 18),
              ),
              const Spacer(),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            value,
            style: TextStyle(
              color: valueColor ?? AppColors.onSurface,
              fontSize: 22,
              fontWeight: FontWeight.bold,
              fontFamily: 'Epilogue',
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: const TextStyle(
              color: AppColors.onSurfaceVariant,
              fontSize: 12,
              fontFamily: 'SpaceGrotesk',
              letterSpacing: 0.5,
            ),
          ),
        ],
      ),
    );
  }
}

// ─── LoadingOverlay ──────────────────────────────────────────────────────────
class LoadingOverlay extends StatelessWidget {
  final bool isLoading;
  final Widget child;

  const LoadingOverlay({
    super.key,
    required this.isLoading,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        child,
        if (isLoading)
          BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 2, sigmaY: 2),
            child: Container(
              color: Colors.black54,
              child: const Center(
                child: CircularProgressIndicator(color: AppColors.primary),
              ),
            ),
          ),
      ],
    );
  }
}

// ─── ConfirmDialog ───────────────────────────────────────────────────────────
class ConfirmDialog extends StatelessWidget {
  final String title;
  final String message;
  final String confirmLabel;
  final Color confirmColor;
  final VoidCallback onConfirm;

  const ConfirmDialog({
    super.key,
    required this.title,
    required this.message,
    this.confirmLabel = 'Hapus',
    this.confirmColor = AppColors.error,
    required this.onConfirm,
  });

  static Future<bool?> show(
    BuildContext context, {
    required String title,
    required String message,
    String confirmLabel = 'Hapus',
    Color confirmColor = AppColors.error,
    required VoidCallback onConfirm,
  }) {
    return showDialog<bool>(
      context: context,
      builder: (_) => ConfirmDialog(
        title: title,
        message: message,
        confirmLabel: confirmLabel,
        confirmColor: confirmColor,
        onConfirm: onConfirm,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      backgroundColor: AppColors.surfaceContainer,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: AppColors.outlineVariant.withOpacity(0.3)),
      ),
      title: Text(title, style: const TextStyle(color: AppColors.onSurface, fontWeight: FontWeight.bold, fontFamily: 'Epilogue')),
      content: Text(message, style: const TextStyle(color: AppColors.onSurfaceVariant)),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context, false),
          child: const Text('Batal', style: TextStyle(color: AppColors.onSurfaceVariant)),
        ),
        ElevatedButton(
          style: ElevatedButton.styleFrom(
            backgroundColor: confirmColor,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          ),
          onPressed: () {
            Navigator.pop(context, true);
            onConfirm();
          },
          child: Text(confirmLabel, style: const TextStyle(color: Colors.white)),
        ),
      ],
    );
  }
}

// ─── Dark Input Decoration ───────────────────────────────────────────────────
InputDecoration darkInputDecoration({
  required String label,
  Widget? prefixIcon,
  Widget? suffixIcon,
  String? hint,
}) {
  return InputDecoration(
    labelText: label,
    hintText: hint,
    labelStyle: const TextStyle(color: AppColors.onSurfaceVariant, fontFamily: 'SpaceGrotesk'),
    hintStyle: const TextStyle(color: AppColors.outline),
    prefixIcon: prefixIcon,
    suffixIcon: suffixIcon,
    filled: true,
    fillColor: AppColors.surfaceContainerLowest,
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(12),
      borderSide: BorderSide(color: AppColors.outlineVariant.withOpacity(0.3)),
    ),
    enabledBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(12),
      borderSide: BorderSide(color: AppColors.outlineVariant.withOpacity(0.3)),
    ),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(12),
      borderSide: const BorderSide(color: AppColors.primary),
    ),
  );
}

// ─── SectionHeader ───────────────────────────────────────────────────────────
class SectionHeader extends StatelessWidget {
  final String title;
  final Widget? trailing;

  const SectionHeader({super.key, required this.title, this.trailing});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          title,
          style: const TextStyle(
            color: AppColors.onSurface,
            fontSize: 20,
            fontWeight: FontWeight.w600,
            fontFamily: 'Epilogue',
          ),
        ),
        if (trailing != null) trailing!,
      ],
    );
  }
}

// ─── StatusBadge ─────────────────────────────────────────────────────────────
class StatusBadge extends StatelessWidget {
  final String status;

  const StatusBadge({super.key, required this.status});

  Color get _color {
    switch (status.toLowerCase()) {
      case 'pending':
        return AppColors.primary;
      case 'selesai':
        return AppColors.green;
      case 'dibatalkan':
        return AppColors.error;
      case 'aktif':
        return AppColors.green;
      case 'tidak aktif':
        return AppColors.outline;
      case 'diproses':
        return AppColors.tertiary;
      default:
        return AppColors.secondary;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: _color.withOpacity(0.15),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 6,
            height: 6,
            decoration: BoxDecoration(
              color: _color,
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(width: 5),
          Text(
            status.toUpperCase(),
            style: TextStyle(
              color: _color,
              fontSize: 10,
              fontWeight: FontWeight.w600,
              fontFamily: 'SpaceGrotesk',
              letterSpacing: 0.8,
            ),
          ),
        ],
      ),
    );
  }
}

// ─── EmptyState ──────────────────────────────────────────────────────────────
class EmptyState extends StatelessWidget {
  final String message;
  final IconData icon;

  const EmptyState({
    super.key,
    this.message = 'Tidak ada data',
    this.icon = Icons.inbox_outlined,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: AppColors.primary.withOpacity(0.08),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, size: 48, color: AppColors.primary.withOpacity(0.5)),
          ),
          const SizedBox(height: 16),
          Text(
            message,
            style: const TextStyle(
              color: AppColors.onSurfaceVariant,
              fontSize: 15,
              fontFamily: 'PlusJakartaSans',
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
