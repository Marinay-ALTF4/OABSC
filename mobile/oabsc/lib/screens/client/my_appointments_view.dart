import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';

class MyAppointmentsView extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback onBookNew;
  const MyAppointmentsView({super.key, required this.onBack, required this.onBookNew});
  @override
  State<MyAppointmentsView> createState() => _MyAppointmentsViewState();
}

class _MyAppointmentsViewState extends State<MyAppointmentsView> with SingleTickerProviderStateMixin {
  final ApiService _apiService = ApiService();
  final AuthService _authService = AuthService();
  List<Map<String, dynamic>> _appointments = [];
  bool _isLoading = true;
  late TabController _tabController;
  static const int _maxCancellations = 3;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _loadAppointments();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadAppointments() async {
    setState(() => _isLoading = true);
    try {
      final userId = await _authService.getSavedUserId();
      final response = await _apiService.get('appointments?user_id=$userId');
      if (mounted) {
        setState(() {
          _appointments = List<Map<String, dynamic>>.from(response['appointments'] ?? []);
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _cancelAppointment(dynamic id) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancel Appointment?'),
        content: const Text('Are you sure you want to cancel this appointment?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Keep It')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Yes, Cancel', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    try {
      final response = await _apiService.post('appointments/cancel', {'id': id});
      if (response['success'] == true) {
        _loadAppointments();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Appointment cancelled'), backgroundColor: AppColors.success),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: AppColors.error),
        );
      }
    }
  }

  Future<void> _rescheduleAppointment(Map<String, dynamic> appt) async {
    String? newDate;
    String? newTime;
    final times = ['09:00','09:30','10:00','10:30','11:00','11:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30'];

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setBS) => Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom, left: 20, right: 20, top: 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Reschedule Appointment', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
              const SizedBox(height: 4),
              Text('Doctor: ${appt['doctor_name'] ?? '-'}', style: const TextStyle(color: AppColors.textSecondary, fontSize: 13)),
              const SizedBox(height: 20),
              const Text('New Date', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
              const SizedBox(height: 8),
              GestureDetector(
                onTap: () async {
                  final d = await showDatePicker(
                    context: ctx,
                    initialDate: DateTime.now().add(const Duration(days: 1)),
                    firstDate: DateTime.now(),
                    lastDate: DateTime.now().add(const Duration(days: 365)),
                  );
                  if (d != null) setBS(() => newDate = '${d.day.toString().padLeft(2,'0')}/${d.month.toString().padLeft(2,'0')}/${d.year}');
                },
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  decoration: BoxDecoration(
                    border: Border.all(color: AppColors.border),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.calendar_today_outlined, size: 18, color: AppColors.textSecondary),
                      const SizedBox(width: 8),
                      Text(newDate ?? 'dd/mm/yyyy', style: TextStyle(color: newDate == null ? AppColors.textHint : AppColors.textPrimary)),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              const Text('New Time', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
              const SizedBox(height: 8),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: times.map((t) {
                  final sel = newTime == t;
                  return GestureDetector(
                    onTap: () => setBS(() => newTime = t),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                      decoration: BoxDecoration(
                        color: sel ? AppColors.accent : Colors.white,
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(color: sel ? AppColors.accent : AppColors.border),
                      ),
                      child: Text(t, style: TextStyle(fontSize: 12, color: sel ? Colors.white : AppColors.textPrimary, fontWeight: FontWeight.w500)),
                    ),
                  );
                }).toList(),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(backgroundColor: AppColors.accent, padding: const EdgeInsets.symmetric(vertical: 14)),
                  onPressed: newDate == null || newTime == null ? null : () async {
                    Navigator.pop(ctx);
                    final parts = newDate!.split('/');
                    final formatted = '${parts[2]}-${parts[1]}-${parts[0]}';
                    final res = await _apiService.post('appointments/reschedule', {
                      'id': appt['id'],
                      'appointment_date': formatted,
                      'appointment_time': newTime,
                    });
                    if (mounted) {
                      if (res['success'] == true) {
                        _loadAppointments();
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Appointment rescheduled'), backgroundColor: AppColors.success),
                        );
                      } else {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text(res['message'] ?? 'Failed'), backgroundColor: AppColors.error),
                        );
                      }
                    }
                  },
                  child: const Text('Confirm Reschedule', style: TextStyle(color: Colors.white)),
                ),
              ),
              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) return const Center(child: CircularProgressIndicator());

    final upcoming = _appointments.where((a) => a['status'] == 'pending' || a['status'] == 'approved').toList();
    final completed = _appointments.where((a) => a['status'] == 'completed').toList();
    final cancelled = _appointments.where((a) => a['status'] == 'cancelled').toList();
    final cancelUsed = cancelled.length.clamp(0, _maxCancellations);
    final cancelRemaining = (_maxCancellations - cancelUsed).clamp(0, _maxCancellations);

    return Column(
      children: [
        // Header area
        Container(
          color: AppColors.surface,
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Title + buttons row
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('MY VISITS', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.accent, letterSpacing: 1.2)),
                        const SizedBox(height: 2),
                        const Text('My Appointments', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                        const SizedBox(height: 4),
                        RichText(
                          text: const TextSpan(
                            style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
                            children: [
                              TextSpan(text: 'Review and '),
                              TextSpan(text: 'manage all your', style: TextStyle(color: AppColors.accent)),
                              TextSpan(text: ' clinic appointments.'),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      OutlinedButton.icon(
                        onPressed: widget.onBack,
                        icon: const Icon(Icons.arrow_back, size: 13),
                        label: const Text('Dashboard', style: TextStyle(fontSize: 11)),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.textPrimary,
                          side: const BorderSide(color: AppColors.border),
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        ),
                      ),
                      const SizedBox(height: 6),
                      ElevatedButton.icon(
                        onPressed: widget.onBookNew,
                        icon: const Icon(Icons.add, size: 13, color: Colors.white),
                        label: const Text('Book Now', style: TextStyle(fontSize: 11, color: Colors.white)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF4F46E5),
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Stats row
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.background,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.border),
                ),
                child: Row(
                  children: [
                    _buildStatBlock(upcoming.length.toString(), 'Upcoming', Icons.calendar_today_outlined, AppColors.accent),
                    _buildStatDivider(),
                    _buildStatBlock(completed.length.toString(), 'Completed', Icons.check_circle_outline, AppColors.success),
                    _buildStatDivider(),
                    _buildStatBlock(cancelled.length.toString(), 'Cancelled', Icons.cancel_outlined, AppColors.error),
                    _buildStatDivider(),
                    Expanded(
                      flex: 2,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Flexible(
                                child: Text('Weekly Cancellation Attempts', style: TextStyle(fontSize: 10, color: AppColors.textSecondary, fontWeight: FontWeight.w500)),
                              ),
                              const SizedBox(width: 4),
                              Text(
                                '$cancelRemaining / $_maxCancellations remaining',
                                style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.success),
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Row(
                            children: List.generate(_maxCancellations, (i) => Expanded(
                              child: Container(
                                height: 6,
                                margin: EdgeInsets.only(right: i < _maxCancellations - 1 ? 3 : 0),
                                decoration: BoxDecoration(
                                  color: i < cancelUsed ? AppColors.error : AppColors.success,
                                  borderRadius: BorderRadius.circular(3),
                                ),
                              ),
                            )),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // Tab bar
              TabBar(
                controller: _tabController,
                isScrollable: true,
                tabAlignment: TabAlignment.start,
                labelColor: AppColors.accent,
                unselectedLabelColor: AppColors.textSecondary,
                indicatorColor: AppColors.accent,
                indicatorWeight: 3,
                dividerColor: AppColors.border,
                labelPadding: const EdgeInsets.only(right: 24),
                labelStyle: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                tabs: [
                  Tab(child: Row(children: [const Icon(Icons.calendar_today, size: 15), const SizedBox(width: 6), Text('Upcoming  ${upcoming.length}')])),
                  Tab(child: Row(children: [const Icon(Icons.check_circle_outline, size: 15), const SizedBox(width: 6), Text('Completed  ${completed.length}')])),
                  Tab(child: Row(children: [const Icon(Icons.cancel_outlined, size: 15), const SizedBox(width: 6), Text('Cancelled  ${cancelled.length}')])),
                ],
              ),
            ],
          ),
        ),

        // Tab content
        Expanded(
          child: TabBarView(
            controller: _tabController,
            children: [
              _buildList(upcoming, 'upcoming'),
              _buildList(completed, 'completed'),
              _buildList(cancelled, 'cancelled'),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildStatBlock(String count, String label, IconData icon, Color color) {
    return Expanded(
      child: Column(
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(height: 4),
          Text(count, style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: color)),
          Text(label, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
        ],
      ),
    );
  }

  Widget _buildStatDivider() => Container(
    width: 1, height: 50, color: AppColors.border, margin: const EdgeInsets.symmetric(horizontal: 4),
  );

  Widget _buildList(List<Map<String, dynamic>> items, String type) {
    if (items.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.event_busy_outlined, size: 52, color: Colors.grey.withValues(alpha: 0.3)),
            const SizedBox(height: 12),
            Text('No $type appointments', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.textSecondary)),
          ],
        ),
      );
    }

    // Group upcoming by status
    if (type == 'upcoming') {
      final pending = items.where((a) => a['status'] == 'pending').toList();
      final approved = items.where((a) => a['status'] == 'approved').toList();
      return RefreshIndicator(
        onRefresh: _loadAppointments,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            if (pending.isNotEmpty) ...[
              _buildGroupHeader('Pending Confirmation', pending.length, const Color(0xFFF59E0B), Icons.hourglass_empty_outlined),
              const SizedBox(height: 8),
              ...pending.map((a) => _buildCard(a, type)),
            ],
            if (approved.isNotEmpty) ...[
              if (pending.isNotEmpty) const SizedBox(height: 16),
              _buildGroupHeader('Confirmed', approved.length, AppColors.success, Icons.check_circle_outline),
              const SizedBox(height: 8),
              ...approved.map((a) => _buildCard(a, type)),
            ],
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadAppointments,
      child: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: items.length,
        separatorBuilder: (_, _x) => const SizedBox(height: 12),
        itemBuilder: (_, i) => _buildCard(items[i], type),
      ),
    );
  }

  Widget _buildGroupHeader(String label, int count, Color color, IconData icon) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Row(
        children: [
          Icon(icon, size: 16, color: color),
          const SizedBox(width: 8),
          Text(label, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: color)),
          const Spacer(),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
            decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(10)),
            child: Text('$count', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.white)),
          ),
        ],
      ),
    );
  }

  Widget _buildCard(Map<String, dynamic> appt, String type) {
    final status = (appt['status'] ?? 'pending').toString();
    final doctorName = appt['doctor_name'] ?? 'Doctor';
    final date = appt['date'] ?? appt['appointment_date'] ?? '';
    final time = appt['time'] ?? appt['appointment_time'] ?? '';
    final reason = appt['notes'] ?? appt['reason'] ?? '';
    final bookedOn = appt['created_at'] ?? '';

    Color statusColor;
    Color statusBg;
    String statusLabel;
    switch (status) {
      case 'approved':
        statusColor = const Color(0xFF6366F1); // Indigo
        statusBg = const Color(0xFFEEF2FF);
        statusLabel = 'CONFIRMED';
        break;
      case 'completed':
        statusColor = AppColors.success;
        statusBg = const Color(0xFFDCFCE7);
        statusLabel = 'COMPLETED';
        break;
      case 'cancelled':
        statusColor = AppColors.error;
        statusBg = const Color(0xFFFEE2E2);
        statusLabel = 'CANCELLED';
        break;
      default:
        statusColor = const Color(0xFFF59E0B);
        statusBg = const Color(0xFFFFFBEB);
        statusLabel = 'PENDING';
    }

    return Card(
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: statusColor.withValues(alpha: 0.25), width: 1),
      ),
      elevation: 0,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const CircleAvatar(radius: 18, backgroundColor: Color(0xFFEEF2FF), child: Icon(Icons.person_outline, size: 20, color: Color(0xFF4F46E5))),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(doctorName, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15, color: AppColors.textPrimary)),
                      const SizedBox(height: 2),
                      Row(
                        children: [
                          const Icon(Icons.calendar_today, size: 12, color: AppColors.textSecondary),
                          const SizedBox(width: 4),
                          Text(date, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                          const SizedBox(width: 10),
                          const Icon(Icons.access_time, size: 12, color: AppColors.textSecondary),
                          const SizedBox(width: 4),
                          Text(time, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                        ],
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(color: statusBg, borderRadius: BorderRadius.circular(4)),
                  child: Text(statusLabel, style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: statusColor)),
                ),
              ],
            ),
            if (reason.isNotEmpty) ...[
              const SizedBox(height: 10),
              Text(reason, style: const TextStyle(fontSize: 13, color: AppColors.textSecondary), maxLines: 2, overflow: TextOverflow.ellipsis),
            ],
            if (type == 'upcoming') ...[
              const SizedBox(height: 12),
              const Divider(height: 1),
              const SizedBox(height: 10),
              Row(
                children: [
                  _buildActionBtn('View Details', Icons.visibility_outlined, AppColors.accent, () => _showDetails(appt)),
                  const SizedBox(width: 8),
                  _buildActionBtn('Reschedule', Icons.refresh_outlined, AppColors.success, () => _rescheduleAppointment(appt)),
                  const SizedBox(width: 8),
                  _buildActionBtn('Cancel', Icons.close, AppColors.error, () => _cancelAppointment(appt['id'])),
                ],
              ),
            ],
            if (bookedOn.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text('Booked on $bookedOn', style: const TextStyle(fontSize: 10, color: AppColors.textHint)),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildActionBtn(String label, IconData icon, Color color, VoidCallback onTap) {
    return Flexible(
      child: OutlinedButton.icon(
        onPressed: onTap,
        icon: Icon(icon, size: 12),
        label: Text(label, style: const TextStyle(fontSize: 10)),
        style: OutlinedButton.styleFrom(
          foregroundColor: color,
          side: BorderSide(color: color.withValues(alpha: 0.4)),
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
          minimumSize: Size.zero,
          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
        ),
      ),
    );
  }

  void _showDetails(Map<String, dynamic> appt) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Appointment Details', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
            const SizedBox(height: 16),
            _detailRow(Icons.person_outline, 'Doctor', appt['doctor_name'] ?? '-'),
            _detailRow(Icons.calendar_today, 'Date', appt['date'] ?? appt['appointment_date'] ?? '-'),
            _detailRow(Icons.access_time, 'Time', appt['time'] ?? appt['appointment_time'] ?? '-'),
            _detailRow(Icons.info_outline, 'Status', (appt['status'] ?? '-').toString().toUpperCase()),
            if ((appt['reason'] ?? appt['notes'] ?? '').isNotEmpty)
              _detailRow(Icons.notes, 'Reason', appt['reason'] ?? appt['notes'] ?? ''),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _detailRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        children: [
          Icon(icon, size: 16, color: AppColors.accent),
          const SizedBox(width: 10),
          Text('$label: ', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
          Expanded(child: Text(value, style: const TextStyle(fontSize: 13, color: AppColors.textSecondary))),
        ],
      ),
    );
  }
}
