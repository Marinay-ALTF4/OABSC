import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/constants.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/welcome_banner.dart';
import '../../widgets/notification_section.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';
import 'manage_users_view.dart';
import 'add_user_view.dart';
import 'add_role_view.dart';
import 'patient_records_view.dart';
import 'add_patient_view.dart';

/// Admin Dashboard screen matching Screenshot 3
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

  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    final name = await _authService.getSavedName();
    if (name != null) {
      setState(() => _adminName = name);
    }
    _fetchStats();
  }

  Future<void> _fetchStats() async {
    setState(() => _isLoadingStats = true);
    try {
      final userId = await _authService.getSavedUserId();
      final role = await _authService.getSavedRole();
      
      final response = await _apiService.get('dashboard?user_id=$userId&role=$role');
      if (response['success'] == true) {
        setState(() {
          _stats = response;
        });
      }
    } catch (e) {
      debugPrint('Error fetching dashboard stats: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoadingStats = false);
      }
    }
  }

  int get _activeNavIndex {
    switch (_currentView) {
      case 'dashboard':
        return 0;
      case 'manage_users':
      case 'add_user':
      case 'add_role':
        return 1;
      case 'patient_records':
        return 2;
      case 'add_patient':
        return 3;
      default:
        return 0;
    }
  }

  List<DrawerNavItem> get _menuItems => [
    DrawerNavItem(
      icon: Icons.dashboard_rounded,
      label: 'Dashboard',
      onTap: () {
        setState(() => _currentView = 'dashboard');
        _fetchStats();
      },
    ),
    DrawerNavItem(
      icon: Icons.people_outline_rounded,
      label: 'Manage Users',
      onTap: () => setState(() => _currentView = 'manage_users'),
    ),
    DrawerNavItem(
      icon: Icons.folder_outlined,
      label: 'Patient Records',
      onTap: () => setState(() => _currentView = 'patient_records'),
    ),
    DrawerNavItem(
      icon: Icons.person_add_outlined,
      label: 'Add Patient',
      onTap: () => setState(() => _currentView = 'add_patient'),
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        elevation: 0,
        leading: Builder(
          builder: (context) => IconButton(
            icon: const Icon(Icons.menu_rounded),
            onPressed: () => Scaffold.of(context).openDrawer(),
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
              icon: const Icon(Icons.notifications_outlined, size: 22),
              onPressed: () {},
            ),
          ),
        ],
      ),
      drawer: AppDrawer(
        roleName: 'Admin',
        menuItems: _menuItems,
        activeIndex: _activeNavIndex,
      ),
      body: _buildBody(),
    );
  }

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
          onBack: () => setState(() => _currentView = 'manage_users'),
        );
      case 'add_role':
        return AddRoleView(
          onBack: () => setState(() => _currentView = 'manage_users'),
        );
      case 'patient_records':
        return PatientRecordsView(
          onBackToDashboard: () => setState(() => _currentView = 'dashboard'),
        );
      case 'add_patient':
        return AddPatientView(
          onBack: () => setState(() => _currentView = 'patient_records'),
        );
      default:
        return _buildDashboard();
    }
  }

  Widget _buildDashboard() {
    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Welcome banner
          WelcomeBanner(
            panelLabel: 'ADMIN PANEL',
            title: 'Welcome back, $_adminName',
            subtitle: 'Quick overview of your clinic\'s activity today.',
            illustrationPath: 'lib/images/doctor-dashboard-illustration.svg',
          ),
          const SizedBox(height: AppSpacing.xl),

          // Stats grid
          LayoutBuilder(
            builder: (context, constraints) {
              final crossAxisCount = constraints.maxWidth > 500 ? 3 : 2;
              final cardWidth =
                  (constraints.maxWidth - (crossAxisCount - 1) * 12) /
                  crossAxisCount;

              return Wrap(
                spacing: 12,
                runSpacing: 12,
                children: [
                  SizedBox(
                    width: cardWidth,
                    child: StatCard(
                      icon: Icons.calendar_today_rounded,
                      iconColor: AppColors.accentLight,
                      iconBgColor: AppColors.iconBlueBg,
                      count: _isLoadingStats ? '...' : (_stats['total_appointments']?.toString() ?? '0'),
                      label: 'TOTAL APPOINTMENTS',
                    ),
                  ),
                  SizedBox(
                    width: cardWidth,
                    child: StatCard(
                      icon: Icons.assignment_outlined,
                      iconColor: AppColors.accentLight,
                      iconBgColor: AppColors.iconBlueBg,
                      count: _isLoadingStats ? '...' : (_stats['approved']?.toString() ?? '0'),
                      label: "APPROVED",
                    ),
                  ),
                  SizedBox(
                    width: cardWidth,
                    child: StatCard(
                      icon: Icons.people_outline_rounded,
                      iconColor: const Color(0xFF10B981),
                      iconBgColor: AppColors.iconGreenBg,
                      count: _isLoadingStats ? '...' : (_stats['total_users']?.toString() ?? '0'),
                      label: 'TOTAL USERS',
                    ),
                  ),
                  SizedBox(
                    width: cardWidth,
                    child: StatCard(
                      icon: Icons.medical_services_outlined,
                      iconColor: const Color(0xFF10B981),
                      iconBgColor: AppColors.iconGreenBg,
                      count: _isLoadingStats ? '...' : (_stats['total_doctors']?.toString() ?? '0'),
                      label: 'TOTAL DOCTORS',
                    ),
                  ),
                  SizedBox(
                    width: cardWidth,
                    child: StatCard(
                      icon: Icons.pending_actions_rounded,
                      iconColor: AppColors.warning,
                      iconBgColor: AppColors.iconAmberBg,
                      count: _isLoadingStats ? '...' : (_stats['pending']?.toString() ?? '0'),
                      label: 'PENDING REQUESTS',
                    ),
                  ),
                  SizedBox(
                    width: cardWidth,
                    child: const StatCard(
                      icon: Icons.badge_outlined,
                      iconColor: AppColors.accentLight,
                      iconBgColor: AppColors.iconBlueBg,
                      count: 'N/A',
                      label: 'SECRETARIES',
                    ),
                  ),
                ],
              );
            },
          ),
          const SizedBox(height: AppSpacing.xxl),

          // Notifications section
          const NotificationSection(),
        ],
      ),
    );
  }
}
