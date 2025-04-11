import 'package:http/http.dart' as http;
import 'package:shared_preferences.dart';
import 'dart:convert';

class ApiService {
  static const String baseUrl = 'http://localhost/backend';
  static const String tokenKey = 'auth_token';

  // Singleton pattern
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(tokenKey);
  }

  Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(tokenKey, token);
  }

  Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(tokenKey);
  }

  Future<Map<String, String>> getHeaders({bool requiresAuth = true}) async {
    Map<String, String> headers = {
      'Content-Type': 'application/json; charset=UTF-8',
    };

    if (requiresAuth) {
      final token = await getToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }
    }

    return headers;
  }

  // Authentication
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: await getHeaders(requiresAuth: false),
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      await saveToken(data['data']['token']);
      return data;
    } else {
      throw Exception(json.decode(response.body)['message']);
    }
  }

  Future<Map<String, dynamic>> register(String name, String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/register'),
      headers: await getHeaders(requiresAuth: false),
      body: jsonEncode({
        'name': name,
        'email': email,
        'password': password,
      }),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception(json.decode(response.body)['message']);
    }
  }

  // Properties
  Future<List<dynamic>> getProperties({int page = 1}) async {
    final response = await http.get(
      Uri.parse('$baseUrl/properties/list?page=$page'),
      headers: await getHeaders(requiresAuth: false),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body)['data'];
    } else {
      throw Exception('Failed to load properties');
    }
  }

  Future<Map<String, dynamic>> getPropertyDetails(int propertyId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/properties/view?id=$propertyId'),
      headers: await getHeaders(requiresAuth: false),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body)['data'];
    } else {
      throw Exception('Failed to load property details');
    }
  }

  Future<Map<String, dynamic>> addProperty(Map<String, dynamic> propertyData) async {
    final response = await http.post(
      Uri.parse('$baseUrl/properties/add'),
      headers: await getHeaders(),
      body: jsonEncode(propertyData),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception(json.decode(response.body)['message']);
    }
  }

  Future<Map<String, dynamic>> updateProperty(Map<String, dynamic> propertyData) async {
    final response = await http.put(
      Uri.parse('$baseUrl/properties/update'),
      headers: await getHeaders(),
      body: jsonEncode(propertyData),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception(json.decode(response.body)['message']);
    }
  }

  // Subscriptions
  Future<Map<String, dynamic>> updateSubscription(String subscriptionType) async {
    final response = await http.put(
      Uri.parse('$baseUrl/agents/updateSubscription'),
      headers: await getHeaders(),
      body: jsonEncode({
        'subscription_type': subscriptionType,
      }),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception(json.decode(response.body)['message']);
    }
  }

  // Reservations
  Future<Map<String, dynamic>> createReservation(Map<String, dynamic> reservationData) async {
    final response = await http.post(
      Uri.parse('$baseUrl/reservations/create'),
      headers: await getHeaders(),
      body: jsonEncode(reservationData),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception(json.decode(response.body)['message']);
    }
  }

  // Payments
  Future<Map<String, dynamic>> processPayment(Map<String, dynamic> paymentData) async {
    final response = await http.post(
      Uri.parse('$baseUrl/payments/process'),
      headers: await getHeaders(),
      body: jsonEncode(paymentData),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception(json.decode(response.body)['message']);
    }
  }
}
