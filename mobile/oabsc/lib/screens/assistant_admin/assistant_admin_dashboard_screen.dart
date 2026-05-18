import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/constants.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/welcome_banner.dart';
import '../../widgets/notification_section.dart';
import '../../services/auth_service.dart';
import '../../services/api_service.dart';

class AssistantAdminDashboardScreen extends StatefulWidget {
  const AssistantAdminDashboardScreen({super.key});

  @override
  State<AssistantAdminDashboardScreen> createState() => _AssistantAdminDashboardScreenState();
}

class _AssistantAdminDashboardScreenState extends State<AssistantAdminDashboardScreen> {
  final _authService = AuthService();
  final _apiService = ApiService();
  int _activeNavIndex = 0;
  String _adminName = 'Assistant Admin';
  final Map<String, dynamic> _stats = {
    'total_appointments': '0',
    'today_appointments': '0',
    'total_patients': '0',
    'total_doctors': '0',
    'pending': '0',
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
      setState(() => _adminName = name);
    }

    if (userId != null) {
      final response = await _apiService.get('dashboard?user_id=$userId&role=assistant_admin');
      if (response['success'] == true || response['total_appointments'] != null) {
        setState(() {
          _stats['total_appointments'] = (response['total_appointments'] ?? 0).toString();
          _stats['today_appointments'] = (response['today_appointments'] ?? 0).toString();
          _stats['total_patients'] = (response['total_patients'] ?? 0).toString();
          _stats['total_doctors'] = (response['total_doctors'] ?? 0).toString();
          _stats['pending'] = (response['pending'] ?? 0).toString();
        });
      }
    }
  }

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
          WelcomeBanner(panelLabel: 'ASSISTANT ADMIN PANEL', title: 'Welcome back, $_adminName', subtitle: 'Quick overview of your clinic\'s activity today.'),
          const SizedBox(height: 20),
          LayoutBuilder(builder: (context, constraints) {
            final cols = constraints.maxWidth > 500 ? 3 : 2;
            final w = (constraints.maxWidth - (cols - 1) * 12) / cols;
            return Wrap(spacing: 12, runSpacing: 12, children: [
              SizedBox(width: w, child: StatCard(icon: Icons.calendar_today_rounded, iconColor: AppColors.accentLight, iconBgColor: AppColors.iconBlueBg, count: _stats['total_appointments'], label: 'TOTAL APPOINTMENTS')),
              SizedBox(width: w, child: StatCard(icon: Icons.assignment_outlined, iconColor: AppColors.accentLight, iconBgColor: AppColors.iconBlueBg, count: _stats['today_appointments'], label: "TODAY'S APPOINTMENTS")),
              SizedBox(width: w, child: StatCard(icon: Icons.people_outline_rounded, iconColor: const Color(0xFF10B981), iconBgColor: AppColors.iconGreenBg, count: _stats['total_patients'], label: 'TOTAL PATIENTS')),
              SizedBox(width: w, child: StatCard(icon: Icons.medical_services_outlined, iconColor: const Color(0xFF10B981), iconBgColor: AppColors.iconGreenBg, count: _stats['total_doctors'], label: 'DOCTORS AVAILABLE')),
              SizedBox(width: w, child: StatCard(icon: Icons.pending_actions_rounded, iconColor: AppColors.warning, iconBgColor: AppColors.iconAmberBg, count: _stats['pending'], label: 'PENDING REQUESTS')),
            ]);
          }),
          const SizedBox(height: 24),
          const NotificationSection(),
        ]),
      ),
    );
  }
}
