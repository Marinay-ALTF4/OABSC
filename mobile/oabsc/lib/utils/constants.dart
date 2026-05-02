/// Application-wide constants
class AppConstants {
  // API Configuration
  // WAMP Server — use 10.0.2.2 for Android Emulator (maps to host localhost)
  // If using a physical device, replace with your PC's local IP (e.g. 192.168.1.X)
  static const String apiBaseUrl = 'http://10.0.2.2/OABSC/api';

  // App Info
  static const String appName = 'Clinic Appointment System';
  static const String appTagline = 'CLINIC APPOINTMENT PORTAL';

  // Asset paths
  static const String logoPath = 'lib/images/logo.png';

  // Splash screen duration
  static const int splashDurationMs = 2500;
}

/// User roles in the system
enum UserRole {
  admin('Admin', 'Full access to all system features'),
  assistantAdmin('Assistant Admin', 'Limited admin access – cannot manage users'),
  client('Client', 'Book appointments and view records'),
  secretary('Secretary', 'Manage front-desk operations'),
  doctor('Doctor', 'View schedule and patient information');

  final String displayName;
  final String description;

  const UserRole(this.displayName, this.description);
}

/// Named route constants
class AppRoutes {
  static const String splash = '/';
  static const String login = '/login';
  static const String roleSelection = '/role-selection';
  static const String adminDashboard = '/admin';
  static const String assistantAdminDashboard = '/assistant-admin';
  static const String clientDashboard = '/client';
  static const String secretaryDashboard = '/secretary';
  static const String doctorDashboard = '/doctor';

  /// Get the dashboard route for a given role
  static String dashboardForRole(UserRole role) {
    switch (role) {
      case UserRole.admin:
        return adminDashboard;
      case UserRole.assistantAdmin:
        return assistantAdminDashboard;
      case UserRole.client:
        return clientDashboard;
      case UserRole.secretary:
        return secretaryDashboard;
      case UserRole.doctor:
        return doctorDashboard;
    }
  }
}
