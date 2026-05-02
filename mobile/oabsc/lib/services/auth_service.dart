import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';

/// Authentication service for login, role selection, and session management
class AuthService {
  final ApiService _api = ApiService();

  static const String _tokenKey = 'auth_token';
  static const String _roleKey = 'user_role';
  static const String _emailKey = 'user_email';
  static const String _nameKey = 'user_name';
  static const String _userIdKey = 'user_id';

  /// Login with email and password
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await _api.post('login', body: {
      'email': email,
      'password': password,
    });

    if (response['success'] == true) {
      final user = response['user'] as Map<String, dynamic>? ?? {};
      final token = response['token'] as String? ?? '';

      await saveSession(
        token: token,
        role: user['role'] as String? ?? 'client',
        email: user['email'] as String? ?? email,
        name: user['name'] as String? ?? '',
        userId: user['id']?.toString() ?? '',
      );

      return {
        'success': true,
        'message': response['message'] ?? 'Login successful',
        'user': user,
        'token': token,
      };
    }

    return {
      'success': false,
      'message': response['message'] ?? 'Login failed',
    };
  }

  /// Verify clinic access code and role password
  Future<Map<String, dynamic>> selectRole(
    String clinicAccessCode,
    String role,
    String rolePassword,
  ) async {
    final userId = await getSavedUserId();

    final response = await _api.post('role-selection', body: {
      'user_id': userId ?? '',
      'clinic_access_code': clinicAccessCode,
      'role': role,
      'role_password': rolePassword,
    });

    if (response['success'] == true) {
      final user = response['user'] as Map<String, dynamic>? ?? {};
      final token = response['token'] as String? ?? '';

      await saveSession(
        token: token,
        role: user['role'] as String? ?? role,
        email: user['email'] as String? ?? '',
        name: user['name'] as String? ?? '',
        userId: user['id']?.toString() ?? userId ?? '',
      );

      return {
        'success': true,
        'message': response['message'] ?? 'Role verified',
        'token': token,
      };
    }

    return {
      'success': false,
      'message': response['message'] ?? 'Role verification failed',
    };
  }

  /// Save session data
  Future<void> saveSession({
    required String token,
    required String role,
    required String email,
    required String name,
    String? userId,
  }) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
    await prefs.setString(_roleKey, role);
    await prefs.setString(_emailKey, email);
    await prefs.setString(_nameKey, name);
    if (userId != null) {
      await prefs.setString(_userIdKey, userId);
    }
    _api.setAuthToken(token);
  }

  /// Get saved role
  Future<String?> getSavedRole() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_roleKey);
  }

  /// Get saved token
  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }

  /// Get saved name
  Future<String?> getSavedName() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_nameKey);
  }

  /// Get saved user ID
  Future<String?> getSavedUserId() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_userIdKey);
  }

  /// Check if user is logged in
  Future<bool> isLoggedIn() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }

  /// Logout and clear session
  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_roleKey);
    await prefs.remove(_emailKey);
    await prefs.remove(_nameKey);
    await prefs.remove(_userIdKey);
    _api.clearAuthToken();
  }
}
