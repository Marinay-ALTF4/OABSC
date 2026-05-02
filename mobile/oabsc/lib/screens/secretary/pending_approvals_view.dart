import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

class PendingApprovalsView extends StatelessWidget {
  const PendingApprovalsView({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header section
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFFE6F7EE),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(
                  Icons.check_circle_outline_rounded,
                  color: Color(0xFF166534),
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              const Text(
                'Pending Approvals',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF166534),
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.xl),

          // Table container
          Container(
            width: double.infinity,
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
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
              children: [
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: ConstrainedBox(
                    constraints: BoxConstraints(
                      minWidth: MediaQuery.of(context).size.width - (AppSpacing.lg * 2),
                    ),
                    child: DataTable(
                      headingRowHeight: 50,
                      dataRowMaxHeight: 60,
                      headingRowColor: WidgetStateProperty.all(const Color(0xFFF0FDF4)),
                      headingTextStyle: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF166534),
                        letterSpacing: 1.0,
                      ),
                      columns: const [
                        DataColumn(label: Text('#')),
                        DataColumn(label: Text('PATIENT')),
                        DataColumn(label: Text('DOCTOR')),
                        DataColumn(label: Text('REASON')),
                        DataColumn(label: Text('DATE')),
                        DataColumn(label: Text('TIME')),
                        DataColumn(label: Text('ACTION')),
                      ],
                      rows: const [], // Empty table
                    ),
                  ),
                ),
                // Empty state indicator
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(vertical: 40),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.approval_rounded,
                        size: 40,
                        color: Colors.grey.withValues(alpha: 0.3),
                      ),
                      const SizedBox(height: 12),
                      const Text(
                        'No pending appointments.',
                        style: TextStyle(
                          fontSize: 13,
                          color: AppColors.textSecondary,
                          fontWeight: FontWeight.w500,
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
}
