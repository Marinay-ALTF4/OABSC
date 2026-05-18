import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';

class PatientQueueView extends StatefulWidget {
  const PatientQueueView({super.key});

  @override
  State<PatientQueueView> createState() => _PatientQueueViewState();
}

class _PatientQueueViewState extends State<PatientQueueView> {
  final _api = ApiService();
  bool _loading = true;
  List _queue = [];

  @override
  void initState() {
    super.initState();
    _fetchQueue();
  }

  Future<void> _fetchQueue() async {
    setState(() => _loading = true);
    try {
      final r = await _api.get('admin/appointments');
      if (mounted && r['success'] == true) {
        final todayStr = DateFormat('yyyy-MM-dd').format(DateTime.now());
        final List all = List.from(r['all'] ?? []);
        
        // Filter appointments scheduled for today that are not cancelled
        setState(() {
          _queue = all.where((a) {
            final date = (a['appointment_date'] ?? '').toString();
            final status = (a['status'] ?? '').toString();
            return date == todayStr && status != 'cancelled';
          }).toList();
          
          // Sort by time
          _queue.sort((a, b) {
            final ta = (a['appointment_time'] ?? '').toString();
            final tb = (b['appointment_time'] ?? '').toString();
            return ta.compareTo(tb);
          });
        });
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
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
    final String currentDate = DateFormat('MMMM d, yyyy').format(DateTime.now());

    return RefreshIndicator(
      onRefresh: _fetchQueue,
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
                                DataColumn(label: Text('QUEUE #')),
                                DataColumn(label: Text('PATIENT')),
                                DataColumn(label: Text('DOCTOR')),
                                DataColumn(label: Text('REASON')),
                                DataColumn(label: Text('TIME')),
                                DataColumn(label: Text('STATUS')),
                              ],
                              rows: _queue.asMap().entries.map((e) {
                                final idx = e.key;
                                final row = e.value as Map;
                                final status = (row['status'] ?? '').toString();
                                Color sc = AppColors.textSecondary;
                                if (status == 'confirmed') sc = const Color(0xFF059669);
                                if (status == 'pending') sc = const Color(0xFFF59E0B);
                                if (status == 'completed') sc = AppColors.accent;

                                return DataRow(cells: [
                                  DataCell(Text('${idx + 1}', style: const TextStyle(fontWeight: FontWeight.w700))),
                                  DataCell(SizedBox(
                                    width: 110,
                                    child: Text(
                                      (row['patient_name'] ?? '—').toString(),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  )),
                                  DataCell(SizedBox(
                                    width: 90,
                                    child: Text(
                                      (row['doctor_name'] ?? '—').toString(),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  )),
                                  DataCell(SizedBox(
                                    width: 120,
                                    child: Text(
                                      (row['notes'] ?? row['reason'] ?? '—').toString(),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  )),
                                  DataCell(Text(_fmtTime(row['appointment_time']?.toString()))),
                                  DataCell(
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                      decoration: BoxDecoration(
                                        color: sc.withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Text(
                                        status,
                                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: sc),
                                      ),
                                    ),
                                  ),
                                ]);
                              }).toList(),
                            ),
                            
                            // Empty state indicator
                            if (_queue.isEmpty)
                              Container(
                                width: MediaQuery.of(context).size.width - 64,
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
