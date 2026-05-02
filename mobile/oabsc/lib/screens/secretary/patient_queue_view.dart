import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../theme/app_theme.dart';

class PatientQueueView extends StatelessWidget {
  const PatientQueueView({super.key});

  @override
  Widget build(BuildContext context) {
    final String currentDate = DateFormat('MMMM d, yyyy').format(DateTime.now());

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
                  Icons.queue_rounded,
                  color: Color(0xFF166534),
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              Text(
                'Patient Queue — $currentDate',
                style: const TextStyle(
                  fontSize: 18,
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
                        DataColumn(label: Text('QUEUE #')),
                        DataColumn(label: Text('PATIENT')),
                        DataColumn(label: Text('DOCTOR')),
                        DataColumn(label: Text('REASON')),
                        DataColumn(label: Text('TIME')),
                        DataColumn(label: Text('STATUS')),
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
                        Icons.group_off_outlined,
                        size: 40,
                        color: Colors.grey.withValues(alpha: 0.3),
                      ),
                      const SizedBox(height: 12),
                      const Text(
                        'No patients in queue today.',
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
