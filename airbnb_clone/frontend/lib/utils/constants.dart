import 'package:flutter/material.dart';

class AppConstants {
  // API Endpoints
  static const String baseUrl = 'http://localhost/backend';
  static const String apiVersion = '/api/v1';

  // API Routes
  static const String loginEndpoint = '/auth/login';
  static const String registerEndpoint = '/auth/register';
  static const String propertiesEndpoint = '/properties';
  static const String reservationsEndpoint = '/reservations';
  static const String paymentsEndpoint = '/payments';

  // Storage Keys
  static const String tokenKey = 'auth_token';
  static const String userDataKey = 'user_data';

  // Subscription Plans
  static const Map<String, int> propertyLimits = {
    'base': 4,
    'gold': 10,
    'vip': -1, // Unlimited
  };

  // Theme Colors
  static const Color primaryColor = Color(0xFF2196F3);
  static const Color accentColor = Color(0xFF03A9F4);
  static const Color errorColor = Color(0xFFD32F2F);
  static const Color successColor = Color(0xFF4CAF50);
  static const Color warningColor = Color(0xFFFFA000);
  static const Color backgroundColor = Color(0xFFF5F5F5);
  static const Color surfaceColor = Colors.white;
  static const Color textPrimaryColor = Color(0xFF212121);
  static const Color textSecondaryColor = Color(0xFF757575);

  // Text Styles
  static const TextStyle headingStyle = TextStyle(
    fontSize: 24,
    fontWeight: FontWeight.bold,
    color: textPrimaryColor,
  );

  static const TextStyle subheadingStyle = TextStyle(
    fontSize: 18,
    fontWeight: FontWeight.w600,
    color: textPrimaryColor,
  );

  static const TextStyle bodyStyle = TextStyle(
    fontSize: 16,
    color: textPrimaryColor,
  );

  static const TextStyle captionStyle = TextStyle(
    fontSize: 14,
    color: textSecondaryColor,
  );

  // Padding and Margins
  static const double paddingXS = 4.0;
  static const double paddingS = 8.0;
  static const double paddingM = 16.0;
  static const double paddingL = 24.0;
  static const double paddingXL = 32.0;

  // Border Radius
  static const double borderRadiusS = 4.0;
  static const double borderRadiusM = 8.0;
  static const double borderRadiusL = 16.0;
  static const double borderRadiusXL = 24.0;

  // Animation Durations
  static const Duration shortAnimationDuration = Duration(milliseconds: 200);
  static const Duration mediumAnimationDuration = Duration(milliseconds: 350);
  static const Duration longAnimationDuration = Duration(milliseconds: 500);

  // Subscription Plan Details
  static const Map<String, Map<String, dynamic>> subscriptionPlans = {
    'base': {
      'name': 'Base Plan',
      'price': 9.99,
      'propertyLimit': 4,
      'features': [
        'List up to 4 properties',
        'Basic analytics',
        'Email support',
      ],
    },
    'gold': {
      'name': 'Gold Plan',
      'price': 19.99,
      'propertyLimit': 10,
      'features': [
        'List up to 10 properties',
        'Advanced analytics',
        'Priority email support',
        'Featured listings',
      ],
    },
    'vip': {
      'name': 'V.I.P Plan',
      'price': 49.99,
      'propertyLimit': -1,
      'features': [
        'Unlimited property listings',
        'Premium analytics',
        '24/7 phone support',
        'Featured listings',
        'Custom branding',
        'Marketing tools',
      ],
    },
  };

  // Error Messages
  static const String networkError = 'Network error occurred. Please try again.';
  static const String serverError = 'Server error occurred. Please try again later.';
  static const String unauthorizedError = 'Unauthorized access. Please login again.';
  static const String validationError = 'Please check your input and try again.';

  // Success Messages
  static const String loginSuccess = 'Successfully logged in!';
  static const String registrationSuccess = 'Registration successful!';
  static const String propertyAddedSuccess = 'Property added successfully!';
  static const String propertyUpdatedSuccess = 'Property updated successfully!';
  static const String propertyDeletedSuccess = 'Property deleted successfully!';
  static const String reservationSuccess = 'Reservation completed successfully!';
  static const String paymentSuccess = 'Payment processed successfully!';

  // Image Assets
  static const String logoPath = 'assets/images/logo.png';
  static const String placeholderImagePath = 'assets/images/placeholder.png';
  static const String defaultAvatarPath = 'assets/images/default_avatar.png';

  // Validation Patterns
  static final RegExp emailPattern = RegExp(
    r'^[a-zA-Z0-9.]+@[a-zA-Z0-9]+\.[a-zA-Z]+',
  );
  static final RegExp passwordPattern = RegExp(
    r'^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$',
  );
  static final RegExp phonePattern = RegExp(
    r'^\+?[\d\s-]{10,}$',
  );
}
