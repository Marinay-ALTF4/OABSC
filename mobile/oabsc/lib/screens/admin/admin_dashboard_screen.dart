import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/constants.dart';
import '../../widgets/welcome_banner.dart';
import '../../widgets/notification_section.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';
import 'manage_users_view.dart';
import 'add_user_view.dart';
import 'add_role_view.dart';
import 'patient_records_view.dart';
import 'add_patient_view.dart';
import 'patient_list_view.dart';
import 'appointments_view.dart';
import 'doctor_schedules_view.dart';
import 'access_requests_view.dart';
import 'announcements_view.dart';
import 'audit_reports_view.dart';

/// Admin Dashboard screen — matching the web admin panel design
class AdminDashboardScreen extends StatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen> {
  String _currentView = 'dashboard';
  final ApiService _apiService = ApiService();
  final AuthService _authService = AuthService();
  Map<String, dynamic> _stats = {};
  bool _isLoadingStats = true;
  String _adminName = 'Admin';
  String _adminRole = 'admin';

  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    final name = await _authService.getSavedName();
    final role = await _authService.getSavedRole();
    if (name != null) setState(() => _adminName = name);
    if (role != null) setState(() => _adminRole = role);
    _fetchStats();
  }

  Future<void> _fetchStats() async {
    setState(() => _isLoadingStats = true);
    try {
      final userId = await _authService.getSavedUserId();
      final role = await _authService.getSavedRole();
      final response =
          await _apiService.get('dashboard?user_id=$userId&role=$role');
      if (response['success'] == true || response['total_appointments'] != null) {
        setState(() => _stats = response);
      }
    } catch (e) {
      debugPrint('Error fetching dashboard stats: $e');
    } finally {
      if (mounted) setState(() => _isLoadingStats = false);
    }
  }

  // ── Sidebar nav index ──────────────────────────────────────
  int get _activeNavIndex {
    switch (_currentView) {
      case 'dashboard':
        return 0;
      case 'manage_users':
      case 'add_user':
      case 'add_role':
        return 1;
      case 'patient_records':
      case 'patient_list':
        return 2;
      case 'manage_permissions':
        return 3;
      case 'appointments':
        return 4;
      case 'doctor_schedules':
        return 5;
      case 'access_requests':
        return 6;
      case 'announcements':
        return 7;
      case 'audit_log':
        return 8;
      case 'audit_reports':
        return 9;
      default:
        return 0;
    }
  }

  // ── Build body ─────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final isAssistant = _adminRole == 'assistant_admin';
    final roleLabel = isAssistant ? 'Assistant Admin' : 'Admin';

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        elevation: 0,
        shadowColor: const Color(0x14000000),
        surfaceTintColor: Colors.transparent,
        leading: Builder(
          builder: (ctx) => IconButton(
            icon: const Icon(Icons.menu_rounded, color: AppColors.textPrimary),
            onPressed: () => Scaffold.of(ctx).openDrawer(),
          ),
        ),
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            ClipOval(
              child: Image.asset(
                AppConstants.logoPath,
                width: 28,
                height: 28,
                fit: BoxFit.cover,
              ),
            ),
            const SizedBox(width: AppSpacing.sm),
            const Flexible(
              child: Text(
                'Clinic Appointment System',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textPrimary,
                ),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: AppSpacing.md),
            child: IconButton(
              icon: const Icon(Icons.notifications_outlined,
                  size: 22, color: AppColors.textPrimary),
              onPressed: () {},
            ),
          ),
        ],
      ),
      drawer: _buildDrawer(roleLabel, isAssistant),
      body: _buildBody(),
    );
  }

  // ── Sidebar drawer — matches web admin sidebar exactly ─────
  Widget _buildDrawer(String roleLabel, bool isAssistant) {
    final navItems = [
      _NavItem(Icons.dashboard_outlined, 'Dashboard',
          () => setState(() { _currentView = 'dashboard'; _fetchStats(); })),
      _NavItem(Icons.people_outline_rounded, 'Manage Users',
          () => setState(() => _currentView = 'manage_users')),
      _NavItem(Icons.folder_open_outlined, 'Patient Records',
          () => setState(() => _currentView = 'patient_records')),
      if (!isAssistant)
        _NavItem(Icons.shield_outlined, 'Manage Permissions',
            () => setState(() => _currentView = 'manage_permissions')),
      _NavItem(Icons.calendar_month_outlined, 'Appointments',
          () => setState(() => _currentView = 'appointments')),
      _NavItem(Icons.calendar_today_outlined, 'Doctor Schedules',
          () => setState(() => _currentView = 'doctor_schedules')),
      _NavItem(Icons.check_circle_outline_rounded, 'Access Requests',
          () => setState(() => _currentView = 'access_requests')),
      _NavItem(Icons.campaign_outlined, 'Announcements',
          () => setState(() => _currentView = 'announcements')),
      _NavItem(Icons.history_rounded, 'System Audit Log',
          () => setState(() => _currentView = 'audit_log')),
      _NavItem(Icons.bar_chart_rounded, 'Audit Reports',
          () => setState(() => _currentView = 'audit_reports')),
    ];

    return Drawer(
      child: SafeArea(
        child: Column(
          children: [
            // ── User section header ──────────────────────────
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(
                horizontal: AppSpacing.lg,
                vertical: AppSpacing.xl,
              ),
              decoration: const BoxDecoration(
                border: Border(bottom: BorderSide(color: AppColors.border)),
              ),
              child: Row(
                children: [
                  // Avatar circle
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: AppColors.iconBlueBg,
                      shape: BoxShape.circle,
                      border: Border.all(color: AppColors.border),
                    ),
                    child: const Icon(
                      Icons.person_rounded,
                      size: 26,
                      color: AppColors.accentLight,
                    ),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          _adminName,
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w700,
                            color: AppColors.textPrimary,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          roleLabel.toUpperCase(),
                          style: const TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w700,
                            color: AppColors.accent,
                            letterSpacing: 1.1,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // ── Navigation items ─────────────────────────────
            Expanded(
              child: ListView.builder(
                padding: const EdgeInsets.symmetric(
                    horizontal: AppSpacing.sm, vertical: AppSpacing.xs),
                itemCount: navItems.length,
                itemBuilder: (context, index) {
                  final item = navItems[index];
                  final isActive = index == _activeNavIndex;
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 2),
                    child: Material(
                      color: Colors.transparent,
                      child: InkWell(
                        onTap: () {
                          Navigator.pop(context);
                          item.onTap();
                        },
                        borderRadius: BorderRadius.circular(8),
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 180),
                          padding: const EdgeInsets.symmetric(
                            horizontal: AppSpacing.lg,
                            vertical: AppSpacing.md,
                          ),
                          decoration: BoxDecoration(
                            color: isActive
                                ? AppColors.accent
                                : Colors.transparent,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            children: [
                              Icon(
                                item.icon,
                                size: 20,
                                color: isActive
                                    ? Colors.white
                                    : AppColors.drawerInactiveText,
                              ),
                              const SizedBox(width: AppSpacing.md),
                              Expanded(
                                child: Text(
                                  item.label,
                                  style: TextStyle(
                                    fontSize: 14,
                                    fontWeight: isActive
                                        ? FontWeight.w600
                                        : FontWeight.w500,
                                    color: isActive
                                        ? Colors.white
                                        : AppColors.drawerInactiveText,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),

            // ── Logout ───────────────────────────────────────
            Container(
              padding: const EdgeInsets.all(AppSpacing.lg),
              decoration: const BoxDecoration(
                border: Border(top: BorderSide(color: AppColors.border)),
              ),
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: () => Navigator.of(context)
                      .pushNamedAndRemoveUntil(
                          AppRoutes.login, (route) => false),
                  borderRadius: BorderRadius.circular(8),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppSpacing.lg,
                      vertical: AppSpacing.md,
                    ),
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: const Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.logout_rounded,
                            size: 18, color: AppColors.error),
                        SizedBox(width: AppSpacing.sm),
                        Text(
                          'Logout',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                            color: AppColors.error,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Route body ─────────────────────────────────────────────
  Widget _buildBody() {
    switch (_currentView) {
      case 'dashboard':
        return RefreshIndicator(
          onRefresh: _fetchStats,
          child: _buildDashboard(),
        );
      case 'manage_users':
        return ManageUsersView(
          onAddUser: () => setState(() => _currentView = 'add_user'),
          onAddRole: () => setState(() => _currentView = 'add_role'),
        );
      case 'add_user':
        return AddUserView(
            onBack: () => setState(() => _currentView = 'manage_users'));
      case 'add_role':
        return AddRoleView(
            onBack: () => setState(() => _currentView = 'manage_users'));
      case 'patient_records':
        return PatientRecordsView(
          onBackToDashboard: () =>
              setState(() => _currentView = 'dashboard'),
          onViewPatientList: () =>
              setState(() => _currentView = 'patient_list'),
          onManageUsers: () =>
              setState(() => _currentView = 'manage_users'),
        );
      case 'patient_list':
        return PatientListView(
            onBack: () => setState(() => _currentView = 'patient_records'));
      case 'add_patient':
        return AddPatientView(
            onBack: () => setState(() => _currentView = 'patient_records'));
      case 'appointments':
        return const AppointmentsView();
      case 'doctor_schedules':
        return const DoctorSchedulesView();
      case 'access_requests':
        return const AccessRequestsView();
      case 'announcements':
        return const AnnouncementsView();
      case 'audit_reports':
      case 'audit_log':
        return const AuditReportsView();
      default:
        return _buildPlaceholder(_currentView);
    }
  }

  Widget _buildPlaceholder(String view) {
    const labels = {
      'manage_permissions': 'Manage Permissions',
    };
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.construction_rounded, size: 48, color: AppColors.textHint),
          const SizedBox(height: AppSpacing.md),
          Text(labels[view] ?? view,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
          const SizedBox(height: AppSpacing.sm),
          const Text('Coming soon', style: TextStyle(color: AppColors.textSecondary)),
        ],
      ),
    );
  }

  // ── Dashboard content ──────────────────────────────────────
  Widget _buildDashboard() {
    final isAssistant = _adminRole == 'assistant_admin';
    final panelLabel = isAssistant ? 'ASSISTANT ADMIN PANEL' : 'ADMIN PANEL';
    final subtitle = isAssistant
        ? 'You have limited admin access.'
        : "Quick overview of your clinic's activity today.";

    String stat(String key) =>
        _isLoadingStats ? '...' : (_stats[key]?.toString() ?? '0');

    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Welcome Banner ───────────────────────────────
          WelcomeBanner(
            panelLabel: panelLabel,
            title: 'Welcome back, $_adminName',
            subtitle: subtitle,
            illustrationPath:
                'lib/images/doctor-dashboard-illustration.svg',
          ),
          const SizedBox(height: AppSpacing.xl),

          // ── Stat cards — 3 per row, 2 rows (6 cards) ────
          LayoutBuilder(
            builder: (context, constraints) {
              // 3 cards per row on wider screens, 2 on phones
              final crossCount = constraints.maxWidth > 480 ? 3 : 2;
              final gap = 12.0;
              final cardWidth =
                  (constraints.maxWidth - gap * (crossCount - 1)) / crossCount;

              final cards = [
                _AdmStatCard(
                  icon: Icons.event_available_outlined,
                  bgColor: const Color(0xFFCCE4ED),
                  iconColor: const Color(0xFF2A6A7E),
                  count: stat('total_appointments'),
                  label: 'Total Appointments',
                ),
                _AdmStatCard(
                  icon: Icons.today,
                  bgColor: const Color(0xFFB8D8E4),
                  iconColor: const Color(0xFF1E5A6E),
                  count: stat('today_appointments'),
                  label: "Today's Appointments",
                ),
                _AdmStatCard(
                  icon: Icons.people_outline_rounded,
                  bgColor: const Color(0xFFA4CCD8),
                  iconColor: const Color(0xFF164A5C),
                  count: stat('total_patients'),
                  label: 'Total Patients',
                ),
                _AdmStatCard(
                  icon: Icons.badge,
                  bgColor: const Color(0xFF4E8A9E),
                  iconColor: const Color(0xFFE0F4FA),
                  count: stat('total_doctors'),
                  label: 'Doctors Available',
                ),
                _AdmStatCard(
                  icon: Icons.hourglass_top,
                  bgColor: const Color(0xFFCCE4ED),
                  iconColor: const Color(0xFF2A6A7E),
                  count: stat('pending'),
                  label: 'Pending Requests',
                ),
                _AdmStatCard(
                  icon: Icons.work_outline,
                  bgColor: const Color(0xFFB8D8E4),
                  iconColor: const Color(0xFF1E5A6E),
                  count: stat('secretaries'),
                  label: 'Secretaries',
                ),
              ];

              return Wrap(
                spacing: gap,
                runSpacing: gap,
                children: cards
                    .map((c) => SizedBox(width: cardWidth, child: c))
                    .toList(),
              );
            },
          ),
          const SizedBox(height: AppSpacing.xxl),

          // ── Notifications ────────────────────────────────
          const NotificationSection(),
        ],
      ),
    );
  }
}

// ── Internal nav item model ────────────────────────────────────
class _NavItem {
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  const _NavItem(this.icon, this.label, this.onTap);
}

// ── Stat card matching the web admin card style ────────────────
class _AdmStatCard extends StatelessWidget {
  final IconData icon;
  final Color bgColor;
  final Color iconColor;
  final String count;
  final String label;

  const _AdmStatCard({
    required this.icon,
    required this.bgColor,
    required this.iconColor,
    required this.count,
    required this.label,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border, width: 0.5),
        boxShadow: const [
          BoxShadow(
            color: AppColors.cardShadow,
            blurRadius: 8,
            offset: Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          // Icon badge
          Container(
            padding: const EdgeInsets.all(AppSpacing.sm),
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, size: 20, color: iconColor),
          ),
          const SizedBox(height: AppSpacing.md),
          // Count
          Text(
            count,
            style: const TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: AppSpacing.xs),
          // Label
          Text(
            label,
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: AppColors.textSecondary,
            ),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}
