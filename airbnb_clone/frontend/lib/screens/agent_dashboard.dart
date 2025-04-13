import 'package:flutter/material.dart';
import '../utils/routes.dart';
import '../services/auth_provider.dart';
import 'package:provider/provider.dart';

class AgentDashboard extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final userData = authProvider.userData;

    if (!authProvider.isAgent) {
      // Redirect to login if not authenticated as agent
      WidgetsBinding.instance.addPostFrameCallback((_) {
        Navigator.pushReplacementNamed(context, AppRoutes.login);
      });
      return Container(); // Return empty container while redirecting
    }

    return Scaffold(
      appBar: AppBar(
        title: Text('Agent Dashboard'),
        actions: [
          IconButton(
            icon: Icon(Icons.logout),
            onPressed: () async {
              await authProvider.logout();
              Navigator.pushReplacementNamed(context, AppRoutes.login);
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: <Widget>[
            Card(
              child: Padding(
                padding: EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Welcome, ${userData?['name'] ?? 'Agent'}!',
                      style: Theme.of(context).textTheme.headlineSmall,
                    ),
                    SizedBox(height: 8),
                    Text(
                      'Subscription: ${userData?['subscription_type']?.toUpperCase() ?? 'Base'}',
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    Text(
                      'Properties: ${userData?['property_count'] ?? 0}',
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                  ],
                ),
              ),
            ),
            SizedBox(height: 24),
            Text(
              'Quick Actions',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: authProvider.canAddProperty()
                ? () => AppRoutes.navigateToAddProperty(context)
                : () => AppRoutes.navigateToSubscription(context),
              icon: Icon(Icons.add_home),
              label: Text(
                authProvider.canAddProperty()
                  ? 'Add New Property'
                  : 'Upgrade to Add More Properties'
              ),
              style: ElevatedButton.styleFrom(
                padding: EdgeInsets.symmetric(vertical: 16),
              ),
            ),
            SizedBox(height: 12),
            ElevatedButton.icon(
              onPressed: () => AppRoutes.navigateToSubscription(context),
              icon: Icon(Icons.card_membership),
              label: Text('Manage Subscription'),
              style: ElevatedButton.styleFrom(
                padding: EdgeInsets.symmetric(vertical: 16),
              ),
            ),
            SizedBox(height: 12),
            ElevatedButton.icon(
              onPressed: () => AppRoutes.navigateToHome(context),
              icon: Icon(Icons.home_work),
              label: Text('View My Properties'),
              style: ElevatedButton.styleFrom(
                padding: EdgeInsets.symmetric(vertical: 16),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
