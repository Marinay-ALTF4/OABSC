import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/constants.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/welcome_banner.dart';
import '../../widgets/notification_section.dart';
import 'manage_appointments_view.dart';
import 'patient_queue_view.dart';
import 'patient_records_view.dart';
import 'register_patient_view.dart';
import 'doctor_schedules_view.dart';
import 'pending_approvals_view.dart';
import '../client/profile_settings_view.dart';
import '../../services/auth_service.dart';
import '../../services/api_service.dart';

class SecretaryDashboardScreen extends StatefulWidget {
  const SecretaryDashboardScreen({super.key});

  @override
  State<SecretaryDashboardScreen> createState() => _SecretaryDashboardScreenState();
}

class _SecretaryDashboardScreenState extends State<SecretaryDashboardScreen> {
  final _authService = AuthService();
  final _apiService = ApiService();
  int _activeNavIndex = 0;
  String _secretaryName = 'Secretary';
  final Map<String, dynamic> _stats = {
    'today_appointments': '0',
    'pending': '0',
    'total_patients': '0',
    'total_doctors': '0',
  };

  @override
  void initState() {
    super.initState();
    _loadDashboardData();
  }

  Future<void> _loadDashboardData() async {
    final name = await _authService.getSavedName();
    final userId = await _authService.getSavedUserId();
    
    if (name != null) {
      setState(() => _secretaryName = name);
    }

    if (userId != null) {
      final response = await _apiService.get('dashboard?user_id=$userId&role=secretary');
      if (response['success'] == true || response['today_appointments'] != null) {
        setState(() {
          _stats['today_appointments'] = (response['today_appointments'] ?? 0).toString();
          _stats['pending'] = (response['pending'] ?? 0).toString();
          _stats['total_patients'] = (response['total_patients'] ?? 0).toString();
          _stats['total_doctors'] = (response['total_doctors'] ?? 0).toString();
        });
      }
    }
  }

  List<DrawerNavItem> get _menuItems => [
    DrawerNavItem(icon: Icons.dashboard_rounded, label: 'Dashboard', onTap: () => setState(() => _activeNavIndex = 0)),
    DrawerNavItem(icon: Icons.calendar_month_outlined, label: 'Manage Appointments', onTap: () => setState(() => _activeNavIndex = 1)),
    DrawerNavItem(icon: Icons.queue_rounded, label: 'Patient Queue', onTap: () => setState(() => _activeNavIndex = 2)),
    DrawerNavItem(icon: Icons.folder_outlined, label: 'Patient Records', onTap: () => setState(() => _activeNavIndex = 3)),
    DrawerNavItem(icon: Icons.person_add_outlined, label: 'Register New Patient', onTap: () => setState(() => _activeNavIndex = 4)),
    DrawerNavItem(icon: Icons.schedule_rounded, label: 'Doctor Schedules', onTap: () => setState(() => _activeNavIndex = 5)),
    DrawerNavItem(icon: Icons.check_circle_outline_rounded, label: 'Pending Approvals', onTap: () => setState(() => _activeNavIndex = 6)),
    DrawerNavItem(icon: Icons.person_outline, label: 'Profile Settings', onTap: () => setState(() => _activeNavIndex = 7)),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        elevation: 0,
        leading: Builder(
          builder: (ctx) => IconButton(icon: const Icon(Icons.menu_rounded), onPressed: () => Scaffold.of(ctx).openDrawer()),
        ),
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            ClipOval(child: Image.asset(AppConstants.logoPath, width: 28, height: 28, fit: BoxFit.cover)),
            const SizedBox(width: 8),
            const Flexible(child: Text('Clinic Appointment System', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.textPrimary), overflow: TextOverflow.ellipsis)),
          ],
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 12.0),
            child: IconButton(
              icon: const Icon(Icons.notifications_outlined, size: 22),
              onPressed: () {},
            ),
          ),
        ],
      ),
      drawer: AppDrawer(roleName: 'Secretary', menuItems: _menuItems, activeIndex: _activeNavIndex),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    switch (_activeNavIndex) {
      case 0:
        return _buildDashboard();
      case 1:
        return const ManageAppointmentsView();
      case 2:
        return const PatientQueueView();
      case 3:
        return const PatientRecordsView();
      case 4:
        return const RegisterPatientView();
      case 5:
        return const DoctorSchedulesView();
      case 6:
        return const PendingApprovalsView();
      case 7:
        return ProfileSettingsView(onBack: () => setState(() => _activeNavIndex = 0));
      default:
        return _buildDashboard();
    }
  }

  Widget _buildDashboard() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          WelcomeBanner(
            panelLabel: 'SECRETARY PANEL', 
            title: 'Welcome back, $_secretaryName', 
            subtitle: 'Here is your front-desk overview for today.',
            illustrationPath: 'lib/images/doctor-dashboard-illustration.svg',
          ),
          const SizedBox(height: 20),
          LayoutBuilder(
            builder: (context, constraints) {
              final cols = constraints.maxWidth > 500 ? 4 : 2;
              final w = (constraints.maxWidth - (cols - 1) * 12) / cols;
              return Wrap(spacing: 12, runSpacing: 12, children: [
                SizedBox(width: w, child: StatCard(icon: Icons.calendar_today_rounded, iconColor: AppColors.accentLight, iconBgColor: AppColors.iconBlueBg, count: _stats['today_appointments'], label: "TODAY'S APPOINTMENTS")),
                SizedBox(width: w, child: StatCard(icon: Icons.pending_actions_rounded, iconColor: AppColors.warning, iconBgColor: AppColors.iconAmberBg, count: _stats['pending'], label: 'PENDING REQUESTS')),
                SizedBox(width: w, child: StatCard(icon: Icons.people_outline_rounded, iconColor: const Color(0xFF10B981), iconBgColor: AppColors.iconGreenBg, count: _stats['total_patients'], label: 'TOTAL PATIENTS')),
                SizedBox(width: w, child: StatCard(icon: Icons.medical_services_outlined, iconColor: const Color(0xFF10B981), iconBgColor: AppColors.iconGreenBg, count: _stats['total_doctors'], label: 'DOCTORS ON DUTY')),
              ]);
            },
          ),
          const SizedBox(height: 24),
          const NotificationSection(),
        ],
      ),
    );
  }
}
