import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../config/api_config.dart';
import '../models/models.dart';
import '../services/api_client.dart';

class AuthProvider extends ChangeNotifier {
  AuthProvider(this.api);

  final ApiClient api;

  StaffUser? user;
  bool booting = true;
  bool busy = false;
  String? error;

  Future<void> bootstrap() async {
    booting = true;
    notifyListeners();
    await api.load();
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(ApiConfig.userKey);
    if (api.isAuthenticated && raw != null) {
      try {
        user = StaffUser.fromJson(jsonDecode(raw) as Map<String, dynamic>);
        // Refresh profile quietly
        try {
          final me = await api.get('/staff/me');
          user = StaffUser.fromJson(Map<String, dynamic>.from(me['user'] as Map));
          await prefs.setString(ApiConfig.userKey, jsonEncode(user!.toJson()));
        } catch (_) {}
      } catch (_) {
        await logout();
      }
    }
    booting = false;
    notifyListeners();
  }

  Future<bool> loginWithCode(String code) async {
    return _login(() => api.post('/staff/login/code', body: {
          'login_code': code,
          'device_name': 'flutter-staff',
        }));
  }

  Future<bool> loginWithEmail(String email, String password) async {
    return _login(() => api.post('/staff/login', body: {
          'email': email,
          'password': password,
          'device_name': 'flutter-staff',
        }));
  }

  Future<bool> _login(Future<Map<String, dynamic>> Function() request) async {
    busy = true;
    error = null;
    notifyListeners();
    try {
      final data = await request();
      final token = data['token']?.toString();
      if (token == null || token.isEmpty) {
        throw ApiException('No token returned');
      }
      await api.setToken(token);
      user = StaffUser.fromJson(Map<String, dynamic>.from(data['user'] as Map));
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(ApiConfig.userKey, jsonEncode(user!.toJson()));
      busy = false;
      notifyListeners();
      return true;
    } catch (e) {
      error = e.toString();
      busy = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    try {
      if (api.isAuthenticated) {
        await api.post('/staff/logout');
      }
    } catch (_) {}
    await api.setToken(null);
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(ApiConfig.userKey);
    user = null;
    notifyListeners();
  }
}
