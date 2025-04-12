class Settings {
  final String language;
  final String currency;
  final bool darkMode;
  final bool notifications;
  final NotificationSettings notificationSettings;
  final bool emailUpdates;
  final DisplaySettings displaySettings;
  final PrivacySettings privacySettings;
  final DateTime lastUpdated;

  Settings({
    required this.language,
    required this.currency,
    required this.darkMode,
    required this.notifications,
    required this.notificationSettings,
    required this.emailUpdates,
    required this.displaySettings,
    required this.privacySettings,
    required this.lastUpdated,
  });

  factory Settings.fromJson(Map<String, dynamic> json) {
    return Settings(
      language: json['language'] ?? 'en',
      currency: json['currency'] ?? 'USD',
      darkMode: json['dark_mode'] ?? false,
      notifications: json['notifications'] ?? true,
      notificationSettings: NotificationSettings.fromJson(
        json['notification_settings'] ?? {},
      ),
      emailUpdates: json['email_updates'] ?? true,
      displaySettings: DisplaySettings.fromJson(
        json['display_settings'] ?? {},
      ),
      privacySettings: PrivacySettings.fromJson(
        json['privacy_settings'] ?? {},
      ),
      lastUpdated: json['last_updated'] != null
          ? DateTime.parse(json['last_updated'])
          : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'language': language,
      'currency': currency,
      'dark_mode': darkMode,
      'notifications': notifications,
      'notification_settings': notificationSettings.toJson(),
      'email_updates': emailUpdates,
      'display_settings': displaySettings.toJson(),
      'privacy_settings': privacySettings.toJson(),
      'last_updated': lastUpdated.toIso8601String(),
    };
  }

  Settings copyWith({
    String? language,
    String? currency,
    bool? darkMode,
    bool? notifications,
    NotificationSettings? notificationSettings,
    bool? emailUpdates,
    DisplaySettings? displaySettings,
    PrivacySettings? privacySettings,
    DateTime? lastUpdated,
  }) {
    return Settings(
      language: language ?? this.language,
      currency: currency ?? this.currency,
      darkMode: darkMode ?? this.darkMode,
      notifications: notifications ?? this.notifications,
      notificationSettings: notificationSettings ?? this.notificationSettings,
      emailUpdates: emailUpdates ?? this.emailUpdates,
      displaySettings: displaySettings ?? this.displaySettings,
      privacySettings: privacySettings ?? this.privacySettings,
      lastUpdated: lastUpdated ?? this.lastUpdated,
    );
  }
}

class NotificationSettings {
  final bool bookingUpdates;
  final bool paymentAlerts;
  final bool propertyUpdates;
  final bool promotionalOffers;
  final bool securityAlerts;
  final bool soundEnabled;
  final bool vibrationEnabled;

  NotificationSettings({
    required this.bookingUpdates,
    required this.paymentAlerts,
    required this.propertyUpdates,
    required this.promotionalOffers,
    required this.securityAlerts,
    required this.soundEnabled,
    required this.vibrationEnabled,
  });

  factory NotificationSettings.fromJson(Map<String, dynamic> json) {
    return NotificationSettings(
      bookingUpdates: json['booking_updates'] ?? true,
      paymentAlerts: json['payment_alerts'] ?? true,
      propertyUpdates: json['property_updates'] ?? true,
      promotionalOffers: json['promotional_offers'] ?? false,
      securityAlerts: json['security_alerts'] ?? true,
      soundEnabled: json['sound_enabled'] ?? true,
      vibrationEnabled: json['vibration_enabled'] ?? true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'booking_updates': bookingUpdates,
      'payment_alerts': paymentAlerts,
      'property_updates': propertyUpdates,
      'promotional_offers': promotionalOffers,
      'security_alerts': securityAlerts,
      'sound_enabled': soundEnabled,
      'vibration_enabled': vibrationEnabled,
    };
  }
}

class DisplaySettings {
  final bool showPricesWithTax;
  final bool showMapByDefault;
  final int resultsPerPage;
  final String defaultView; // 'list' or 'grid'
  final bool showAmenityIcons;
  final bool animationsEnabled;

  DisplaySettings({
    required this.showPricesWithTax,
    required this.showMapByDefault,
    required this.resultsPerPage,
    required this.defaultView,
    required this.showAmenityIcons,
    required this.animationsEnabled,
  });

  factory DisplaySettings.fromJson(Map<String, dynamic> json) {
    return DisplaySettings(
      showPricesWithTax: json['show_prices_with_tax'] ?? true,
      showMapByDefault: json['show_map_by_default'] ?? false,
      resultsPerPage: json['results_per_page'] ?? 20,
      defaultView: json['default_view'] ?? 'grid',
      showAmenityIcons: json['show_amenity_icons'] ?? true,
      animationsEnabled: json['animations_enabled'] ?? true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'show_prices_with_tax': showPricesWithTax,
      'show_map_by_default': showMapByDefault,
      'results_per_page': resultsPerPage,
      'default_view': defaultView,
      'show_amenity_icons': showAmenityIcons,
      'animations_enabled': animationsEnabled,
    };
  }
}

class PrivacySettings {
  final bool showProfilePicture;
  final bool showPhoneNumber;
  final bool showEmail;
  final bool showBookingHistory;
  final bool allowLocationAccess;
  final bool shareAnalytics;

  PrivacySettings({
    required this.showProfilePicture,
    required this.showPhoneNumber,
    required this.showEmail,
    required this.showBookingHistory,
    required this.allowLocationAccess,
    required this.shareAnalytics,
  });

  factory PrivacySettings.fromJson(Map<String, dynamic> json) {
    return PrivacySettings(
      showProfilePicture: json['show_profile_picture'] ?? true,
      showPhoneNumber: json['show_phone_number'] ?? false,
      showEmail: json['show_email'] ?? false,
      showBookingHistory: json['show_booking_history'] ?? false,
      allowLocationAccess: json['allow_location_access'] ?? true,
      shareAnalytics: json['share_analytics'] ?? true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'show_profile_picture': showProfilePicture,
      'show_phone_number': showPhoneNumber,
      'show_email': showEmail,
      'show_booking_history': showBookingHistory,
      'allow_location_access': allowLocationAccess,
      'share_analytics': shareAnalytics,
    };
  }
}
