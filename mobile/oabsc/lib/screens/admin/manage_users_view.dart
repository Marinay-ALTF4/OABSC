import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';

class ManageUsersView extends StatefulWidget {
  final VoidCallback onAddUser;
  final VoidCallback onAddRole;

  const ManageUsersView({
    super.key,
    required this.onAddUser,
    required this.onAddRole,
  });

  @override
  State<ManageUsersView> createState() => _ManageUsersViewState();
}

class _ManageUsersViewState extends State<ManageUsersView> {
  final ApiService _apiService = ApiService();
  List<dynamic> _users = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchUsers();
  }

  Future<void> _fetchUsers() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.get('users');
      if (response['success'] == true) {
        setState(() {
          _users = response['users'] ?? [];
        });
      }
    } catch (e) {
      debugPrint('Error fetching users: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

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
                    onPressed: widget.onAddUser,
                    icon: const Icon(Icons.person_add_alt_1_outlined, size: 16),
                    label: const Text('Add User'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    ),
                  ),
                  OutlinedButton.icon(
                    onPressed: widget.onAddRole,
                    icon: const Icon(Icons.shield_outlined, size: 16),
                    label: const Text('Add Role'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.textPrimary,
                      side: const BorderSide(color: AppColors.border),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    ),
                  ),
                  IconButton(
                    onPressed: _fetchUsers,
                    icon: const Icon(Icons.refresh, size: 20),
                    tooltip: 'Refresh',
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
            child: _isLoading
                ? const Center(
                    child: Padding(
                      padding: EdgeInsets.all(40.0),
                      child: CircularProgressIndicator(),
                    ),
                  )
                : _users.isEmpty
                    ? const Center(
                        child: Padding(
                          padding: EdgeInsets.all(40.0),
                          child: Text('No users found'),
                        ),
                      )
                    : SingleChildScrollView(
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
                              DataColumn(label: Text('REGISTERED')),
                              DataColumn(label: Text('ACTIONS')),
                            ],
                            rows: _users.map((user) {
                              return _buildUserRow(
                                id: user['id'].toString(),
                                name: user['name'] ?? '',
                                email: user['email'] ?? '',
                                role: user['role'] ?? '',
                                date: user['created_at'] ?? '',
                              );
                            }).toList(),
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
      case 'assistant_admin':
      case 'assistant_secretary':
        roleBgColor = const Color(0xFFF3E8FF); // Light purple
        roleTextColor = const Color(0xFF6B21A8); // Dark purple
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
              role.replaceAll('_', ' ').toUpperCase(),
              style: TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w700,
                color: roleTextColor,
              ),
            ),
          ),
        ),
        DataCell(Text(date, style: const TextStyle(fontSize: 13, color: AppColors.textSecondary))),
        DataCell(
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              IconButton(
                icon: const Icon(Icons.edit_outlined, size: 18, color: AppColors.accentLight),
                onPressed: () {},
                constraints: const BoxConstraints(),
                padding: EdgeInsets.zero,
              ),
              const SizedBox(width: 8),
              IconButton(
                icon: const Icon(Icons.delete_outline, size: 18, color: Colors.red),
                onPressed: () {},
                constraints: const BoxConstraints(),
                padding: EdgeInsets.zero,
              ),
            ],
          ),
        ),
      ],
    );
  }
}
