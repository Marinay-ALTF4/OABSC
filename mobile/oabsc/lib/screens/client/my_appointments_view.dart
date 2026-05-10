import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';

class MyAppointmentsView extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback onBookNew;

  const MyAppointmentsView({
    super.key,
    required this.onBack,
    required this.onBookNew,
  });

  @override
  State<MyAppointmentsView> createState() => _MyAppointmentsViewState();
}

class _MyAppointmentsViewState extends State<MyAppointmentsView> {
  final ApiService _apiService = ApiService();
  final AuthService _authService = AuthService();
  List<Map<String, dynamic>> _appointments = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadAppointments();
  }

  Future<void> _loadAppointments() async {
    setState(() => _isLoading = true);
    try {
      final userId = await _authService.getSavedUserId();
      final response = await _apiService.get('appointments?user_id=$userId');
      if (response['success'] == true || response['appointments'] != null) {
        setState(() {
          _appointments = List<Map<String, dynamic>>.from(response['appointments'] ?? []);
        });
      }
    } catch (e) {
      debugPrint('Error loading appointments: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _cancelAppointment(dynamic id) async {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancel Appointment?'),
        content: const Text('Are you sure you want to cancel this appointment?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Keep It')),
          TextButton(
            onPressed: () async {
              Navigator.pop(ctx);
              try {
                final response = await _apiService.post('appointments/cancel', body: {'id': id});
                if (response['success'] == true) {
                  _loadAppointments(); // Refresh list
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Appointment cancelled successfully'), backgroundColor: AppColors.success),
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
            },
            child: const Text('Yes, Cancel', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(child: Padding(padding: EdgeInsets.all(40), child: CircularProgressIndicator()));
    }

    final upcomingCount = _appointments.where((a) => a['status'] == 'pending' || a['status'] == 'approved').length;
    final completedCount = _appointments.where((a) => a['status'] == 'completed').length;
    final cancelledCount = _appointments.where((a) => a['status'] == 'cancelled').length;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'My Appointments',
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w700,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Review and manage all your clinic appointments.',
                      style: TextStyle(
                        fontSize: 13,
                        color: AppColors.textSecondary.withValues(alpha: 0.8),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  OutlinedButton(
                    onPressed: widget.onBack,
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.textPrimary,
                      side: const BorderSide(color: AppColors.border),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    ),
                    child: const Row(
                      children: [
                        Icon(Icons.arrow_back, size: 14),
                        SizedBox(width: 4),
                        Text('Dashboard', style: TextStyle(fontSize: 11)),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton(
                    onPressed: widget.onBookNew,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1E40AF),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    ),
                    child: const Row(
                      children: [
                        Icon(Icons.add, size: 14, color: Colors.white),
                        SizedBox(width: 4),
                        Text('Book New', style: TextStyle(fontSize: 11, color: Colors.white)),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.xxl),

          // Summary Badges
          Row(
            children: [
              _buildSummaryBadge('$upcomingCount Upcoming', const Color(0xFFDBEAFE), const Color(0xFF1E40AF)),
              const SizedBox(width: 8),
              _buildSummaryBadge('$completedCount Completed', const Color(0xFFDCFCE7), const Color(0xFF15803D)),
              const SizedBox(width: 8),
              _buildSummaryBadge('$cancelledCount Cancelled', const Color(0xFFFEE2E2), const Color(0xFFB91C1C)),
            ],
          ),
          const SizedBox(height: AppSpacing.xxl),

          // Tabs UI
          DefaultTabController(
            length: 3,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                TabBar(
                  isScrollable: true,
                  tabAlignment: TabAlignment.start,
                  labelColor: AppColors.accent,
                  unselectedLabelColor: AppColors.textSecondary,
                  indicatorColor: AppColors.accent,
                  indicatorWeight: 3,
                  dividerColor: AppColors.border,
                  labelPadding: const EdgeInsets.only(right: 32),
                  labelStyle: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
                  tabs: [
                    Tab(child: Row(children: [const Icon(Icons.calendar_today, size: 16), const SizedBox(width: 8), Text('Upcoming  $upcomingCount')])),
                    Tab(child: Row(children: [const Icon(Icons.check_circle_outline, size: 16), const SizedBox(width: 8), Text('Completed  $completedCount')])),
                    Tab(child: Row(children: [const Icon(Icons.cancel_outlined, size: 16), const SizedBox(width: 8), Text('Cancelled  $cancelledCount')])),
                  ],
                ),
                const SizedBox(height: 24),
                
                // Tab Views
                SizedBox(
                  height: 450,
                  child: TabBarView(
                    children: [
                      _buildAppointmentList('upcoming'),
                      _buildAppointmentList('completed'),
                      _buildAppointmentList('cancelled'),
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

  Widget _buildAppointmentList(String listType) {
    List<Map<String, dynamic>> filtered;
    if (listType == 'upcoming') {
      filtered = _appointments.where((a) => a['status'] == 'pending' || a['status'] == 'approved').toList();
    } else {
      filtered = _appointments.where((a) => a['status'] == listType).toList();
    }

    if (filtered.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.grey.withValues(alpha: 0.05),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(
                Icons.event_busy_outlined,
                size: 48,
                color: Colors.grey.withValues(alpha: 0.3),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'No $listType appointments',
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w700,
                color: AppColors.textPrimary,
              ),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      itemCount: filtered.length,
      itemBuilder: (context, index) {
        final appt = filtered[index];
        final status = (appt['status'] ?? 'pending').toString();
        final doctorName = appt['doctor_name'] ?? 'Doctor';
        final date = appt['date'] ?? '';
        final time = appt['time'] ?? '';
        final reason = appt['notes'] ?? appt['reason'] ?? 'No reason';

        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12), side: const BorderSide(color: AppColors.border)),
          elevation: 0,
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(doctorName, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: (status == 'approved' ? Colors.green : Colors.orange).withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        status.toUpperCase(),
                        style: TextStyle(
                          fontSize: 10, 
                          fontWeight: FontWeight.w800, 
                          color: status == 'approved' ? Colors.green : Colors.orange
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    const Icon(Icons.calendar_today, size: 14, color: AppColors.textSecondary),
                    const SizedBox(width: 6),
                    Text(date, style: const TextStyle(fontSize: 13)),
                    const SizedBox(width: 16),
                    const Icon(Icons.access_time, size: 14, color: AppColors.textSecondary),
                    const SizedBox(width: 6),
                    Text(time, style: const TextStyle(fontSize: 13)),
                  ],
                ),
                const SizedBox(height: 8),
                Text('Reason: $reason', style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                if (listType == 'upcoming') ...[
                  const SizedBox(height: 12),
                  const Divider(),
                  Align(
                    alignment: Alignment.centerRight,
                    child: TextButton(
                      onPressed: () => _cancelAppointment(appt['id']),
                      style: TextButton.styleFrom(foregroundColor: Colors.red),
                      child: const Text('Cancel Appointment'),
                    ),
                  ),
                ],
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildSummaryBadge(String text, Color bgColor, Color textColor) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        text,
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w700,
          color: textColor,
        ),
      ),
    );
  }
}
