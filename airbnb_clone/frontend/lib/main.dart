import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'services/auth_provider.dart';
import 'screens/home_page.dart';
import 'screens/agent_login.dart';
import 'screens/agent_dashboard.dart';
import 'screens/subscription.dart';
import 'screens/add_property.dart';
import 'screens/edit_property.dart';
import 'screens/property_details.dart';
import 'screens/payment_page.dart';
import 'screens/admin/admin_login.dart';
import 'screens/admin/admin_dashboard.dart';
import 'screens/splash_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);
  runApp(
    ChangeNotifierProvider(
      create: (_) => AuthProvider(),
      child: AirbnbApp(),
    ),
  );
}

class AirbnbApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Airbnb Clone',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        primarySwatch: Colors.blue,
        fontFamily: 'Poppins',
        appBarTheme: AppBarTheme(
          backgroundColor: Colors.white,
          foregroundColor: Colors.black,
          elevation: 1,
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            padding: EdgeInsets.symmetric(horizontal: 24, vertical: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(8),
            ),
          ),
        ),
        inputDecorationTheme: InputDecorationTheme(
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
          ),
          contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        ),
      ),
      home: SplashScreen(),
      onGenerateRoute: (settings) {
        // Handle named routes with arguments
        switch (settings.name) {
          case '/':
            return MaterialPageRoute(builder: (_) => HomePage());
          
          case '/admin/login':
            return MaterialPageRoute(builder: (_) => AdminLogin());
          
          case '/admin/dashboard':
            return MaterialPageRoute(
              builder: (_) => Consumer<AuthProvider>(
                builder: (context, auth, _) {
                  if (!auth.isAdmin) {
                    return AdminLogin();
                  }
                  return AdminDashboard();
                },
              ),
            );
          
          case '/agent/login':
            return MaterialPageRoute(builder: (_) => AgentLogin());
          
          case '/agent/dashboard':
            return MaterialPageRoute(
              builder: (_) => Consumer<AuthProvider>(
                builder: (context, auth, _) {
                  if (!auth.isAgent) {
                    return AgentLogin();
                  }
                  return AgentDashboard();
                },
              ),
            );
          
          case '/subscription':
            return MaterialPageRoute(
              builder: (_) => Consumer<AuthProvider>(
                builder: (context, auth, _) {
                  if (!auth.isAgent) {
                    return AgentLogin();
                  }
                  return Subscription();
                },
              ),
            );
          
          case '/add_property':
            return MaterialPageRoute(
              builder: (_) => Consumer<AuthProvider>(
                builder: (context, auth, _) {
                  if (!auth.isAgent) {
                    return AgentLogin();
                  }
                  if (!auth.canAddProperty()) {
                    return Subscription();
                  }
                  return AddProperty();
                },
              ),
            );
          
          case '/edit_property':
            if (settings.arguments == null) {
              return MaterialPageRoute(builder: (_) => HomePage());
            }
            return MaterialPageRoute(
              builder: (_) => Consumer<AuthProvider>(
                builder: (context, auth, _) {
                  if (!auth.isAgent && !auth.isAdmin) {
                    return AgentLogin();
                  }
                  return EditProperty();
                },
              ),
              settings: settings,
            );
          
          case '/property_details':
            if (settings.arguments == null) {
              return MaterialPageRoute(builder: (_) => HomePage());
            }
            return MaterialPageRoute(
              builder: (_) => PropertyDetails(),
              settings: settings,
            );
          
          case '/payment':
            if (settings.arguments == null) {
              return MaterialPageRoute(builder: (_) => HomePage());
            }
            return MaterialPageRoute(
              builder: (_) => Consumer<AuthProvider>(
                builder: (context, auth, _) {
                  if (!auth.isAuthenticated) {
                    return AgentLogin();
                  }
                  return PaymentPage();
                },
              ),
              settings: settings,
            );
          
          default:
            return MaterialPageRoute(builder: (_) => HomePage());
        }
      },
    );
  }
}

// Custom route observer to handle navigation analytics or state management
class RouteObserver extends NavigatorObserver {
  @override
  void didPush(Route<dynamic> route, Route<dynamic>? previousRoute) {
    // Handle route push
  }

  @override
  void didPop(Route<dynamic> route, Route<dynamic>? previousRoute) {
    // Handle route pop
  }
}
