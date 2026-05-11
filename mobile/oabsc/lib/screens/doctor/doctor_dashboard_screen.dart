import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/constants.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/welcome_banner.dart';
import '../../widgets/notification_section.dart';
import 'doctor_appointments_view.dart';
import 'doctor_queue_view.dart';
import 'doctor_patient_records_view.dart';
import 'doctor_notes_view.dart';
import 'doctor_prescriptions_view.dart';
import 'doctor_schedule_settings_view.dart';
import '../client/profile_settings_view.dart';
import '../../services/auth_service.dart';
import '../../services/api_service.dart';

class DoctorDashboardScreen extends StatefulWidget {
  const DoctorDashboardScreen({super.key});

  @override
  State<DoctorDashboardScreen> createState() => _DoctorDashboardScreenState();
}

class _DoctorDashboardScreenState extends State<DoctorDashboardScreen> {
  final _authService = AuthService();
  final _apiService = ApiService();
  int _activeNavIndex = 0;
  String _doctorName = 'Doctor';
  Map<String, dynamic> _stats = {
    'today_patients': '0',
    'upcoming': '0',
    'completed': '0',
    'total_consultations': '0',
  };
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadDashboardData();
  }

  Future<void> _loadDashboardData() async {
    setState(() => _isLoading = true);
    final name = await _authService.getSavedName();
    final userId = await _authService.getSavedUserId();
    
    if (name != null) {
      setState(() => _doctorName = name);
    }

    if (userId != null) {
      final response = await _apiService.get('dashboard?user_id=$userId&role=doctor');
      if (response['success'] == true || response['total_consultations'] != null) {
        setState(() {
          _stats['today_patients'] = (response['today_patients'] ?? 0).toString();
          _stats['upcoming'] = (response['upcoming'] ?? 0).toString();
          _stats['completed'] = (response['completed'] ?? 0).toString();
          _stats['total_consultations'] = (response['total_consultations'] ?? 0).toString();
        });
      }
    }
    setState(() => _isLoading = false);
  }

  List<DrawerNavItem> get _menuItems => [
    DrawerNavItem(icon: Icons.dashboard_rounded, label: 'Dashboard', onTap: () => setState(() => _activeNavIndex = 0)),
    DrawerNavItem(icon: Icons.assignment_outlined, label: 'My Appointments', onTap: () => setState(() => _activeNavIndex = 1)),
    DrawerNavItem(icon: Icons.queue_rounded, label: "Today's Queue", onTap: () => setState(() => _activeNavIndex = 2)),
    DrawerNavItem(icon: Icons.folder_outlined, label: 'Patient Records', onTap: () => setState(() => _activeNavIndex = 3)),
    DrawerNavItem(icon: Icons.edit_note_rounded, label: 'Write Notes', onTap: () => setState(() => _activeNavIndex = 4)),
    DrawerNavItem(icon: Icons.medication_rounded, label: 'Prescriptions', onTap: () => setState(() => _activeNavIndex = 5)),
    DrawerNavItem(icon: Icons.settings_rounded, label: 'Schedule Settings', onTap: () => setState(() => _activeNavIndex = 6)),
    DrawerNavItem(icon: Icons.person_outline, label: 'Profile Settings', onTap: () => setState(() => _activeNavIndex = 7)),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface, elevation: 0,
        leading: Builder(builder: (ctx) => IconButton(icon: const Icon(Icons.menu_rounded), onPressed: () => Scaffold.of(ctx).openDrawer())),
        title: Row(mainAxisSize: MainAxisSize.min, children: [
          ClipOval(child: Image.asset(AppConstants.logoPath, width: 28, height: 28, fit: BoxFit.cover)),
          const SizedBox(width: 8),
          const Flexible(child: Text('Clinic Appointment System', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.textPrimary), overflow: TextOverflow.ellipsis)),
        ]),
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
      drawer: AppDrawer(roleName: 'Doctor', menuItems: _menuItems, activeIndex: _activeNavIndex),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    switch (_activeNavIndex) {
      case 0:
        return _buildDashboard();
      case 1:
        return DoctorAppointmentsView(onBack: () => setState(() => _activeNavIndex = 0));
      case 2:
        return DoctorQueueView(onBack: () => setState(() => _activeNavIndex = 0));
      case 3:
        return DoctorPatientRecordsView(onBack: () => setState(() => _activeNavIndex = 0));
      case 4:
        return DoctorNotesView(onBack: () => setState(() => _activeNavIndex = 0));
      case 5:
        return DoctorPrescriptionsView(onBack: () => setState(() => _activeNavIndex = 0));
      case 6:
        return DoctorScheduleSettingsView(onBack: () => setState(() => _activeNavIndex = 0));
      case 7:
        return ProfileSettingsView(onBack: () => setState(() => _activeNavIndex = 0));
      default:
        return _buildDashboard();
    }
  }

  Widget _buildDashboard() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        WelcomeBanner(
          panelLabel: 'DOCTOR PANEL', 
          title: 'Welcome, Dr. $_doctorName', 
          subtitle: 'Here is your clinical overview for today.',
          illustrationPath: 'lib/images/doctor-dashboard-illustration.svg',
        ),
        const SizedBox(height: 20),
        LayoutBuilder(builder: (context, constraints) {
          final cols = constraints.maxWidth > 600 ? 4 : 2;
          final w = (constraints.maxWidth - (cols - 1) * 12) / cols;
          return Wrap(spacing: 12, runSpacing: 12, children: [
            SizedBox(width: w, child: StatCard(icon: Icons.people_outline_rounded, iconColor: AppColors.accentLight, iconBgColor: const Color(0xFFEAF6EA), count: _stats['today_patients'], label: "TODAY'S PATIENTS")),
            SizedBox(width: w, child: StatCard(icon: Icons.calendar_today_rounded, iconColor: AppColors.accent, iconBgColor: const Color(0xFFD0E8D2), count: _stats['upcoming'], label: 'UPCOMING')),
            SizedBox(width: w, child: StatCard(icon: Icons.check_circle_outline_rounded, iconColor: AppColors.primary, iconBgColor: const Color(0xFFE0F0E1), count: _stats['completed'], label: 'COMPLETED')),
            SizedBox(width: w, child: StatCard(icon: Icons.assignment_outlined, iconColor: AppColors.accentLight, iconBgColor: const Color(0xFFF4F9F4), count: _stats['total_consultations'], label: 'TOTAL CONSULTATIONS')),
          ]);
        }),
        const SizedBox(height: 24),
        const NotificationSection(),
      ]),
    );
  }
}
