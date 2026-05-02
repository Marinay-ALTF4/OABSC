import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

class ManageUsersView extends StatelessWidget {
  final VoidCallback onAddUser;
  final VoidCallback onAddRole;

  const ManageUsersView({
    super.key,
    required this.onAddUser,
    required this.onAddRole,
  });

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
                    'Users List',
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.w700,
                      color: AppColors.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'All registered users including admin accounts.',
                    style: TextStyle(
                      fontSize: 13,
                      color: AppColors.textSecondary.withValues(alpha: 0.8),
                    ),
                  ),
                ],
              ),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  ElevatedButton.icon(
                    onPressed: onAddUser,
                    icon: const Icon(Icons.person_add_alt_1_outlined, size: 16),
                    label: const Text('Add User'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    ),
                  ),
                  OutlinedButton.icon(
                    onPressed: onAddRole,
                    icon: const Icon(Icons.shield_outlined, size: 16),
                    label: const Text('Add Role'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.textPrimary,
                      side: const BorderSide(color: AppColors.border),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.xxl),

          // Table container
          Container(
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.border),
            ),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  minWidth: MediaQuery.of(context).size.width - (AppSpacing.lg * 2),
                ),
                child: DataTable(
                  headingRowColor: WidgetStateProperty.all(AppColors.background.withValues(alpha: 0.5)),
                  dataRowMaxHeight: 65,
                  dataRowMinHeight: 60,
                  headingTextStyle: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: AppColors.textSecondary,
                    letterSpacing: 1.0,
                  ),
                  columns: const [
                    DataColumn(label: Text('ID')),
                    DataColumn(label: Text('NAME')),
                    DataColumn(label: Text('EMAIL')),
                    DataColumn(label: Text('ROLE')),
                    DataColumn(label: Text('STATUS')),
                    DataColumn(label: Text('REGISTERED')),
                    DataColumn(label: Text('ACTIONS')),
                  ],
                  rows: [
                    _buildUserRow(id: '8', name: 'Client', email: 'client@example.com', role: 'Client', date: '2026-05-02 02:56:40'),
                    _buildUserRow(id: '7', name: 'Ramon Garcia', email: 'dr.garcia@example.com', role: 'Doctor', date: '2026-05-02 02:56:40'),
                    _buildUserRow(id: '6', name: 'Ana Cruz', email: 'dr.cruz@example.com', role: 'Doctor', date: '2026-05-02 02:56:40'),
                    _buildUserRow(id: '5', name: 'Jose Reyes', email: 'dr.reyes@example.com', role: 'Doctor', date: '2026-05-02 02:56:40'),
                    _buildUserRow(id: '4', name: 'Maria Santos', email: 'dr.santos@example.com', role: 'Doctor', date: '2026-05-02 02:56:40'),
                    _buildUserRow(id: '3', name: 'Doctor', email: 'doctor@example.com', role: 'Doctor', date: '2026-05-02 02:56:28'),
                    _buildUserRow(id: '2', name: 'Secretary', email: 'secretary@example.com', role: 'Secretary', date: '2026-05-02 02:56:28'),
                    _buildUserRow(id: '1', name: 'Admin', email: 'admin@example.com', role: 'Admin', date: '2026-05-02 02:56:28', isYou: true),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  DataRow _buildUserRow({
    required String id,
    required String name,
    required String email,
    required String role,
    required String date,
    bool isYou = false,
  }) {
    Color roleBgColor;
    Color roleTextColor;
    
    switch (role.toLowerCase()) {
      case 'client':
        roleBgColor = const Color(0xFFDCFCE7); // Light green
        roleTextColor = const Color(0xFF166534); // Dark green
        break;
      case 'doctor':
        roleBgColor = const Color(0xFFCCFBF1); // Light teal
        roleTextColor = const Color(0xFF115E59); // Dark teal
        break;
      case 'secretary':
        roleBgColor = const Color(0xFFDBEAFE); // Light blue
        roleTextColor = const Color(0xFF1E40AF); // Dark blue
        break;
      case 'admin':
        roleBgColor = const Color(0xFFFEE2E2); // Light red
        roleTextColor = const Color(0xFF991B1B); // Dark red
        break;
      default:
        roleBgColor = AppColors.border;
        roleTextColor = AppColors.textPrimary;
    }

    return DataRow(
      cells: [
        DataCell(Text(id, style: const TextStyle(fontWeight: FontWeight.w500))),
        DataCell(
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(name, style: const TextStyle(fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
              if (isYou) ...[
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF3C7),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: const Text(
                    'You',
                    style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: Color(0xFF92400E)),
                  ),
                ),
              ],
            ],
          ),
        ),
        DataCell(Text(email, style: const TextStyle(color: AppColors.textSecondary))),
        DataCell(
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: roleBgColor,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              role,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: roleTextColor,
              ),
            ),
          ),
        ),
        DataCell(
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: const Color(0xFFDCFCE7),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Text(
              'Active',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: Color(0xFF166534),
              ),
            ),
          ),
        ),
        DataCell(Text(date, style: const TextStyle(fontSize: 13, color: AppColors.textSecondary))),
        DataCell(
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              OutlinedButton.icon(
                onPressed: () {},
                icon: const Icon(Icons.edit_outlined, size: 14),
                label: const Text('Edit'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.accentLight,
                  side: BorderSide(color: AppColors.accentLight.withValues(alpha: 0.3)),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 0),
                  minimumSize: const Size(0, 32),
                ),
              ),
              const SizedBox(width: 8),
              TextButton.icon(
                onPressed: () {},
                icon: const Icon(Icons.delete_outline, size: 14),
                label: const Text('Delete'),
                style: TextButton.styleFrom(
                  foregroundColor: const Color(0xFFDC2626),
                  backgroundColor: const Color(0xFFFEF2F2),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 0),
                  minimumSize: const Size(0, 32),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
