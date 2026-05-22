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
  List<Map<String, dynamic>> _notifications = [];

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
      final notifResponse = await _apiService.get('notifications?user_id=$userId');

      if (mounted) {
        setState(() {
          if (response['success'] == true || response['today_appointments'] != null) {
            _stats['today_appointments'] = (response['today_appointments'] ?? 0).toString();
            _stats['pending'] = (response['pending'] ?? 0).toString();
            _stats['total_patients'] = (response['total_patients'] ?? 0).toString();
            _stats['total_doctors'] = (response['total_doctors'] ?? 0).toString();
          }
          if (notifResponse['notifications'] != null) {
            final rawNotifs = notifResponse['notifications'] as List;
            _notifications = rawNotifs.map((n) => n as Map<String, dynamic>).toList();
          }
        });
      }
    }
  }

  Future<void> _markAllNotificationsRead() async {
    final userId = await _authService.getSavedUserId();
    if (userId != null) {
      await _apiService.post('notifications/mark-read', {'user_id': userId});
    }
    setState(() {
      _notifications.clear();
    });
  }

  Future<void> _deleteNotification(int id) async {
    final response = await _apiService.delete('notifications/$id');
    if (response['success'] == true) {
      setState(() {
        _notifications.removeWhere((n) => (n['id'] is int ? n['id'] : int.tryParse(n['id']?.toString() ?? '')) == id);
      });
    }
  }

  void _viewNotification(Map<String, dynamic> notification) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text((notification['title'] ?? 'Notification').toString(), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        content: Text((notification['body'] ?? '').toString(), style: const TextStyle(fontSize: 14)),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close'),
          ),
        ],
      ),
    );
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
              icon: Badge(
                isLabelVisible: _notifications.isNotEmpty,
                label: Text(_notifications.length.toString()),
                child: const Icon(Icons.notifications_outlined, size: 22),
              ),
              onPressed: () {
                showModalBottomSheet(
                  context: context,
                  backgroundColor: Colors.transparent,
                  isScrollControlled: true,
                  builder: (context) => Container(
                    height: MediaQuery.of(context).size.height * 0.6,
                    padding: const EdgeInsets.only(top: 16, left: 16, right: 16),
                    decoration: const BoxDecoration(
                      color: AppColors.background,
                      borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
                    ),
                    child: SingleChildScrollView(
                      child: NotificationSection(
                        notifications: _notifications,
                        onMarkAllRead: () async {
                          await _markAllNotificationsRead();
                          if (mounted) Navigator.pop(context);
                        },
                        onDelete: _deleteNotification,
                        onView: _viewNotification,
                      ),
                    ),
                  ),
                );
              },
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
          NotificationSection(
            notifications: _notifications,
            onMarkAllRead: _markAllNotificationsRead,
            onDelete: _deleteNotification,
            onView: _viewNotification,
          ),
        ],
      ),
    );
  }
}
