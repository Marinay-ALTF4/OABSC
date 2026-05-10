import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

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
  final List<Map<String, dynamic>> _appointments = [
    {
      'id': 1,
      'doctor': 'Dr. Doctor',
      'date': '15/06/2026',
      'time': '10:00 am',
      'reason': 'General Checkup',
      'status': 'upcoming'
    }
  ];

  void _cancelAppointment(int id) async {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancel Appointment?'),
        content: const Text('Are you sure you want to cancel this appointment?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Keep It')),
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              setState(() {
                final index = _appointments.indexWhere((a) => a['id'] == id);
                if (index != -1) _appointments[index]['status'] = 'cancelled';
              });
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Appointment cancelled successfully'), backgroundColor: AppColors.success),
              );
            },
            child: const Text('Yes, Cancel', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
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
                      backgroundColor: const Color(0xFF1E40AF), // Darker blue like web
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
              _buildSummaryBadge('0 Upcoming', const Color(0xFFDBEAFE), const Color(0xFF1E40AF)),
              const SizedBox(width: 8),
              _buildSummaryBadge('0 Completed', const Color(0xFFDCFCE7), const Color(0xFF15803D)),
              const SizedBox(width: 8),
              _buildSummaryBadge('0 Cancelled', const Color(0xFFFEE2E2), const Color(0xFFB91C1C)),
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
                    Tab(child: Row(children: [Icon(Icons.calendar_today, size: 16), SizedBox(width: 8), Text('Upcoming  ${_appointments.where((a) => a['status'] == 'upcoming').length}')])),
                    Tab(child: Row(children: [Icon(Icons.check_circle_outline, size: 16), SizedBox(width: 8), Text('Completed  ${_appointments.where((a) => a['status'] == 'completed').length}')])),
                    Tab(child: Row(children: [Icon(Icons.cancel_outlined, size: 16), SizedBox(width: 8), Text('Cancelled  ${_appointments.where((a) => a['status'] == 'cancelled').length}')])),
                  ],
                ),
                const SizedBox(height: 24),
                
                // Tab Views
                SizedBox(
                  height: 400,
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

  Widget _buildAppointmentList(String status) {
    final filtered = _appointments.where((a) => a['status'] == status).toList();
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
              'No $status appointments',
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
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(appt['doctor'], style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                const SizedBox(height: 8),
                Text('Date: ${appt['date']} at ${appt['time']}'),
                Text('Reason: ${appt['reason']}'),
                const SizedBox(height: 12),
                if (status == 'upcoming')
                  Row(
                    children: [
                      OutlinedButton(
                        onPressed: () => _cancelAppointment(appt['id']),
                        style: OutlinedButton.styleFrom(foregroundColor: Colors.red),
                        child: const Text('Cancel'),
                      ),
                    ],
                  ),
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
