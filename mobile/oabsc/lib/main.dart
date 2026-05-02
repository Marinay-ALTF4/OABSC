import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'theme/app_theme.dart';
import 'utils/constants.dart';
import 'screens/splash_screen.dart';
import 'screens/login_screen.dart';
import 'screens/role_selection_screen.dart';
import 'screens/admin/admin_dashboard_screen.dart';
import 'screens/assistant_admin/assistant_admin_dashboard_screen.dart';
import 'screens/client/client_dashboard_screen.dart';
import 'screens/secretary/secretary_dashboard_screen.dart';
import 'screens/doctor/doctor_dashboard_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
    ),
  );
  runApp(const ClinicApp());
}

class ClinicApp extends StatelessWidget {
  const ClinicApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: AppConstants.appName,
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      initialRoute: AppRoutes.splash,
      routes: {
        AppRoutes.splash: (_) => const SplashScreen(),
        AppRoutes.login: (_) => const LoginScreen(),
        AppRoutes.roleSelection: (_) => const RoleSelectionScreen(),
        AppRoutes.adminDashboard: (_) => const AdminDashboardScreen(),
        AppRoutes.assistantAdminDashboard: (_) => const AssistantAdminDashboardScreen(),
        AppRoutes.clientDashboard: (_) => const ClientDashboardScreen(),
        AppRoutes.secretaryDashboard: (_) => const SecretaryDashboardScreen(),
        AppRoutes.doctorDashboard: (_) => const DoctorDashboardScreen(),
      },
    );
  }
}
