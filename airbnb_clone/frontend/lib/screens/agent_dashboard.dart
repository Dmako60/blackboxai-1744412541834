import 'package:flutter/material.dart';

class AgentDashboard extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Agent Dashboard'),
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            Text('Welcome to your dashboard!'),
            SizedBox(height: 20),
            ElevatedButton(
              onPressed: () {
                // Navigate to add property screen
                Navigator.pushNamed(context, '/add_property');
              },
              child: Text('Add Property'),
            ),
            ElevatedButton(
              onPressed: () {
                // Navigate to subscription management
                Navigator.pushNamed(context, '/subscription');
              },
              child: Text('Manage Subscription'),
            },
            ElevatedButton(
              onPressed: () {
                // Navigate to view properties
                Navigator.pushNamed(context, '/view_properties');
              },
              child: Text('View My Properties'),
            },
          ],
        ),
      ),
    );
  }
}
