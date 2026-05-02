import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

class PatientRecordsView extends StatelessWidget {
  final VoidCallback onBackToDashboard;

  const PatientRecordsView({super.key, required this.onBackToDashboard});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header section
          Wrap(
            alignment: WrapAlignment.spaceBetween,
            crossAxisAlignment: WrapCrossAlignment.center,
            spacing: AppSpacing.lg,
            runSpacing: AppSpacing.lg,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text(
                    'Patients',
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.w700,
                      color: AppColors.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Manage patient records: view list, search, and review appointment history.',
                    style: TextStyle(
                      fontSize: 13,
                      color: AppColors.textSecondary.withValues(alpha: 0.8),
                    ),
                  ),
                ],
              ),
              OutlinedButton.icon(
                onPressed: onBackToDashboard,
                icon: const Icon(Icons.arrow_back, size: 16),
                label: const Text('Dashboard'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.textPrimary,
                  side: const BorderSide(color: AppColors.border),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.xxl),

          // Section Label
          const Text(
            'MANAGE PATIENT RECORDS',
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: AppColors.textSecondary,
              letterSpacing: 1.2,
            ),
          ),
          const SizedBox(height: AppSpacing.md),

          // Cards Grid
          LayoutBuilder(
            builder: (context, constraints) {
              final crossAxisCount = constraints.maxWidth > 600 ? 4 : (constraints.maxWidth > 400 ? 2 : 1);
              final cardWidth = (constraints.maxWidth - (crossAxisCount - 1) * AppSpacing.md) / crossAxisCount;

              return Wrap(
                spacing: AppSpacing.md,
                runSpacing: AppSpacing.md,
                children: [
                  SizedBox(
                    width: cardWidth,
                    child: _buildRecordCard(
                      category: 'RECORDS',
                      title: 'View Patient List',
                      description: 'See all patients registered in the clinic.',
                      buttonText: 'Open',
                      icon: Icons.people_alt,
                      iconColor: const Color(0xFF0F766E), // Dark Teal
                      iconBgColor: const Color(0xFFCCFBF1), // Light Teal
                      isPrimaryAction: true,
                    ),
                  ),
                  SizedBox(
                    width: cardWidth,
                    child: _buildRecordCard(
                      category: 'SEARCH',
                      title: 'Search Patient',
                      description: 'Quickly find a patient by name or ID.',
                      buttonText: 'Search (soon)',
                      icon: Icons.search,
                      iconColor: const Color(0xFF0369A1), // Dark Blue
                      iconBgColor: const Color(0xFFE0F2FE), // Light Blue
                    ),
                  ),
                  SizedBox(
                    width: cardWidth,
                    child: _buildRecordCard(
                      category: 'HISTORY',
                      title: 'Appointment History',
                      description: 'Review a patient\'s visit and booking history.',
                      buttonText: 'Open',
                      icon: Icons.access_time_rounded,
                      iconColor: const Color(0xFF0F766E), // Dark Teal
                      iconBgColor: const Color(0xFFCCFBF1), // Light Teal
                    ),
                  ),
                  SizedBox(
                    width: cardWidth,
                    child: _buildRecordCard(
                      category: 'EDIT',
                      title: 'Edit Patient Info',
                      description: 'Update contact details and basic information.',
                      buttonText: 'Open',
                      icon: Icons.edit_outlined,
                      iconColor: const Color(0xFF0369A1), // Dark Blue
                      iconBgColor: const Color(0xFFE0F2FE), // Light Blue
                    ),
                  ),
                ],
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildRecordCard({
    required String category,
    required String title,
    required String description,
    required String buttonText,
    required IconData icon,
    required Color iconColor,
    required Color iconBgColor,
    bool isPrimaryAction = false,
  }) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.xl),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: iconBgColor,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: iconColor, size: 22),
          ),
          const SizedBox(height: AppSpacing.md),
          Text(
            category,
            style: const TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w700,
              color: AppColors.textSecondary,
              letterSpacing: 1.2,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            title,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: 4),
          SizedBox(
            height: 40, // Fixed height for consistent alignment
            child: Text(
              description,
              style: const TextStyle(
                fontSize: 13,
                color: AppColors.textSecondary,
                height: 1.3,
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          const SizedBox(height: AppSpacing.lg),
          Align(
            alignment: Alignment.centerLeft,
            child: isPrimaryAction
                ? ElevatedButton(
                    onPressed: () {},
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary, // Dark navy
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                      minimumSize: const Size(0, 36),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                    ),
                    child: Text(buttonText, style: const TextStyle(fontSize: 12)),
                  )
                : OutlinedButton(
                    onPressed: () {},
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.textPrimary,
                      side: const BorderSide(color: AppColors.border),
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                      minimumSize: const Size(0, 36),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                    ),
                    child: Text(buttonText, style: const TextStyle(fontSize: 12)),
                  ),
          ),
        ],
      ),
    );
  }
}
