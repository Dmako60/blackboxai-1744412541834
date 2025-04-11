import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class EditProperty extends StatefulWidget {
  @override
  _EditPropertyState createState() => _EditPropertyState();
}

class _EditPropertyState extends State<EditProperty> {
  final _formKey = GlobalKey<FormState>();
  late int propertyId;
  String title = '';
  String description = '';
  double pricePerNight = 0.0;
  String location = '';
  String amenities = '';
  String youtubeUrl = '';
  List<String> imageUrls = [];
  bool isLoading = true;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    // Get property ID from route arguments
    final Map<String, dynamic> args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>;
    propertyId = args['propertyId'];
    fetchPropertyDetails();
  }

  Future<void> fetchPropertyDetails() async {
    final response = await http.get(
      Uri.parse('http://localhost/backend/properties/view?id=$propertyId'),
      headers: <String, String>{
        'Authorization': 'Bearer YOUR_TOKEN_HERE', // Replace with actual token
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body)['data'];
      setState(() {
        title = data['title'];
        description = data['description'];
        pricePerNight = double.parse(data['price_per_night'].toString());
        location = data['location'];
        amenities = data['amenities'];
        youtubeUrl = data['youtube_url'] ?? '';
        imageUrls = List<String>.from(data['image_urls'] ?? []);
        isLoading = false;
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to load property details')),
      );
      Navigator.pop(context);
    }
  }

  Future<void> updateProperty() async {
    final response = await http.put(
      Uri.parse('http://localhost/backend/properties/update'),
      headers: <String, String>{
        'Content-Type': 'application/json; charset=UTF-8',
        'Authorization': 'Bearer YOUR_TOKEN_HERE', // Replace with actual token
      },
      body: jsonEncode(<String, dynamic>{
        'id': propertyId,
        'title': title,
        'description': description,
        'price_per_night': pricePerNight,
        'location': location,
        'amenities': amenities,
        'youtube_url': youtubeUrl,
        'image_urls': imageUrls,
      }),
    );

    if (response.statusCode == 200) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Property updated successfully')),
      );
      Navigator.pop(context);
    } else {
      final error = json.decode(response.body);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(error['message'])),
      );
    }
  }

  void addImageUrl(String url) {
    setState(() {
      imageUrls.add(url);
    });
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return Scaffold(
        appBar: AppBar(title: Text('Edit Property')),
        body: Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text('Edit Property'),
        actions: [
          IconButton(
            icon: Icon(Icons.delete),
            onPressed: () async {
              final confirm = await showDialog(
                context: context,
                builder: (context) => AlertDialog(
                  title: Text('Confirm Delete'),
                  content: Text('Are you sure you want to delete this property?'),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.pop(context, false),
                      child: Text('Cancel'),
                    ),
                    TextButton(
                      onPressed: () => Navigator.pop(context, true),
                      child: Text('Delete'),
                      style: TextButton.styleFrom(foregroundColor: Colors.red),
                    ),
                  ],
                ),
              );

              if (confirm == true) {
                // Delete property
                final response = await http.delete(
                  Uri.parse('http://localhost/backend/properties/delete'),
                  headers: <String, String>{
                    'Content-Type': 'application/json; charset=UTF-8',
                    'Authorization': 'Bearer YOUR_TOKEN_HERE', // Replace with actual token
                  },
                  body: jsonEncode(<String, dynamic>{
                    'id': propertyId,
                  }),
                );

                if (response.statusCode == 200) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Property deleted successfully')),
                  );
                  Navigator.pop(context);
                } else {
                  final error = json.decode(response.body);
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text(error['message'])),
                  );
                }
              }
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: <Widget>[
              TextFormField(
                initialValue: title,
                decoration: InputDecoration(labelText: 'Title'),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter a title';
                  }
                  return null;
                },
                onChanged: (value) {
                  title = value;
                },
              ),
              TextFormField(
                initialValue: description,
                decoration: InputDecoration(labelText: 'Description'),
                maxLines: 3,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter a description';
                  }
                  return null;
                },
                onChanged: (value) {
                  description = value;
                },
              ),
              TextFormField(
                initialValue: pricePerNight.toString(),
                decoration: InputDecoration(labelText: 'Price per Night'),
                keyboardType: TextInputType.number,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter a price';
                  }
                  return null;
                },
                onChanged: (value) {
                  pricePerNight = double.tryParse(value) ?? 0.0;
                },
              ),
              TextFormField(
                initialValue: location,
                decoration: InputDecoration(labelText: 'Location'),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter a location';
                  }
                  return null;
                },
                onChanged: (value) {
                  location = value;
                },
              ),
              TextFormField(
                initialValue: amenities,
                decoration: InputDecoration(labelText: 'Amenities (comma-separated)'),
                onChanged: (value) {
                  amenities = value;
                },
              ),
              TextFormField(
                initialValue: youtubeUrl,
                decoration: InputDecoration(labelText: 'YouTube Video URL'),
                onChanged: (value) {
                  youtubeUrl = value;
                },
              ),
              SizedBox(height: 20),
              Text('Images:', style: Theme.of(context).textTheme.titleMedium),
              ListView.builder(
                shrinkWrap: true,
                physics: NeverScrollableScrollPhysics(),
                itemCount: imageUrls.length + 1,
                itemBuilder: (context, index) {
                  if (index == imageUrls.length) {
                    return TextButton(
                      onPressed: () {
                        showDialog(
                          context: context,
                          builder: (context) => AlertDialog(
                            title: Text('Add Image URL'),
                            content: TextField(
                              decoration: InputDecoration(
                                hintText: 'Enter image URL',
                              ),
                              onSubmitted: (value) {
                                addImageUrl(value);
                                Navigator.pop(context);
                              },
                            ),
                          ),
                        );
                      },
                      child: Text('+ Add Image'),
                    );
                  }
                  return ListTile(
                    title: Text(imageUrls[index]),
                    trailing: IconButton(
                      icon: Icon(Icons.delete),
                      onPressed: () {
                        setState(() {
                          imageUrls.removeAt(index);
                        });
                      },
                    ),
                  );
                },
              ),
              SizedBox(height: 20),
              ElevatedButton(
                onPressed: () {
                  if (_formKey.currentState!.validate()) {
                    updateProperty();
                  }
                },
                child: Text('Update Property'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
