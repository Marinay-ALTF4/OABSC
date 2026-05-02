import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/constants.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/welcome_banner.dart';
import '../../widgets/notification_section.dart';

class AssistantAdminDashboardScreen extends StatefulWidget {
  const AssistantAdminDashboardScreen({super.key});

  @override
  State<AssistantAdminDashboardScreen> createState() => _AssistantAdminDashboardScreenState();
}

class _AssistantAdminDashboardScreenState extends State<AssistantAdminDashboardScreen> {
  int _activeNavIndex = 0;

  List<DrawerNavItem> get _menuItems => [
    DrawerNavItem(icon: Icons.dashboard_rounded, label: 'Dashboard', onTap: () => setState(() => _activeNavIndex = 0)),
    DrawerNavItem(icon: Icons.folder_outlined, label: 'Patient Records', onTap: () => setState(() => _activeNavIndex = 1)),
    DrawerNavItem(icon: Icons.person_add_outlined, label: 'Add Patient', onTap: () => setState(() => _activeNavIndex = 2)),
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
      drawer: AppDrawer(roleName: 'Assistant Admin', menuItems: _menuItems, activeIndex: _activeNavIndex),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const WelcomeBanner(panelLabel: 'ASSISTANT ADMIN PANEL', title: 'Welcome back, Assistant Admin', subtitle: 'Quick overview of your clinic\'s activity today.'),
          const SizedBox(height: 20),
          LayoutBuilder(builder: (context, constraints) {
            final cols = constraints.maxWidth > 500 ? 3 : 2;
            final w = (constraints.maxWidth - (cols - 1) * 12) / cols;
            return Wrap(spacing: 12, runSpacing: 12, children: [
              SizedBox(width: w, child: const StatCard(icon: Icons.calendar_today_rounded, iconColor: AppColors.accentLight, iconBgColor: AppColors.iconBlueBg, count: '0', label: 'TOTAL APPOINTMENTS')),
              SizedBox(width: w, child: const StatCard(icon: Icons.assignment_outlined, iconColor: AppColors.accentLight, iconBgColor: AppColors.iconBlueBg, count: '0', label: "TODAY'S APPOINTMENTS")),
              SizedBox(width: w, child: const StatCard(icon: Icons.people_outline_rounded, iconColor: Color(0xFF10B981), iconBgColor: AppColors.iconGreenBg, count: '0', label: 'TOTAL PATIENTS')),
              SizedBox(width: w, child: const StatCard(icon: Icons.medical_services_outlined, iconColor: Color(0xFF10B981), iconBgColor: AppColors.iconGreenBg, count: '0', label: 'DOCTORS AVAILABLE')),
              SizedBox(width: w, child: const StatCard(icon: Icons.pending_actions_rounded, iconColor: AppColors.warning, iconBgColor: AppColors.iconAmberBg, count: '0', label: 'PENDING REQUESTS')),
            ]);
          }),
          const SizedBox(height: 24),
          const NotificationSection(),
        ]),
      ),
    );
  }
}
