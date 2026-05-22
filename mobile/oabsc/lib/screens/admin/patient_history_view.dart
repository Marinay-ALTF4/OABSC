import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';
import '../../widgets/stat_card.dart';

class PatientHistoryView extends StatefulWidget {
  final Map<String, dynamic> patient;
  final VoidCallback onBack;

  const PatientHistoryView({super.key, required this.patient, required this.onBack});

  @override
  State<PatientHistoryView> createState() => _PatientHistoryViewState();
}

class _PatientHistoryViewState extends State<PatientHistoryView> {
  final _api = ApiService();
  bool _loading = true;
  List _appointments = [];
  Map<String, int> _stats = {'Total': 0, 'Pending': 0, 'Confirmed / Done': 0, 'Canceled': 0};

  @override
  void initState() {
    super.initState();
    _fetchHistory();
  }

  Future<void> _fetchHistory() async {
    setState(() => _loading = true);
    final patientId = widget.patient['id'];
    try {
      final r = await _api.get('appointments?user_id=$patientId');
      if (mounted && r['success'] == true) {
        final apps = List.from(r['appointments'] ?? []);
        int pending = 0, confirmed = 0, canceled = 0;
        for (var a in apps) {
          final s = a['status']?.toString().toLowerCase();
          if (s == 'pending') pending++;
          else if (s == 'confirmed' || s == 'approved' || s == 'done' || s == 'completed') confirmed++;
          else if (s == 'cancelled' || s == 'canceled') canceled++;
        }
        setState(() {
          _appointments = apps;
          _stats = {
            'Total': apps.length,
            'Pending': pending,
            'Confirmed / Done': confirmed,
            'Canceled': canceled,
          };
        });
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  String _getInitials(String name) {
    if (name.isEmpty) return 'P';
    List<String> parts = name.trim().split(' ');
    if (parts.length > 1) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    return parts[0][0].toUpperCase();
  }

  @override
  Widget build(BuildContext context) {
    final name = (widget.patient['name'] ?? '—').toString();
    final email = (widget.patient['email'] ?? '—').toString();
    final phone = (widget.patient['phone'] ?? '—').toString();
    final init = _getInitials(name);

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
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.access_time_rounded, size: 22, color: AppColors.accent),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('History: $name', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                          const SizedBox(height: 4),
                          Text(email, style: TextStyle(fontSize: 13, color: AppColors.textSecondary.withValues(alpha: 0.8))),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: AppSpacing.sm),
              OutlinedButton.icon(
                onPressed: widget.onBack,
                icon: const Icon(Icons.arrow_back, size: 14),
                label: const Text('All Patients', style: TextStyle(fontSize: 12)),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.textPrimary,
                  side: const BorderSide(color: AppColors.border),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.xl),

          // Profile Card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.border),
              boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 8, offset: const Offset(0, 2))],
            ),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 24,
                  backgroundColor: AppColors.primary,
                  child: Text(init, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: Colors.white)),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(name, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                      const SizedBox(height: 6),
                      Wrap(
                        spacing: 16,
                        runSpacing: 4,
                        children: [
                          Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.email_outlined, size: 14, color: AppColors.textSecondary),
                              const SizedBox(width: 4),
                              Text(email, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                            ],
                          ),
                          Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.phone_outlined, size: 14, color: AppColors.textSecondary),
                              const SizedBox(width: 4),
                              Text(phone, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                            ],
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: AppSpacing.lg),

          // Stat Cards
          LayoutBuilder(builder: (context, constraints) {
            final cols = constraints.maxWidth > 600 ? 4 : 2;
            final w = (constraints.maxWidth - (cols - 1) * 12) / cols;
            return Wrap(spacing: 12, runSpacing: 12, children: [
              SizedBox(width: w, child: StatCard(icon: Icons.assignment_outlined, iconColor: AppColors.textSecondary, iconBgColor: AppColors.border, count: '${_stats['Total']}', label: 'Total')),
              SizedBox(width: w, child: StatCard(icon: Icons.hourglass_empty, iconColor: const Color(0xFFD97706), iconBgColor: const Color(0xFFFEF3C7), count: '${_stats['Pending']}', label: 'Pending')),
              SizedBox(width: w, child: StatCard(icon: Icons.check_circle_outline, iconColor: const Color(0xFF059669), iconBgColor: const Color(0xFFD1FAE5), count: '${_stats['Confirmed / Done']}', label: 'Confirmed / Done')),
              SizedBox(width: w, child: StatCard(icon: Icons.cancel_outlined, iconColor: const Color(0xFFEF4444), iconBgColor: const Color(0xFFFEE2E2), count: '${_stats['Canceled']}', label: 'Canceled')),
            ]);
          }),
          const SizedBox(height: AppSpacing.xl),

          // History Table
          Container(
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.border),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.calendar_month_outlined, size: 16, color: AppColors.textSecondary),
                          SizedBox(width: 8),
                          Text('Appointment History', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                        ],
                      ),
                      Text('${_appointments.length} records', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                    ],
                  ),
                ),
                const Divider(height: 1),
                if (_loading)
                  const Padding(padding: EdgeInsets.all(40), child: Center(child: CircularProgressIndicator()))
                else if (_appointments.isEmpty)
                  Padding(
                    padding: const EdgeInsets.symmetric(vertical: 60),
                    child: Center(
                      child: Column(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(8)),
                            child: const Icon(Icons.event_busy, color: AppColors.textHint, size: 24),
                          ),
                          const SizedBox(height: 16),
                          const Text('No appointment records found for this patient.', style: TextStyle(color: AppColors.textHint, fontSize: 13)),
                        ],
                      ),
                    ),
                  )
                else
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: DataTable(
                      headingRowColor: WidgetStateProperty.all(const Color(0xFFF8FAFC)),
                      headingTextStyle: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.textSecondary, letterSpacing: 0.5),
                      columns: const [
                        DataColumn(label: Text('#')),
                        DataColumn(label: Text('DATE')),
                        DataColumn(label: Text('TIME')),
                        DataColumn(label: Text('DOCTOR')),
                        DataColumn(label: Text('STATUS')),
                      ],
                      rows: _appointments.asMap().entries.map((e) {
                        final i = e.key;
                        final a = e.value as Map;
                        return DataRow(cells: [
                          DataCell(Text('${i + 1}', style: const TextStyle(fontSize: 12))),
                          DataCell(Text((a['appointment_date'] ?? '').toString(), style: const TextStyle(fontSize: 12))),
                          DataCell(Text((a['appointment_time'] ?? '').toString(), style: const TextStyle(fontSize: 12))),
                          DataCell(Text((a['doctor_name'] ?? '—').toString(), style: const TextStyle(fontSize: 12))),
                          DataCell(Text((a['status'] ?? '').toString(), style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500))),
                        ]);
                      }).toList(),
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
