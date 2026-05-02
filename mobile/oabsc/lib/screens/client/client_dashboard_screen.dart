import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/constants.dart';
import '../../widgets/quick_access_card.dart';
import '../../widgets/welcome_banner.dart';
import '../../widgets/notification_section.dart';
import '../../services/auth_service.dart';
import 'book_appointment_view.dart';
import 'my_appointments_view.dart';
import 'profile_settings_view.dart';

/// Client Dashboard screen with sub-views
class ClientDashboardScreen extends StatefulWidget {
  const ClientDashboardScreen({super.key});

  @override
  State<ClientDashboardScreen> createState() => _ClientDashboardScreenState();
}

class _ClientDashboardScreenState extends State<ClientDashboardScreen> {
  String _currentView = 'dashboard';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        elevation: 0,
        automaticallyImplyLeading: false,
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
          IconButton(
            icon: const Icon(Icons.notifications_outlined, size: 22),
            onPressed: () {},
          ),
          PopupMenuButton<String>(
            offset: const Offset(0, 50),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            child: Container(
              margin: const EdgeInsets.only(right: AppSpacing.sm),
              padding: const EdgeInsets.symmetric(
                horizontal: AppSpacing.md,
                vertical: AppSpacing.xs,
              ),
              decoration: BoxDecoration(
                color: AppColors.background,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const CircleAvatar(
                    radius: 14,
                    backgroundColor: Color(0xFF10B981),
                    child: Text(
                      'C',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: Colors.white,
                      ),
                    ),
                  ),
                  const SizedBox(width: AppSpacing.xs),
                  const Text(
                    'Client',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                      color: AppColors.textPrimary,
                    ),
                  ),
                  const Icon(
                    Icons.keyboard_arrow_down,
                    size: 18,
                    color: AppColors.textSecondary,
                  ),
                ],
              ),
            ),
            onSelected: (value) async {
              if (value == 'logout') {
                await AuthService().logout();
                if (context.mounted) {
                  Navigator.of(context).pushNamedAndRemoveUntil(
                    AppRoutes.login,
                    (route) => false,
                  );
                }
              } else if (value == 'settings') {
                setState(() => _currentView = 'settings');
              }
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'settings',
                child: Row(
                  children: [
                    Icon(Icons.settings_outlined, size: 18),
                    SizedBox(width: 8),
                    Text('Settings'),
                  ],
                ),
              ),
              const PopupMenuItem(
                value: 'logout',
                child: Row(
                  children: [
                    Icon(Icons.logout, size: 18, color: AppColors.error),
                    SizedBox(width: 8),
                    Text('Logout', style: TextStyle(color: AppColors.error)),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    switch (_currentView) {
      case 'dashboard':
        return _buildDashboard();
      case 'book_appointment':
        return BookAppointmentView(
          onBack: () => setState(() => _currentView = 'dashboard'),
          onViewAppointments: () => setState(() => _currentView = 'my_appointments'),
        );
      case 'my_appointments':
        return MyAppointmentsView(
          onBack: () => setState(() => _currentView = 'dashboard'),
          onBookNew: () => setState(() => _currentView = 'book_appointment'),
        );
      case 'settings':
        return ProfileSettingsView(
          onBack: () => setState(() => _currentView = 'dashboard'),
        );
      default:
        return _buildDashboard();
    }
  }

  Widget _buildDashboard() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Welcome banner
          const WelcomeBanner(
            panelLabel: 'PATIENT PORTAL',
            title: 'Welcome, Client',
            subtitle:
                'From here you can request or review your appointments.',
            illustrationPath: 'lib/images/doctor-dashboard-illustration.svg',
          ),
          const SizedBox(height: AppSpacing.xl),

          // Quick Access section label
          const Text(
            'QUICK ACCESS',
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: AppColors.textSecondary,
              letterSpacing: 1.2,
            ),
          ),
          const SizedBox(height: AppSpacing.md),

          // Quick access cards
          QuickAccessCard(
            categoryLabel: 'BOOK',
            title: 'New Appointment',
            description:
                'Choose your doctor, date, and time that works best for you.',
            buttonText: 'Book Appointment',
            icon: Icons.calendar_today_outlined,
            iconColor: AppColors.accentLight,
            iconBgColor: AppColors.iconBlueBg,
            isPrimary: true,
            onButtonTap: () => setState(() => _currentView = 'book_appointment'),
          ),
          const SizedBox(height: AppSpacing.md),

          QuickAccessCard(
            categoryLabel: 'MY VISITS',
            title: 'My Appointments',
            description:
                'View or cancel your upcoming visits and see past appointments.',
            buttonText: 'View Appointments',
            icon: Icons.assignment_outlined,
            iconColor: const Color(0xFF10B981),
            iconBgColor: AppColors.iconGreenBg,
            onButtonTap: () => setState(() => _currentView = 'my_appointments'),
          ),
          const SizedBox(height: AppSpacing.md),

          QuickAccessCard(
            categoryLabel: 'MESSAGING',
            title: 'Chat with Clinic',
            description:
                'Ask questions or send a message to your doctor or clinic staff before your visit.',
            buttonText: 'Open Chat',
            icon: Icons.chat_bubble_outline_rounded,
            iconColor: const Color(0xFF8B5CF6),
            iconBgColor: const Color(0xFFF3E8FF),
            onButtonTap: () {},
          ),
          const SizedBox(height: AppSpacing.xxl),

          // Notifications section
          const NotificationSection(),
          const SizedBox(height: AppSpacing.lg),
        ],
      ),
    );
  }
}
