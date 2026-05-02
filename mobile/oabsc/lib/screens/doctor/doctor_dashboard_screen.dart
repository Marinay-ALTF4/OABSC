import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/constants.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/welcome_banner.dart';
import '../../widgets/notification_section.dart';
import 'doctor_appointments_view.dart';

class DoctorDashboardScreen extends StatefulWidget {
  const DoctorDashboardScreen({super.key});

  @override
  State<DoctorDashboardScreen> createState() => _DoctorDashboardScreenState();
}

class _DoctorDashboardScreenState extends State<DoctorDashboardScreen> {
  int _activeNavIndex = 0;

  List<DrawerNavItem> get _menuItems => [
    DrawerNavItem(icon: Icons.dashboard_rounded, label: 'Dashboard', onTap: () => setState(() => _activeNavIndex = 0)),
    DrawerNavItem(icon: Icons.assignment_outlined, label: 'My Appointments', onTap: () => setState(() => _activeNavIndex = 1)),
    DrawerNavItem(icon: Icons.queue_rounded, label: "Today's Queue", onTap: () => setState(() => _activeNavIndex = 2)),
    DrawerNavItem(icon: Icons.folder_outlined, label: 'Patient Records', onTap: () => setState(() => _activeNavIndex = 3)),
    DrawerNavItem(icon: Icons.edit_note_rounded, label: 'Write Notes', onTap: () => setState(() => _activeNavIndex = 4)),
    DrawerNavItem(icon: Icons.medication_rounded, label: 'Prescriptions', onTap: () => setState(() => _activeNavIndex = 5)),
    DrawerNavItem(icon: Icons.settings_rounded, label: 'Schedule Settings', onTap: () => setState(() => _activeNavIndex = 6)),
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
      default:
        return _buildDashboard();
    }
  }

  Widget _buildDashboard() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        const WelcomeBanner(
          panelLabel: 'DOCTOR PANEL', 
          title: 'Welcome, Dr. Doctor', 
          subtitle: 'Here is your clinical overview for today.',
          illustrationPath: 'lib/images/doctor-dashboard-illustration.svg',
        ),
        const SizedBox(height: 20),
        LayoutBuilder(builder: (context, constraints) {
          final cols = constraints.maxWidth > 600 ? 4 : 2;
          final w = (constraints.maxWidth - (cols - 1) * 12) / cols;
          return Wrap(spacing: 12, runSpacing: 12, children: [
            SizedBox(width: w, child: const StatCard(icon: Icons.people_outline_rounded, iconColor: Color(0xFF10B981), iconBgColor: AppColors.iconGreenBg, count: '0', label: "TODAY'S PATIENTS")),
            SizedBox(width: w, child: const StatCard(icon: Icons.calendar_today_rounded, iconColor: AppColors.accentLight, iconBgColor: AppColors.iconBlueBg, count: '0', label: 'UPCOMING')),
            SizedBox(width: w, child: const StatCard(icon: Icons.check_circle_outline_rounded, iconColor: Color(0xFF0D9488), iconBgColor: Color(0xFFF0FDFA), count: '0', label: 'COMPLETED')),
            SizedBox(width: w, child: const StatCard(icon: Icons.assignment_outlined, iconColor: Color(0xFF4F46E5), iconBgColor: Color(0xFFEEF2FF), count: '0', label: 'TOTAL CONSULTATIONS')),
          ]);
        }),
        const SizedBox(height: 24),
        const NotificationSection(),
      ]),
    );
  }
}
