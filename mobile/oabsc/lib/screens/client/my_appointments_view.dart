import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

class MyAppointmentsView extends StatelessWidget {
  final VoidCallback onBack;
  final VoidCallback onBookNew;

  const MyAppointmentsView({
    super.key,
    required this.onBack,
    required this.onBookNew,
  });

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'My Appointments',
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w700,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Review and manage all your clinic appointments.',
                      style: TextStyle(
                        fontSize: 13,
                        color: AppColors.textSecondary.withValues(alpha: 0.8),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  OutlinedButton(
                    onPressed: onBack,
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.textPrimary,
                      side: const BorderSide(color: AppColors.border),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    ),
                    child: const Row(
                      children: [
                        Icon(Icons.arrow_back, size: 14),
                        SizedBox(width: 4),
                        Text('Dashboard', style: TextStyle(fontSize: 11)),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton(
                    onPressed: onBookNew,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1E40AF), // Darker blue like web
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    ),
                    child: const Row(
                      children: [
                        Icon(Icons.add, size: 14, color: Colors.white),
                        SizedBox(width: 4),
                        Text('Book New', style: TextStyle(fontSize: 11, color: Colors.white)),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.xxl),

          // Summary Badges
          Row(
            children: [
              _buildSummaryBadge('0 Upcoming', const Color(0xFFDBEAFE), const Color(0xFF1E40AF)),
              const SizedBox(width: 8),
              _buildSummaryBadge('0 Completed', const Color(0xFFDCFCE7), const Color(0xFF15803D)),
              const SizedBox(width: 8),
              _buildSummaryBadge('0 Cancelled', const Color(0xFFFEE2E2), const Color(0xFFB91C1C)),
            ],
          ),
          const SizedBox(height: AppSpacing.xxl),

          // Tabs UI
          DefaultTabController(
            length: 3,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const TabBar(
                  isScrollable: true,
                  tabAlignment: TabAlignment.start,
                  labelColor: AppColors.accent,
                  unselectedLabelColor: AppColors.textSecondary,
                  indicatorColor: AppColors.accent,
                  indicatorWeight: 3,
                  dividerColor: AppColors.border,
                  labelPadding: EdgeInsets.only(right: 32),
                  labelStyle: TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
                  tabs: [
                    Tab(child: Row(children: [Icon(Icons.calendar_today, size: 16), SizedBox(width: 8), Text('Upcoming  0')])),
                    Tab(child: Row(children: [Icon(Icons.check_circle_outline, size: 16), SizedBox(width: 8), Text('Completed  0')])),
                    Tab(child: Row(children: [Icon(Icons.cancel_outlined, size: 16), SizedBox(width: 8), Text('Cancelled  0')])),
                  ],
                ),
                const SizedBox(height: 60), // Space before empty state
                
                // Empty State
                Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: Colors.grey.withValues(alpha: 0.05),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(
                          Icons.event_busy_outlined,
                          size: 48,
                          color: Colors.grey.withValues(alpha: 0.3),
                        ),
                      ),
                      const SizedBox(height: 16),
                      const Text(
                        'No upcoming appointments',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'You have no scheduled appointments. Book one now!',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 13,
                          color: AppColors.textSecondary.withValues(alpha: 0.7),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryBadge(String text, Color bgColor, Color textColor) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        text,
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w700,
          color: textColor,
        ),
      ),
    );
  }
}
