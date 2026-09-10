import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import '../config/api_config.dart';

class ApiException implements Exception {
  ApiException(this.message, {this.statusCode});
  final String message;
  final int? statusCode;

  @override
  String toString() => message;
}

class ApiClient {
  ApiClient();

  String _baseUrl = ApiConfig.defaultBaseUrl;
  String? _token;

  String get baseUrl => _baseUrl;
  String? get token => _token;
  bool get isAuthenticated => _token != null && _token!.isNotEmpty;

  Future<void> load() async {
    final prefs = await SharedPreferences.getInstance();
    _baseUrl = prefs.getString(ApiConfig.baseUrlKey) ?? ApiConfig.defaultBaseUrl;
    _token = prefs.getString(ApiConfig.tokenKey);
  }

  Future<void> setBaseUrl(String url) async {
    _baseUrl = url.replaceAll(RegExp(r'/+$'), '');
    if (!_baseUrl.endsWith('/api')) {
      // allow either full API root or host root
      if (!_baseUrl.contains('/api')) {
        _baseUrl = '$_baseUrl/api';
      }
    }
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(ApiConfig.baseUrlKey, _baseUrl);
  }

  Future<void> setToken(String? token) async {
    _token = token;
    final prefs = await SharedPreferences.getInstance();
    if (token == null) {
      await prefs.remove(ApiConfig.tokenKey);
    } else {
      await prefs.setString(ApiConfig.tokenKey, token);
    }
  }

  Uri _uri(String path, [Map<String, String>? query]) {
    final clean = path.startsWith('/') ? path : '/$path';
    return Uri.parse('$_baseUrl$clean').replace(queryParameters: query);
  }

  Map<String, String> _headers({bool jsonBody = false}) {
    return {
      'Accept': 'application/json',
      if (jsonBody) 'Content-Type': 'application/json',
      if (_token != null) 'Authorization': 'Bearer $_token',
    };
  }

  Future<Map<String, dynamic>> get(String path, {Map<String, String>? query}) async {
    final res = await http.get(_uri(path, query), headers: _headers());
    return _decode(res);
  }

  Future<Map<String, dynamic>> post(String path, {Map<String, dynamic>? body}) async {
    final res = await http.post(
      _uri(path),
      headers: _headers(jsonBody: true),
      body: body == null ? null : jsonEncode(body),
    );
    return _decode(res);
  }

  Map<String, dynamic> _decode(http.Response res) {
    Map<String, dynamic> data = {};
    try {
      final decoded = jsonDecode(res.body);
      if (decoded is Map<String, dynamic>) {
        data = decoded;
      }
    } catch (_) {}

    if (res.statusCode >= 200 && res.statusCode < 300) {
      return data;
    }

    final message = data['message']?.toString()
        ?? (data['errors'] is Map
            ? (data['errors'] as Map).values.expand((e) => e is List ? e : [e]).join('\n')
            : null)
        ?? 'Request failed (${res.statusCode})';

    throw ApiException(message, statusCode: res.statusCode);
  }
}
