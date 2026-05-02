import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/constants.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/welcome_banner.dart';
import '../../widgets/notification_section.dart';
import 'manage_appointments_view.dart';

class SecretaryDashboardScreen extends StatefulWidget {
  const SecretaryDashboardScreen({super.key});

  @override
  State<SecretaryDashboardScreen> createState() => _SecretaryDashboardScreenState();
}

class _SecretaryDashboardScreenState extends State<SecretaryDashboardScreen> {
  int _activeNavIndex = 0;

  List<DrawerNavItem> get _menuItems => [
    DrawerNavItem(icon: Icons.dashboard_rounded, label: 'Dashboard', onTap: () => setState(() => _activeNavIndex = 0)),
    DrawerNavItem(icon: Icons.calendar_month_outlined, label: 'Manage Appointments', onTap: () => setState(() => _activeNavIndex = 1)),
    DrawerNavItem(icon: Icons.queue_rounded, label: 'Patient Queue', onTap: () => setState(() => _activeNavIndex = 2)),
    DrawerNavItem(icon: Icons.folder_outlined, label: 'Patient Records', onTap: () => setState(() => _activeNavIndex = 3)),
    DrawerNavItem(icon: Icons.person_add_outlined, label: 'Register New Patient', onTap: () => setState(() => _activeNavIndex = 4)),
    DrawerNavItem(icon: Icons.schedule_rounded, label: 'Doctor Schedules', onTap: () => setState(() => _activeNavIndex = 5)),
    DrawerNavItem(icon: Icons.check_circle_outline_rounded, label: 'Pending Approvals', onTap: () => setState(() => _activeNavIndex = 6)),
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
          const WelcomeBanner(panelLabel: 'SECRETARY PANEL', title: 'Welcome back, Secretary', subtitle: 'Here is your front-desk overview for today.'),
          const SizedBox(height: 20),
          LayoutBuilder(
            builder: (context, constraints) {
              final cols = constraints.maxWidth > 500 ? 4 : 2;
              final w = (constraints.maxWidth - (cols - 1) * 12) / cols;
              return Wrap(spacing: 12, runSpacing: 12, children: [
                SizedBox(width: w, child: const StatCard(icon: Icons.calendar_today_rounded, iconColor: AppColors.accentLight, iconBgColor: AppColors.iconBlueBg, count: '0', label: "TODAY'S APPOINTMENTS")),
                SizedBox(width: w, child: const StatCard(icon: Icons.pending_actions_rounded, iconColor: AppColors.warning, iconBgColor: AppColors.iconAmberBg, count: '0', label: 'PENDING REQUESTS')),
                SizedBox(width: w, child: const StatCard(icon: Icons.people_outline_rounded, iconColor: Color(0xFF10B981), iconBgColor: AppColors.iconGreenBg, count: '1', label: 'TOTAL PATIENTS')),
                SizedBox(width: w, child: const StatCard(icon: Icons.medical_services_outlined, iconColor: Color(0xFF10B981), iconBgColor: AppColors.iconGreenBg, count: '0', label: 'DOCTORS ON DUTY')),
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
