import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';

class ManageAppointmentsView extends StatefulWidget {
  const ManageAppointmentsView({super.key});

  @override
  State<ManageAppointmentsView> createState() => _ManageAppointmentsViewState();
}

class _ManageAppointmentsViewState extends State<ManageAppointmentsView>
    with SingleTickerProviderStateMixin {
  final _api = ApiService();
  late TabController _tabController;
  bool _loading = true;
  List _pending = [], _confirmed = [], _archived = [];
  int _totalActive = 0;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _fetch();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    try {
      final r = await _api.get('admin/appointments');
      if (mounted && r['success'] == true) {
        setState(() {
          _pending = List.from(r['pending'] ?? []);
          _confirmed = List.from(r['confirmed'] ?? []);
          _archived = List.from(r['archived'] ?? []);
          _totalActive = (_pending.length + _confirmed.length);
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
            content: Text('Appointment successfully updated to $status'),
            backgroundColor: status == 'confirmed' ? const Color(0xFF059669) : AppColors.error,
          ),
        );
        _fetch();
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
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Header section
        Padding(
          padding: const EdgeInsets.all(AppSpacing.lg),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE6F7EE),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(
                      Icons.calendar_month_outlined,
                      color: Color(0xFF166534),
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  const Text(
                    'Manage Appointments',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF166534),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                '$_totalActive active appointment${_totalActive == 1 ? '' : 's'} registered',
                style: const TextStyle(fontSize: 13, color: Color(0xFF166534), fontWeight: FontWeight.w600),
              ),
              const SizedBox(height: 16),
              // Tab Bar
              TabBar(
                controller: _tabController,
                labelColor: const Color(0xFF166534),
                unselectedLabelColor: AppColors.textSecondary,
                indicatorColor: const Color(0xFF166534),
                indicatorWeight: 2.5,
                labelStyle: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                tabs: [
                  Tab(text: 'Pending (${_pending.length})'),
                  Tab(text: 'Confirmed (${_confirmed.length})'),
                  Tab(text: 'Archive (${_archived.length})'),
                ],
              ),
              const Divider(height: 1),
            ],
          ),
        ),

        Expanded(
          child: _loading
              ? const Center(child: CircularProgressIndicator())
              : TabBarView(
                  controller: _tabController,
                  children: [
                    _buildTable(_pending, 'pending'),
                    _buildTable(_confirmed, 'confirmed'),
                    _buildTable(_archived, 'archived'),
                  ],
                ),
        ),
      ],
    );
  }

  Widget _buildTable(List rows, String tab) {
    if (rows.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.event_busy_outlined,
              size: 44,
              color: Colors.grey.withValues(alpha: 0.3),
            ),
            const SizedBox(height: 12),
            Text(
              'No $tab appointments found.',
              style: const TextStyle(fontSize: 13, color: AppColors.textSecondary, fontWeight: FontWeight.w500),
            ),
          ],
        ),
      );
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        children: [
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Container(
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(
                children: [
                  // Table Header
                  Container(
                    color: const Color(0xFFF0FDF4),
                    padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                    child: Row(
                      children: const [
                        SizedBox(width: 36, child: Text('#', style: _thStyle)),
                        SizedBox(width: 120, child: Text('PATIENT', style: _thStyle)),
                        SizedBox(width: 100, child: Text('DOCTOR', style: _thStyle)),
                        SizedBox(width: 90, child: Text('DATE', style: _thStyle)),
                        SizedBox(width: 70, child: Text('TIME', style: _thStyle)),
                        SizedBox(width: 90, child: Text('STATUS', style: _thStyle)),
                        SizedBox(width: 110, child: Text('ACTION', style: _thStyle)),
                      ],
                    ),
                  ),
                  const Divider(height: 1),
                  // Table Rows
                  ...rows.asMap().entries.map((e) {
                    final i = e.key;
                    final row = e.value as Map;
                    final status = (row['status'] ?? '').toString();
                    Color sc = AppColors.textSecondary;
                    if (status == 'confirmed') sc = const Color(0xFF059669);
                    if (status == 'pending') sc = const Color(0xFFF59E0B);

                    return Column(
                      children: [
                        Padding(
                          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                          child: Row(
                            children: [
                              SizedBox(
                                width: 36,
                                child: Text('${i + 1}', style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                              ),
                              SizedBox(
                                width: 120,
                                child: Text(
                                  (row['patient_name'] ?? '—').toString(),
                                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: AppColors.textPrimary),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              SizedBox(
                                width: 100,
                                child: Text(
                                  (row['doctor_name'] ?? '—').toString(),
                                  style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              SizedBox(
                                width: 90,
                                child: Text(
                                  (row['appointment_date'] ?? '—').toString(),
                                  style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                ),
                              ),
                              SizedBox(
                                width: 70,
                                child: Text(
                                  _fmtTime(row['appointment_time']?.toString()),
                                  style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                ),
                              ),
                              SizedBox(
                                width: 90,
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: sc.withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Text(
                                    status,
                                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: sc),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ),
                              SizedBox(
                                width: 110,
                                child: tab == 'archived'
                                    ? const SizedBox.shrink()
                                    : Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          if (status == 'pending') ...[
                                            IconButton(
                                              padding: EdgeInsets.zero,
                                              constraints: const BoxConstraints(),
                                              icon: const Icon(Icons.check_circle_outline, color: Color(0xFF059669), size: 20),
                                              onPressed: () => _updateStatus(int.tryParse(row['id']?.toString() ?? '') ?? 0, 'confirmed'),
                                              tooltip: 'Approve',
                                            ),
                                            const SizedBox(width: 12),
                                          ],
                                          IconButton(
                                            padding: EdgeInsets.zero,
                                            constraints: const BoxConstraints(),
                                            icon: const Icon(Icons.cancel_outlined, color: AppColors.error, size: 20),
                                            onPressed: () => _updateStatus(int.tryParse(row['id']?.toString() ?? '') ?? 0, 'cancelled'),
                                            tooltip: 'Cancel',
                                          ),
                                        ],
                                      ),
                              ),
                            ],
                          ),
                        ),
                        if (i < rows.length - 1) const Divider(height: 1),
                      ],
                    );
                  }),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  static const _thStyle = TextStyle(
    fontSize: 10,
    fontWeight: FontWeight.w700,
    color: Color(0xFF166534),
    letterSpacing: 0.5,
  );
}
