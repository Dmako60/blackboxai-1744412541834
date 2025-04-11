import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class HomePage extends StatefulWidget {
  @override
  _HomePageState createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  List properties = [];

  @override
  void initState() {
    super.initState();
    fetchProperties();
  }

  Future<void> fetchProperties() async {
    final response = await http.get(Uri.parse('http://localhost/backend/properties/list'));

    if (response.statusCode == 200) {
      setState(() {
        properties = json.decode(response.body)['data'];
      });
    } else {
      throw Exception('Failed to load properties');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Available Properties'),
      ),
      body: properties.isEmpty
          ? Center(child: CircularProgressIndicator())
          : ListView.builder(
              itemCount: properties.length,
              itemBuilder: (context, index) {
                return ListTile(
                  title: Text(properties[index]['title']),
                  subtitle: Text(properties[index]['location']),
                  onTap: () {
                    // Navigate to property details
                  },
                );
              },
            ),
    );
  }
}
