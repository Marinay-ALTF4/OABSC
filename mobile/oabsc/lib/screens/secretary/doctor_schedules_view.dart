import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

class DoctorSchedulesView extends StatelessWidget {
  const DoctorSchedulesView({super.key});

  final List<Map<String, String>> doctors = const [
    {
      'name': 'Dr. Doctor',
      'specialty': 'General',
      'education': 'MD',
      'experience': 'N/A experience',
      'phone': '',
    },
    {
      'name': 'Dr. Maria Santos',
      'specialty': 'General Practitioner',
      'education': 'MD, University of Santo Tomas',
      'experience': '8 years experience',
      'phone': '09171234567',
    },
    {
      'name': 'Dr. Jose Reyes',
      'specialty': 'Cardiologist',
      'education': 'MD, University of the Philippines',
      'experience': '12 years experience',
      'phone': '09189876543',
    },
    {
      'name': 'Dr. Ana Cruz',
      'specialty': 'Pediatrician',
      'education': 'MD, Ateneo School of Medicine',
      'experience': '5 years experience',
      'phone': '09201122334',
    },
    {
      'name': 'Dr. Ramon Garcia',
      'specialty': 'Dermatologist',
      'education': 'MD, Far Eastern University',
      'experience': '10 years experience',
      'phone': '09215566778',
    },
  ];

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
                  Icons.schedule_rounded,
                  color: Color(0xFF166534),
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              const Text(
                'Doctor Schedules',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF166534),
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.xl),

          // Doctors Grid
          LayoutBuilder(
            builder: (context, constraints) {
              final crossAxisCount = constraints.maxWidth > 600 ? 3 : 1;
              return GridView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: crossAxisCount,
                  crossAxisSpacing: 16,
                  mainAxisSpacing: 16,
                  mainAxisExtent: 180,
                ),
                itemCount: doctors.length,
                itemBuilder: (context, index) {
                  return _buildDoctorCard(doctors[index]);
                },
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildDoctorCard(Map<String, String> doc) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
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
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: const Color(0xFFE6F7EE),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.person_outline, size: 18, color: Color(0xFF166534)),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      doc['name']!,
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    Text(
                      doc['specialty']!,
                      style: const TextStyle(
                        fontSize: 12,
                        color: Color(0xFF166534),
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          _buildInfoRow(Icons.school_outlined, doc['education']!),
          const SizedBox(height: 6),
          _buildInfoRow(Icons.work_outline_rounded, doc['experience']!),
          if (doc['phone']!.isNotEmpty) ...[
            const SizedBox(height: 6),
            _buildInfoRow(Icons.phone_outlined, doc['phone']!),
          ],
        ],
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String text) {
    return Row(
      children: [
        Icon(icon, size: 14, color: AppColors.textSecondary),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            text,
            style: const TextStyle(
              fontSize: 12,
              color: AppColors.textSecondary,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }
}
