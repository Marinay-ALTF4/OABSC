import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../widgets/stat_card.dart';

class DoctorQueueView extends StatefulWidget {
  final VoidCallback onBack;

  const DoctorQueueView({super.key, required this.onBack});

  @override
  State<DoctorQueueView> createState() => _DoctorQueueViewState();
}

class _DoctorQueueViewState extends State<DoctorQueueView> {
  // Mock data for now as per the screenshot
  final int _todayCount = 0;
  final int _upcomingCount = 0;
  final int _totalCount = 0;

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildHeaderBanner(),
          const SizedBox(height: AppSpacing.xl),
          _buildStatGrid(),
          const SizedBox(height: AppSpacing.xxl),
          _buildScheduleSection(
            title: "Today's Schedule",
            subtitle: "Appointments scheduled for May 9, 2026",
            count: _todayCount,
            columns: ['TIME', 'PATIENT', 'REASON', 'STATUS'],
            emptyMessage: "No appointments scheduled for today.",
            emptyIcon: Icons.event_busy_outlined,
          ),
          const SizedBox(height: AppSpacing.xl),
          _buildScheduleSection(
            title: "Upcoming Schedule",
            subtitle: "Future approved and pending appointments",
            count: _upcomingCount,
            columns: ['DATE', 'TIME', 'PATIENT', 'REASON', 'STATUS'],
            emptyMessage: "No upcoming appointments found.",
            emptyIcon: Icons.calendar_month_outlined,
          ),
        ],
      ),
    );
  }

  Widget _buildHeaderBanner() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(AppSpacing.xxl),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF3B82F6), Color(0xFF2563EB)],
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF2563EB).withValues(alpha: 0.2),
            blurRadius: 15,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'DOCTOR QUEUE',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                    color: Colors.white70,
                    letterSpacing: 1.2,
                  ),
                ),
                const SizedBox(height: 8),
                const Text(
                  "Today's Queue",
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.w800,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 8),
                const Text(
                  "View and organize today's schedule alongside upcoming appointments.",
                  style: TextStyle(
                    fontSize: 13,
                    color: Colors.white,
                    fontWeight: FontWeight.w400,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: AppSpacing.md),
          ElevatedButton(
            onPressed: widget.onBack,
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.white,
              foregroundColor: const Color(0xFF2563EB),
              elevation: 0,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
            child: const Text(
              'Back to Dashboard',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatGrid() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final cols = constraints.maxWidth > 600 ? 3 : 1;
        final w = (constraints.maxWidth - (cols - 1) * 12) / cols;
        
        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: [
            SizedBox(
              width: w,
              child: StatCard(
                icon: Icons.calendar_today_rounded,
                iconColor: AppColors.accentLight,
                iconBgColor: AppColors.iconBlueBg,
                count: _todayCount.toString(),
                label: 'TODAY',
              ),
            ),
            SizedBox(
              width: w,
              child: StatCard(
                icon: Icons.access_time_rounded,
                iconColor: const Color(0xFF0D9488),
                iconBgColor: const Color(0xFFF0FDFA),
                count: _upcomingCount.toString(),
                label: 'UPCOMING',
              ),
            ),
            SizedBox(
              width: w,
              child: StatCard(
                icon: Icons.format_list_bulleted_rounded,
                iconColor: const Color(0xFF10B981),
                iconBgColor: AppColors.iconGreenBg,
                count: _totalCount.toString(),
                label: 'TOTAL',
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildScheduleSection({
    required String title,
    required String subtitle,
    required int count,
    required List<String> columns,
    required String emptyMessage,
    required IconData emptyIcon,
  }) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Section Header
          Padding(
            padding: const EdgeInsets.all(AppSpacing.lg),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: AppColors.iconBlueBg,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Icon(
                        emptyIcon,
                        size: 20,
                        color: AppColors.accentLight,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          title,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        Text(
                          subtitle,
                          style: const TextStyle(
                            fontSize: 11,
                            color: AppColors.textSecondary,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFF3B82F6),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    count.toString(),
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
                    ),
                  ),
                ),
              ],
            ),
          ),
          
          // Table Headers
          Container(
            padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.md),
            color: AppColors.background.withValues(alpha: 0.5),
            child: Row(
              children: columns.map((col) => Expanded(
                child: Text(
                  col,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: AppColors.textSecondary,
                    letterSpacing: 0.5,
                  ),
                ),
              )).toList(),
            ),
          ),
          
          // Content
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 60),
            child: Center(
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.grey.withValues(alpha: 0.05),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Icon(
                      emptyIcon,
                      size: 24,
                      color: Colors.grey.withValues(alpha: 0.3),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    emptyMessage,
                    style: const TextStyle(
                      fontSize: 14,
                      color: AppColors.textSecondary,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
