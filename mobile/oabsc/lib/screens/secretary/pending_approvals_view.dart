import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';

class PendingApprovalsView extends StatefulWidget {
  const PendingApprovalsView({super.key});

  @override
  State<PendingApprovalsView> createState() => _PendingApprovalsViewState();
}

class _PendingApprovalsViewState extends State<PendingApprovalsView> {
  final _api = ApiService();
  bool _loading = true;
  List _pending = [];

  @override
  void initState() {
    super.initState();
    _fetchPending();
  }

  Future<void> _fetchPending() async {
    setState(() => _loading = true);
    try {
      final r = await _api.get('admin/appointments');
      if (mounted && r['success'] == true) {
        setState(() {
          _pending = List.from(r['pending'] ?? []);
        });
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  Future<void> _updateStatus(int id, String status) async {
    try {
      final response = await _api.post('admin/appointments/update-status', {
        'id': id,
        'status': status,
      });
      if (mounted && response['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Appointment $status successfully!'),
            backgroundColor: status == 'confirmed' ? const Color(0xFF059669) : AppColors.error,
          ),
        );
        _fetchPending();
      }
    } catch (_) {}
  }

  String _fmtTime(String? t) {
    if (t == null || t.isEmpty) return '—';
    final parts = t.split(':');
    if (parts.length < 2) return t;
    final hr = int.tryParse(parts[0]) ?? 0;
    final min = parts[1];
    final ampm = hr >= 12 ? 'PM' : 'AM';
    final hr12 = hr > 12 ? hr - 12 : (hr == 0 ? 12 : hr);
    return '$hr12:$min $ampm';
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _fetchPending,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
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

            if (_loading)
              const Center(child: Padding(padding: EdgeInsets.symmetric(vertical: 40), child: CircularProgressIndicator()))
            else ...[
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
                      child: Container(
                        decoration: const BoxDecoration(
                          borderRadius: BorderRadius.vertical(top: Radius.circular(12)),
                        ),
                        child: Column(
                          children: [
                            DataTable(
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
                              rows: _pending.asMap().entries.map((e) {
                                final idx = e.key;
                                final row = e.value as Map;
                                return DataRow(cells: [
                                  DataCell(Text('${idx + 1}', style: const TextStyle(fontWeight: FontWeight.w600))),
                                  DataCell(Text((row['patient_name'] ?? '—').toString())),
                                  DataCell(Text((row['doctor_name'] ?? '—').toString())),
                                  DataCell(Text((row['notes'] ?? row['reason'] ?? '—').toString())),
                                  DataCell(Text((row['appointment_date'] ?? '—').toString())),
                                  DataCell(Text(_fmtTime(row['appointment_time']?.toString()))),
                                  DataCell(
                                    Row(
                                      children: [
                                        IconButton(
                                          icon: const Icon(Icons.check_circle_outline, color: Color(0xFF059669), size: 20),
                                          onPressed: () => _updateStatus(int.tryParse(row['id']?.toString() ?? '') ?? 0, 'confirmed'),
                                          tooltip: 'Approve',
                                        ),
                                        IconButton(
                                          icon: const Icon(Icons.cancel_outlined, color: AppColors.error, size: 20),
                                          onPressed: () => _updateStatus(int.tryParse(row['id']?.toString() ?? '') ?? 0, 'cancelled'),
                                          tooltip: 'Reject',
                                        ),
                                      ],
                                    ),
                                  ),
                                ]);
                              }).toList(),
                            ),
                            
                            // Empty state indicator
                            if (_pending.isEmpty)
                              Container(
                                width: MediaQuery.of(context).size.width - 64,
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
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
