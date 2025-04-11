import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'constants.dart';

class Helpers {
  // Date Formatting
  static String formatDate(DateTime date) {
    return DateFormat('MMM dd, yyyy').format(date);
  }

  static String formatDateTime(DateTime date) {
    return DateFormat('MMM dd, yyyy HH:mm').format(date);
  }

  // Currency Formatting
  static String formatCurrency(double amount) {
    return NumberFormat.currency(symbol: '\$').format(amount);
  }

  // Input Validation
  static bool isValidEmail(String email) {
    return AppConstants.emailPattern.hasMatch(email);
  }

  static bool isValidPassword(String password) {
    return AppConstants.passwordPattern.hasMatch(password);
  }

  static bool isValidPhone(String phone) {
    return AppConstants.phonePattern.hasMatch(phone);
  }

  // String Manipulation
  static String capitalize(String text) {
    if (text.isEmpty) return text;
    return text[0].toUpperCase() + text.substring(1);
  }

  static String truncateText(String text, int maxLength) {
    if (text.length <= maxLength) return text;
    return '${text.substring(0, maxLength)}...';
  }

  // Color Manipulation
  static Color darken(Color color, [double amount = .1]) {
    assert(amount >= 0 && amount <= 1);

    final hsl = HSLColor.fromColor(color);
    final hslDark = hsl.withLightness((hsl.lightness - amount).clamp(0.0, 1.0));

    return hslDark.toColor();
  }

  static Color lighten(Color color, [double amount = .1]) {
    assert(amount >= 0 && amount <= 1);

    final hsl = HSLColor.fromColor(color);
    final hslLight = hsl.withLightness((hsl.lightness + amount).clamp(0.0, 1.0));

    return hslLight.toColor();
  }

  // YouTube URL Helpers
  static String? getYouTubeVideoId(String url) {
    final regExp = RegExp(
      r'^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*',
      caseSensitive: false,
      multiLine: false,
    );
    final match = regExp.firstMatch(url);
    return match?[7];
  }

  static String getYouTubeThumbnailUrl(String videoId) {
    return 'https://img.youtube.com/vi/$videoId/maxresdefault.jpg';
  }

  // Subscription Plan Helpers
  static int getPropertyLimit(String subscriptionType) {
    return AppConstants.propertyLimits[subscriptionType] ?? 0;
  }

  static bool canAddMoreProperties(String subscriptionType, int currentCount) {
    final limit = getPropertyLimit(subscriptionType);
    return limit == -1 || currentCount < limit;
  }

  // Error Handling
  static String getErrorMessage(dynamic error) {
    if (error is String) return error;
    
    if (error is Map) {
      if (error.containsKey('message')) return error['message'];
      if (error.containsKey('error')) return error['error'];
    }

    return AppConstants.serverError;
  }

  // Navigation
  static void showSnackBar(BuildContext context, String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? AppConstants.errorColor : AppConstants.successColor,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  static Future<bool> showConfirmationDialog(
    BuildContext context, {
    required String title,
    required String message,
    String confirmText = 'Confirm',
    String cancelText = 'Cancel',
  }) async {
    final result = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text(cancelText),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text(confirmText),
            style: TextButton.styleFrom(
              foregroundColor: AppConstants.primaryColor,
            ),
          ),
        ],
      ),
    );
    return result ?? false;
  }

  // Device Screen Size Helpers
  static bool isSmallScreen(BuildContext context) {
    return MediaQuery.of(context).size.width < 600;
  }

  static bool isMediumScreen(BuildContext context) {
    return MediaQuery.of(context).size.width >= 600 && 
           MediaQuery.of(context).size.width < 1200;
  }

  static bool isLargeScreen(BuildContext context) {
    return MediaQuery.of(context).size.width >= 1200;
  }

  // Responsive Grid Items Count
  static int getGridCrossAxisCount(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    if (width < 600) return 1;
    if (width < 900) return 2;
    if (width < 1200) return 3;
    return 4;
  }

  // Date Range Validation
  static bool isValidDateRange(DateTime? startDate, DateTime? endDate) {
    if (startDate == null || endDate == null) return false;
    return startDate.isBefore(endDate);
  }

  static int calculateNights(DateTime startDate, DateTime endDate) {
    return endDate.difference(startDate).inDays;
  }

  static double calculateTotalPrice(double pricePerNight, DateTime startDate, DateTime endDate) {
    final nights = calculateNights(startDate, endDate);
    return pricePerNight * nights;
  }
}
