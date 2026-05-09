import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../widgets/stat_card.dart';

class DoctorPatientRecordsView extends StatefulWidget {
  final VoidCallback onBack;

  const DoctorPatientRecordsView({super.key, required this.onBack});

  @override
  State<DoctorPatientRecordsView> createState() => _DoctorPatientRecordsViewState();
}

class _DoctorPatientRecordsViewState extends State<DoctorPatientRecordsView> {
  final _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildHeader(),
          const SizedBox(height: AppSpacing.xl),
          _buildStatGrid(),
          const SizedBox(height: AppSpacing.xxl),
          _buildPatientListSection(),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Patient Records',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 4),
              const Text(
                'View patients and the appointment history linked to your account.',
                style: TextStyle(
                  fontSize: 13,
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),
        ),
        OutlinedButton(
          onPressed: widget.onBack,
          style: OutlinedButton.styleFrom(
            side: const BorderSide(color: AppColors.border),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          ),
          child: const Text(
            'Back to Dashboard',
            style: TextStyle(fontSize: 11, color: AppColors.textPrimary, fontWeight: FontWeight.w600),
          ),
        ),
      ],
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
              child: const StatCard(
                icon: Icons.people_outline_rounded,
                iconColor: Color(0xFF3B82F6),
                iconBgColor: AppColors.iconBlueBg,
                count: '0',
                label: 'PATIENTS',
              ),
            ),
            SizedBox(
              width: w,
              child: const StatCard(
                icon: Icons.assignment_outlined,
                iconColor: Color(0xFF0D9488),
                iconBgColor: Color(0xFFF0FDFA),
                count: '0',
                label: 'APPOINTMENTS',
              ),
            ),
            SizedBox(
              width: w,
              child: const StatCard(
                icon: Icons.calendar_today_outlined,
                iconColor: Color(0xFF10B981),
                iconBgColor: AppColors.iconGreenBg,
                count: '0',
                label: 'TODAY',
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildPatientListSection() {
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
          Padding(
            padding: const EdgeInsets.all(AppSpacing.lg),
            child: Wrap(
              alignment: WrapAlignment.spaceBetween,
              crossAxisAlignment: WrapCrossAlignment.start,
              spacing: AppSpacing.md,
              runSpacing: AppSpacing.md,
              children: [
                const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.folder_open_outlined, size: 20, color: AppColors.accentLight),
                    SizedBox(width: 12),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Patient List',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
                        ),
                        Text(
                          'Select a patient to open their appointment history.',
                          style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
                        ),
                      ],
                    ),
                  ],
                ),
                SizedBox(
                  width: 250,
                  child: Column(
                    children: [
                      TextField(
                        controller: _searchController,
                        style: const TextStyle(fontSize: 13),
                        decoration: InputDecoration(
                          hintText: 'Search name, email, or phone...',
                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          isDense: true,
                          fillColor: const Color(0xFFF8FAFC),
                        ),
                      ),
                      const SizedBox(height: 8),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: () {},
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF2563EB),
                            padding: const EdgeInsets.symmetric(vertical: 8),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                          ),
                          child: const Text('Search', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          
          // Table Headers with horizontal scroll
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Container(
              width: 850, // Increased width to accommodate 800px children + padding
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.md),
              color: AppColors.background.withValues(alpha: 0.5),
              child: const Row(
                children: [
                  SizedBox(width: 40, child: Text('#', style: _tableHeaderStyle)),
                  SizedBox(width: 180, child: Text('PATIENT', style: _tableHeaderStyle)),
                  SizedBox(width: 200, child: Text('EMAIL', style: _tableHeaderStyle)),
                  SizedBox(width: 140, child: Text('PHONE', style: _tableHeaderStyle)),
                  SizedBox(width: 100, child: Text('APPOINTMENTS', style: _tableHeaderStyle)),
                  SizedBox(width: 140, child: Text('LATEST VISIT', style: _tableHeaderStyle)),
                ],
              ),
            ),
          ),

          // Empty State
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 80),
            child: Center(
              child: Column(
                children: [
                  const Text(
                    'No patient records found.',
                    style: TextStyle(
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

  static const _tableHeaderStyle = TextStyle(
    fontSize: 10,
    fontWeight: FontWeight.w700,
    color: AppColors.textSecondary,
    letterSpacing: 0.5,
  );
}
