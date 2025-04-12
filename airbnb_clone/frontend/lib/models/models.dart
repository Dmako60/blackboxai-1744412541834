export 'user.dart';
export 'agent.dart';
export 'property.dart';
export 'reservation.dart';
export 'payment.dart';
export 'review.dart';
export 'notification.dart';
export 'settings.dart';

// Enums for consistent status values across the app
enum ReservationStatus {
  pending,
  confirmed,
  cancelled,
  completed;

  String get displayName => name[0].toUpperCase() + name.substring(1).toLowerCase();
}

enum PaymentStatus {
  pending,
  completed,
  failed,
  refunded;

  String get displayName => name[0].toUpperCase() + name.substring(1).toLowerCase();
}

enum PropertyStatus {
  active,
  inactive,
  pending,
  rejected;

  String get displayName => name[0].toUpperCase() + name.substring(1).toLowerCase();
}

enum SubscriptionType {
  base,
  gold,
  vip;

  String get displayName => name[0].toUpperCase() + name.substring(1).toLowerCase();
  
  int get maxProperties {
    switch (this) {
      case SubscriptionType.base:
        return 4;
      case SubscriptionType.gold:
        return 10;
      case SubscriptionType.vip:
        return 999; // Unlimited for practical purposes
    }
  }

  double get commissionRate {
    switch (this) {
      case SubscriptionType.base:
        return 0.15; // 15%
      case SubscriptionType.gold:
        return 0.12; // 12%
      case SubscriptionType.vip:
        return 0.10; // 10%
    }
  }
}

enum NotificationPriority {
  low,
  medium,
  high,
  urgent;

  String get displayName => name[0].toUpperCase() + name.substring(1).toLowerCase();
  
  String get icon {
    switch (this) {
      case NotificationPriority.low:
        return '📝';
      case NotificationPriority.medium:
        return '📢';
      case NotificationPriority.high:
        return '⚠️';
      case NotificationPriority.urgent:
        return '🚨';
    }
  }
}

enum DisplayView {
  list,
  grid,
  map;

  String get displayName => name[0].toUpperCase() + name.substring(1).toLowerCase();
}

// Constants for validation and settings
class AppConstants {
  // Validation Constants
  static const int minPasswordLength = 8;
  static const int maxTitleLength = 100;
  static const int maxDescriptionLength = 1000;
  static const int maxSpecialRequestsLength = 500;
  static const int maxReviewLength = 500;
  static const int maxImagesPerProperty = 10;
  static const int maxImagesPerReview = 5;
  static const double minPrice = 1.0;
  static const double maxPrice = 99999.99;
  static const int maxGuestsPerBooking = 20;
  static const int minBookingDays = 1;
  static const int maxBookingDays = 90;
  static const int maxNotificationTitleLength = 100;
  static const int maxNotificationMessageLength = 500;
  static const int maxNotificationsPerPage = 20;
  static const int notificationRetentionDays = 30;

  // Settings Constants
  static const List<String> supportedLanguages = ['en', 'es', 'fr', 'de', 'it', 'pt', 'ja', 'ko', 'zh'];
  static const List<String> supportedCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'AUD', 'CAD', 'CHF', 'CNY', 'INR'];
  static const Map<String, String> languageNames = {
    'en': 'English',
    'es': 'Español',
    'fr': 'Français',
    'de': 'Deutsch',
    'it': 'Italiano',
    'pt': 'Português',
    'ja': '日本語',
    'ko': '한국어',
    'zh': '中文',
  };
  static const Map<String, String> currencySymbols = {
    'USD': '\$',
    'EUR': '€',
    'GBP': '£',
    'JPY': '¥',
    'AUD': 'A\$',
    'CAD': 'C\$',
    'CHF': 'Fr',
    'CNY': '¥',
    'INR': '₹',
  };

  // Default Settings
  static const String defaultLanguage = 'en';
  static const String defaultCurrency = 'USD';
  static const bool defaultDarkMode = false;
  static const bool defaultNotifications = true;
  static const bool defaultEmailUpdates = true;
  static const int defaultResultsPerPage = 20;
  static const DisplayView defaultView = DisplayView.grid;
}

// Helper functions for model-related operations
class ModelHelpers {
  static String formatCurrency(double amount, String currency) {
    final symbol = AppConstants.currencySymbols[currency] ?? currency;
    return '$symbol ${amount.toStringAsFixed(2)}';
  }

  static String formatDate(DateTime date) {
    return '${date.day}/${date.month}/${date.year}';
  }

  static String formatDateTime(DateTime dateTime) {
    return '${dateTime.day}/${dateTime.month}/${dateTime.year} ${dateTime.hour}:${dateTime.minute.toString().padLeft(2, '0')}';
  }

  static String getStarRating(double rating) {
    return '★' * rating.round() + '☆' * (5 - rating.round());
  }

  static bool isValidEmail(String email) {
    return RegExp(r'^[a-zA-Z0-9.]+@[a-zA-Z0-9]+\.[a-zA-Z]+').hasMatch(email);
  }

  static bool isValidPhone(String phone) {
    return RegExp(r'^\+?[\d\s-]{10,}$').hasMatch(phone);
  }

  static String maskCreditCard(String number) {
    if (number.length < 4) return number;
    return '****${number.substring(number.length - 4)}';
  }

  static int calculateNights(DateTime checkIn, DateTime checkOut) {
    return checkOut.difference(checkIn).inDays;
  }

  static double calculateTotalPrice(double pricePerNight, int nights) {
    return pricePerNight * nights;
  }

  static String getTimeAgo(DateTime dateTime) {
    final now = DateTime.now();
    final difference = now.difference(dateTime);

    if (difference.inDays > 7) {
      return dateTime.toString().substring(0, 10);
    } else if (difference.inDays > 0) {
      return '${difference.inDays}d ago';
    } else if (difference.inHours > 0) {
      return '${difference.inHours}h ago';
    } else if (difference.inMinutes > 0) {
      return '${difference.inMinutes}m ago';
    } else {
      return 'Just now';
    }
  }

  static NotificationPriority getNotificationPriority(String type) {
    switch (type.toLowerCase()) {
      case 'security':
      case 'payment_failed':
      case 'account_suspended':
        return NotificationPriority.urgent;
      case 'reservation_cancelled':
      case 'payment_pending':
      case 'property_rejected':
        return NotificationPriority.high;
      case 'new_message':
      case 'review_received':
      case 'booking_reminder':
        return NotificationPriority.medium;
      default:
        return NotificationPriority.low;
    }
  }

  static String getLanguageName(String code) {
    return AppConstants.languageNames[code] ?? code;
  }

  static String getCurrencySymbol(String code) {
    return AppConstants.currencySymbols[code] ?? code;
  }
}
