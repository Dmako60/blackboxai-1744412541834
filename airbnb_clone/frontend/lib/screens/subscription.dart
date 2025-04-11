import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class Subscription extends StatefulWidget {
  @override
  _SubscriptionState createState() => _SubscriptionState();
}

class _SubscriptionState extends State<Subscription> {
  String selectedPlan = 'base';

  Future<void> updateSubscription() async {
    final response = await http.put(
      Uri.parse('http://localhost/backend/agents/updateSubscription'),
      headers: <String, String>{
        'Content-Type': 'application/json; charset=UTF-8',
        'Authorization': 'Bearer YOUR_TOKEN_HERE', // Replace with actual token
      },
      body: jsonEncode(<String, String>{
        'subscription_type': selectedPlan,
      }),
    );

    if (response.statusCode == 200) {
      // Handle successful subscription update
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Subscription updated successfully')));
    } else {
      // Handle error
      final error = json.decode(response.body);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error['message'])));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Manage Subscription'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: <Widget>[
            ListTile(
              title: Text('Base Plan'),
              leading: Radio<String>(
                value: 'base',
                groupValue: selectedPlan,
                onChanged: (value) {
                  setState(() {
                    selectedPlan = value!;
                  });
                },
              ),
            ),
            ListTile(
              title: Text('Gold Plan'),
              leading: Radio<String>(
                value: 'gold',
                groupValue: selectedPlan,
                onChanged: (value) {
                  setState(() {
                    selectedPlan = value!;
                  });
                },
              ),
            ),
            ListTile(
              title: Text('V.I.P Plan'),
              leading: Radio<String>(
                value: 'vip',
                groupValue: selectedPlan,
                onChanged: (value) {
                  setState(() {
                    selectedPlan = value!;
                  });
                },
              ),
            ),
            SizedBox(height: 20),
            ElevatedButton(
              onPressed: updateSubscription,
              child: Text('Update Subscription'),
            ),
          ],
        ),
      ),
    );
  }
}
