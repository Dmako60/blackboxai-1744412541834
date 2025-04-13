import 'package:flutter/material.dart';
import '../screens/home_page.dart';
import '../screens/agent_login.dart';
import '../screens/agent_dashboard.dart';
import '../screens/subscription.dart';
import '../screens/add_property.dart';
import '../screens/edit_property.dart';
import '../screens/property_details.dart';
import '../screens/payment_page.dart';
import '../screens/admin/admin_login.dart';
import '../screens/admin/admin_dashboard.dart';
import '../services/auth_provider.dart';
import 'package:provider/provider.dart';

class AppRoutes {
  static const String home = '/';
  static const String login = '/login';
  static const String dashboard = '/dashboard';
  static const String subscription = '/subscription';
  static const String addProperty = '/add_property';
  static const String editProperty = '/edit_property';
  static const String propertyDetails = '/property_details';
  static const String payment = '/payment';
  
  // Admin routes
  static const String adminLogin = '/admin/login';
  static const String adminDashboard = '/admin/dashboard';

  static Route<dynamic> generateRoute(RouteSettings settings) {
    switch (settings.name) {
      case home:
        return MaterialPageRoute(builder: (_) => HomePage());

      case login:
        return MaterialPageRoute(builder: (_) => AgentLogin());

      case dashboard:
        return MaterialPageRoute(
          builder: (context) {
            final auth = Provider.of<AuthProvider>(context, listen: false);
            if (!auth.isAuthenticated) {
              return AgentLogin();
            }
            return AgentDashboard();
          },
        );

      case subscription:
        return MaterialPageRoute(
          builder: (context) {
            final auth = Provider.of<AuthProvider>(context, listen: false);
            if (!auth.isAuthenticated) {
              return AgentLogin();
            }
            return Subscription();
          },
        );

      case addProperty:
        return MaterialPageRoute(
          builder: (context) {
            final auth = Provider.of<AuthProvider>(context, listen: false);
            if (!auth.isAuthenticated) {
              return AgentLogin();
            }
            if (!auth.canAddProperty()) {
              return Subscription();
            }
            return AddProperty();
          },
        );

      case editProperty:
        if (settings.arguments == null) {
          return MaterialPageRoute(builder: (_) => HomePage());
        }
        return MaterialPageRoute(
          builder: (context) {
            final auth = Provider.of<AuthProvider>(context, listen: false);
            if (!auth.isAuthenticated) {
              return AgentLogin();
            }
            return EditProperty();
          },
          settings: settings,
        );

      case propertyDetails:
        if (settings.arguments == null) {
          return MaterialPageRoute(builder: (_) => HomePage());
        }
        return MaterialPageRoute(
          builder: (_) => PropertyDetails(),
          settings: settings,
        );

      case payment:
        if (settings.arguments == null) {
          return MaterialPageRoute(builder: (_) => HomePage());
        }
        return MaterialPageRoute(
          builder: (context) {
            final auth = Provider.of<AuthProvider>(context, listen: false);
            if (!auth.isAuthenticated) {
              return AgentLogin();
            }
            return PaymentPage();
          },
          settings: settings,
        );

      // Admin routes
      case adminLogin:
        return MaterialPageRoute(builder: (_) => AdminLogin());

      case adminDashboard:
        return MaterialPageRoute(
          builder: (context) {
            final auth = Provider.of<AuthProvider>(context, listen: false);
            if (!auth.isAdmin) {
              return AdminLogin();
            }
            return AdminDashboard();
          },
        );

      default:
        return MaterialPageRoute(
          builder: (_) => Scaffold(
            body: Center(
              child: Text('No route defined for ${settings.name}'),
            ),
          ),
        );
    }
  }

  // Navigation helper methods
  static void navigateToHome(BuildContext context) {
    Navigator.pushReplacementNamed(context, home);
  }

  static void navigateToLogin(BuildContext context) {
    Navigator.pushNamed(context, login);
  }

  static void navigateToDashboard(BuildContext context) {
    Navigator.pushReplacementNamed(context, dashboard);
  }

  static void navigateToSubscription(BuildContext context) {
    Navigator.pushNamed(context, subscription);
  }

  static void navigateToAddProperty(BuildContext context) {
    Navigator.pushNamed(context, addProperty);
  }

  static void navigateToEditProperty(BuildContext context, int propertyId) {
    Navigator.pushNamed(
      context,
      editProperty,
      arguments: {'propertyId': propertyId},
    );
  }

  static void navigateToPropertyDetails(BuildContext context, int propertyId) {
    Navigator.pushNamed(
      context,
      propertyDetails,
      arguments: {'propertyId': propertyId},
    );
  }

  static void navigateToPayment(BuildContext context, {
    required int reservationId,
    required double amount,
  }) {
    Navigator.pushNamed(
      context,
      payment,
      arguments: {
        'reservation_id': reservationId,
        'amount': amount,
      },
    );
  }

  // Admin navigation methods
  static void navigateToAdminLogin(BuildContext context) {
    Navigator.pushReplacementNamed(context, adminLogin);
  }

  static void navigateToAdminDashboard(BuildContext context) {
    Navigator.pushReplacementNamed(context, adminDashboard);
  }
}
