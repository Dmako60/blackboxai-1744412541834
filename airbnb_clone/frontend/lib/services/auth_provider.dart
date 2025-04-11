import 'package:flutter/material.dart';
import 'package:shared_preferences.dart';
import 'dart:convert';

enum UserRole {
  none,
  admin,
  agent,
  user
}

class AuthProvider with ChangeNotifier {
  String? _token;
  UserRole _userRole = UserRole.none;
  Map<String, dynamic>? _userData;
  bool _isInitialized = false;

  bool get isAuthenticated => _token != null;
  bool get isAdmin => _userRole == UserRole.admin;
  bool get isAgent => _userRole == UserRole.agent;
  bool get isUser => _userRole == UserRole.user;
  String? get token => _token;
  Map<String, dynamic>? get userData => _userData;

  Future<void> initialize() async {
    if (_isInitialized) return;

    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    final roleStr = prefs.getString('user_role');
    _userRole = roleStr != null ? UserRole.values.firstWhere(
      (e) => e.toString() == roleStr,
      orElse: () => UserRole.none
    ) : UserRole.none;
    
    final userDataStr = prefs.getString('user_data');
    if (userDataStr != null) {
      _userData = json.decode(userDataStr);
    }

    _isInitialized = true;
    notifyListeners();
  }

  Future<void> setAdminToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
    await prefs.setString('user_role', UserRole.admin.toString());
    
    _token = token;
    _userRole = UserRole.admin;
    notifyListeners();
  }

  Future<void> setAgentToken(String token, Map<String, dynamic> userData) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
    await prefs.setString('user_role', UserRole.agent.toString());
    await prefs.setString('user_data', json.encode(userData));
    
    _token = token;
    _userRole = UserRole.agent;
    _userData = userData;
    notifyListeners();
  }

  Future<void> setUserToken(String token, Map<String, dynamic> userData) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
    await prefs.setString('user_role', UserRole.user.toString());
    await prefs.setString('user_data', json.encode(userData));
    
    _token = token;
    _userRole = UserRole.user;
    _userData = userData;
    notifyListeners();
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('user_role');
    await prefs.remove('user_data');
    
    _token = null;
    _userRole = UserRole.none;
    _userData = null;
    notifyListeners();
  }

  bool canAddProperty() {
    if (!isAgent || _userData == null) return false;

    final propertyLimits = {
      'base': 4,
      'gold': 10,
      'vip': double.infinity
    };

    final subscriptionType = _userData!['subscription_type'] ?? 'base';
    final propertyCount = _userData!['property_count'] ?? 0;
    final limit = propertyLimits[subscriptionType] ?? 0;

    return propertyCount < limit;
  }

  Future<void> updateUserData(Map<String, dynamic> newData) async {
    if (_userData != null) {
      _userData!.addAll(newData);
      
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('user_data', json.encode(_userData));
      
      notifyListeners();
    }
  }

  String? getAuthorizationHeader() {
    return _token != null ? 'Bearer $_token' : null;
  }
}
