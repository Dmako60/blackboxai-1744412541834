import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:youtube_player_flutter/youtube_player_flutter.dart';
import 'package:carousel_slider/carousel_slider.dart';

class PropertyDetails extends StatefulWidget {
  @override
  _PropertyDetailsState createState() => _PropertyDetailsState();
}

class _PropertyDetailsState extends State<PropertyDetails> {
  late int propertyId;
  bool isLoading = true;
  Map<String, dynamic> propertyData = {};
  YoutubePlayerController? _youtubeController;
  DateTime? selectedCheckIn;
  DateTime? selectedCheckOut;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final Map<String, dynamic> args = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>;
    propertyId = args['propertyId'];
    fetchPropertyDetails();
  }

  @override
  void dispose() {
    _youtubeController?.dispose();
    super.dispose();
  }

  Future<void> fetchPropertyDetails() async {
    final response = await http.get(
      Uri.parse('http://localhost/backend/properties/view?id=$propertyId'),
      headers: <String, String>{
        'Authorization': 'Bearer YOUR_TOKEN_HERE', // Replace with actual token if needed
      },
    );

    if (response.statusCode == 200) {
      setState(() {
        propertyData = json.decode(response.body)['data'];
        isLoading = false;

        // Initialize YouTube player if URL exists
        if (propertyData['youtube_url'] != null && propertyData['youtube_url'].isNotEmpty) {
          final videoId = YoutubePlayer.convertUrlToId(propertyData['youtube_url']);
          if (videoId != null) {
            _youtubeController = YoutubePlayerController(
              initialVideoId: videoId,
              flags: YoutubePlayerFlags(
                autoPlay: false,
                mute: false,
              ),
            );
          }
        }
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to load property details')),
      );
      Navigator.pop(context);
    }
  }

  Future<void> makeReservation() async {
    if (selectedCheckIn == null || selectedCheckOut == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Please select check-in and check-out dates')),
      );
      return;
    }

    final response = await http.post(
      Uri.parse('http://localhost/backend/reservations/create'),
      headers: <String, String>{
        'Content-Type': 'application/json; charset=UTF-8',
        'Authorization': 'Bearer YOUR_TOKEN_HERE', // Replace with actual token
      },
      body: jsonEncode(<String, dynamic>{
        'property_id': propertyId,
        'check_in': selectedCheckIn!.toIso8601String(),
        'check_out': selectedCheckOut!.toIso8601String(),
      }),
    );

    if (response.statusCode == 200) {
      // Navigate to payment screen
      Navigator.pushNamed(
        context,
        '/payment',
        arguments: {
          'reservation_id': json.decode(response.body)['data']['id'],
          'amount': calculateTotalPrice(),
        },
      );
    } else {
      final error = json.decode(response.body);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(error['message'])),
      );
    }
  }

  double calculateTotalPrice() {
    if (selectedCheckIn == null || selectedCheckOut == null) return 0;
    final days = selectedCheckOut!.difference(selectedCheckIn!).inDays;
    return days * (propertyData['price_per_night'] ?? 0);
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return Scaffold(
        appBar: AppBar(title: Text('Property Details')),
        body: Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text(propertyData['title']),
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: <Widget>[
            // Image Carousel
            if (propertyData['image_urls'] != null && propertyData['image_urls'].isNotEmpty)
              CarouselSlider(
                options: CarouselOptions(
                  height: 250.0,
                  viewportFraction: 1.0,
                  enlargeCenterPage: false,
                ),
                items: (propertyData['image_urls'] as List).map<Widget>((url) {
                  return Builder(
                    builder: (BuildContext context) {
                      return Image.network(
                        url,
                        fit: BoxFit.cover,
                        width: double.infinity,
                      );
                    },
                  );
                }).toList(),
              ),

            Padding(
              padding: EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Text(
                    propertyData['title'],
                    style: Theme.of(context).textTheme.headlineMedium,
                  ),
                  SizedBox(height: 8),
                  Text(
                    propertyData['location'],
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  SizedBox(height: 16),
                  Text(
                    '\$${propertyData['price_per_night']}/night',
                    style: Theme.of(context).textTheme.titleLarge,
                  ),
                  SizedBox(height: 16),
                  Text(
                    'Description',
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  Text(propertyData['description']),
                  SizedBox(height: 16),

                  // YouTube Video Player
                  if (_youtubeController != null)
                    YoutubePlayer(
                      controller: _youtubeController!,
                      showVideoProgressIndicator: true,
                    ),

                  SizedBox(height: 16),
                  Text(
                    'Amenities',
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  Text(propertyData['amenities'] ?? 'No amenities listed'),
                  SizedBox(height: 24),

                  // Reservation Section
                  Card(
                    child: Padding(
                      padding: EdgeInsets.all(16.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Make a Reservation',
                            style: Theme.of(context).textTheme.titleLarge,
                          ),
                          SizedBox(height: 16),
                          Row(
                            children: [
                              Expanded(
                                child: TextButton(
                                  onPressed: () async {
                                    final date = await showDatePicker(
                                      context: context,
                                      initialDate: DateTime.now(),
                                      firstDate: DateTime.now(),
                                      lastDate: DateTime.now().add(Duration(days: 365)),
                                    );
                                    if (date != null) {
                                      setState(() {
                                        selectedCheckIn = date;
                                      });
                                    }
                                  },
                                  child: Text(selectedCheckIn != null
                                      ? 'Check-in: ${selectedCheckIn!.toString().split(' ')[0]}'
                                      : 'Select Check-in'),
                                ),
                              ),
                              SizedBox(width: 16),
                              Expanded(
                                child: TextButton(
                                  onPressed: () async {
                                    if (selectedCheckIn == null) {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        SnackBar(content: Text('Please select check-in date first')),
                                      );
                                      return;
                                    }
                                    final date = await showDatePicker(
                                      context: context,
                                      initialDate: selectedCheckIn!.add(Duration(days: 1)),
                                      firstDate: selectedCheckIn!.add(Duration(days: 1)),
                                      lastDate: selectedCheckIn!.add(Duration(days: 30)),
                                    );
                                    if (date != null) {
                                      setState(() {
                                        selectedCheckOut = date;
                                      });
                                    }
                                  },
                                  child: Text(selectedCheckOut != null
                                      ? 'Check-out: ${selectedCheckOut!.toString().split(' ')[0]}'
                                      : 'Select Check-out'),
                                ),
                              ),
                            ],
                          ),
                          SizedBox(height: 16),
                          if (selectedCheckIn != null && selectedCheckOut != null)
                            Text(
                              'Total Price: \$${calculateTotalPrice()}',
                              style: Theme.of(context).textTheme.titleMedium,
                            ),
                          SizedBox(height: 16),
                          ElevatedButton(
                            onPressed: selectedCheckIn != null && selectedCheckOut != null
                                ? makeReservation
                                : null,
                            child: Text('Make Reservation'),
                            style: ElevatedButton.styleFrom(
                              minimumSize: Size(double.infinity, 50),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
