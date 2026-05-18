import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';
import '../../widgets/stat_card.dart';

class DoctorQueueView extends StatefulWidget {
  final VoidCallback onBack;

  const DoctorQueueView({super.key, required this.onBack});

  @override
  State<DoctorQueueView> createState() => _DoctorQueueViewState();
}

class _DoctorQueueViewState extends State<DoctorQueueView> {
  final ApiService _apiService = ApiService();
  final AuthService _authService = AuthService();
  bool _isLoading = true;
  List<Map<String, dynamic>> _todayAppointments = [];
  List<Map<String, dynamic>> _upcomingAppointments = [];
  int _todayCount = 0;
  int _upcomingCount = 0;
  int _totalCount = 0;

  @override
  void initState() {
    super.initState();
    _fetchQueue();
  }

  Future<void> _fetchQueue() async {
    if (!mounted) return;
    setState(() => _isLoading = true);
    try {
      final userId = await _authService.getSavedUserId();
      if (userId == null) return;

      final response = await _apiService.get('appointments?user_id=$userId&role=doctor');
      
      if (response['success'] == true) {
        final List<dynamic> allData = response['appointments'] ?? [];
        final List<Map<String, dynamic>> all = List<Map<String, dynamic>>.from(allData);
        
        final now = DateTime.now();
        final todayStr = DateFormat('yyyy-MM-dd').format(now);
        
        if (mounted) {
          setState(() {
            _todayAppointments = all.where((a) => a['date'] == todayStr).toList();
            _upcomingAppointments = all.where((a) {
              final date = (a['date'] ?? '').toString();
              final status = (a['status'] ?? '').toString().toLowerCase();
              return date.compareTo(todayStr) > 0 && (status == 'approved' || status == 'pending');
            }).toList();
            
            _todayCount = _todayAppointments.length;
            _upcomingCount = _upcomingAppointments.length;
            _totalCount = all.length;
          });
        }
      }
    } catch (e) {
      debugPrint('Error fetching queue: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _updateStatus(dynamic id, String status) async {
    try {
      final response = await _apiService.post('appointments/update-status', {
        'id': id,
        'status': status,
      });
      if (response['success'] == true) {
        _fetchQueue();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Appointment $status successfully'), backgroundColor: AppColors.success),
          );
        }
      }
    } catch (e) {
      debugPrint('Error updating status: $e');
    }
  }

  void _callNextPatient() {
    if (_todayAppointments.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('No patients in queue for today.')));
      return;
    }
    
    final next = _todayAppointments.firstWhere(
      (a) {
        final status = (a['status'] ?? '').toString().toLowerCase();
        return status == 'pending' || status == 'approved';
      },
      orElse: () => <String, dynamic>{},
    );
    
    if (next.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('All patients for today have been attended.')));
      return;
    }
    
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Call Next Patient'),
        content: Text('Now calling: ${next['patient_name'] ?? 'Patient #${next['id']}'}\nReason: ${next['notes'] ?? 'General'}'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Dismiss')),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              _updateStatus(next['id'], 'approved');
            }, 
            child: const Text('Arrived'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildHeaderBanner(),
          const SizedBox(height: AppSpacing.xl),
          _buildStatGrid(),
          const SizedBox(height: AppSpacing.xxl),
          _buildScheduleSection(
            title: "Today's Schedule",
            subtitle: "Appointments scheduled for ${DateFormat('MMMM d, yyyy').format(DateTime.now())}",
            count: _todayCount,
            items: _todayAppointments,
            columns: ['TIME', 'PATIENT', 'REASON', 'STATUS'],
            emptyMessage: "No appointments scheduled for today.",
            emptyIcon: Icons.event_busy_outlined,
          ),
          const SizedBox(height: AppSpacing.xl),
          _buildScheduleSection(
            title: "Upcoming Schedule",
            subtitle: "Future approved and pending appointments",
            count: _upcomingCount,
            items: _upcomingAppointments,
            columns: ['DATE', 'TIME', 'PATIENT', 'REASON', 'STATUS'],
            emptyMessage: "No upcoming appointments found.",
            emptyIcon: Icons.calendar_month_outlined,
          ),
        ],
      ),
    );
  }

  Widget _buildHeaderBanner() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(AppSpacing.xxl),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [AppColors.doctorAccentLight, AppColors.doctorAccent],
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: AppColors.doctorAccent.withValues(alpha: 0.2),
            blurRadius: 15,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'DOCTOR QUEUE',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Colors.white70, letterSpacing: 1.2),
                ),
                const SizedBox(height: 8),
                const Text(
                  "Today's Queue",
                  style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: Colors.white),
                ),
                const SizedBox(height: 8),
                const Text(
                  "View and organize today's schedule alongside upcoming appointments.",
                  style: TextStyle(fontSize: 13, color: Colors.white, fontWeight: FontWeight.w400),
                ),
              ],
            ),
          ),
          const SizedBox(width: AppSpacing.md),
          Column(
            children: [
              ElevatedButton(
                onPressed: _callNextPatient,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.accent,
                  foregroundColor: Colors.white,
                  elevation: 5,
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.campaign_rounded, size: 20),
                    SizedBox(width: 8),
                    Text('CALL NEXT', style: TextStyle(fontWeight: FontWeight.w900)),
                  ],
                ),
              ),
              const SizedBox(height: 8),
              OutlinedButton(
                onPressed: widget.onBack,
                style: OutlinedButton.styleFrom(
                  backgroundColor: Colors.white.withValues(alpha: 0.1),
                  foregroundColor: Colors.white,
                  side: const BorderSide(color: Colors.white),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
                child: const Text('Back', style: TextStyle(fontSize: 11)),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatGrid() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final cols = constraints.maxWidth > 600 ? 3 : 1;
        final w = (constraints.maxWidth - (cols - 1) * 12) / cols;
        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: [
            SizedBox(width: w, child: StatCard(icon: Icons.calendar_today_rounded, count: _todayCount.toString(), label: 'TODAY', iconColor: AppColors.success, iconBgColor: AppColors.iconGreenBg)),
            SizedBox(width: w, child: StatCard(icon: Icons.access_time_rounded, count: _upcomingCount.toString(), label: 'UPCOMING', iconColor: AppColors.accent, iconBgColor: AppColors.iconBlueBg)),
            SizedBox(width: w, child: StatCard(icon: Icons.format_list_bulleted_rounded, count: _totalCount.toString(), label: 'TOTAL', iconColor: AppColors.primary, iconBgColor: const Color(0xFFF0F2F5))),
          ],
        );
      },
    );
  }

  Widget _buildScheduleSection({
    required String title,
    required String subtitle,
    required int count,
    required List<Map<String, dynamic>> items,
    required List<String> columns,
    required String emptyMessage,
    required IconData emptyIcon,
  }) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 10, offset: const Offset(0, 4))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(AppSpacing.lg),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                    Text(subtitle, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(color: AppColors.accent, borderRadius: BorderRadius.circular(20)),
                  child: Text(count.toString(), style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Colors.white)),
                ),
              ],
            ),
          ),
          
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: SizedBox(
              width: 800,
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.md),
                    color: AppColors.background.withValues(alpha: 0.5),
                    child: Row(
                      children: columns.map((col) => SizedBox(
                        width: (800 - 32) / columns.length,
                        child: Text(col, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textSecondary, letterSpacing: 0.5)),
                      )).toList(),
                    ),
                  ),
                  if (items.isEmpty)
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 60),
                      child: Center(
                        child: Column(
                          children: [
                            Icon(emptyIcon, size: 32, color: Colors.grey.withValues(alpha: 0.3)),
                            const SizedBox(height: 12),
                            Text(emptyMessage, style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                          ],
                        ),
                      ),
                    )
                  else
                    ...items.map((item) => _buildScheduleRow(item, columns)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildScheduleRow(Map<String, dynamic> item, List<String> columns) {
    final status = (item['status'] ?? 'pending').toString().toLowerCase();
    Color statusColor = AppColors.warning;
    if (status == 'approved') statusColor = Colors.green;
    if (status == 'completed') statusColor = AppColors.primary;
    if (status == 'cancelled') statusColor = Colors.red;

    return Container(
      width: 800,
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: 16),
      decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: AppColors.border))),
      child: Row(
        children: columns.map((col) {
          Widget content;
          switch (col) {
            case 'DATE':
              content = Text((item['date'] ?? '').toString(), style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600));
              break;
            case 'TIME':
              content = Text((item['time'] ?? '').toString(), style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600));
              break;
            case 'PATIENT':
              content = Text((item['patient_name'] ?? 'Patient #${item['id']}').toString(), style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600));
              break;
            case 'REASON':
              content = Text((item['notes'] ?? 'General').toString(), style: const TextStyle(fontSize: 12, color: AppColors.textSecondary), maxLines: 1, overflow: TextOverflow.ellipsis);
              break;
            case 'STATUS':
              content = Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(color: statusColor.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(4)),
                    child: Text(status.toUpperCase(), style: TextStyle(color: statusColor, fontSize: 9, fontWeight: FontWeight.w800)),
                  ),
                  if (status != 'cancelled' && status != 'completed') ...[
                    const SizedBox(width: 4),
                    IconButton(icon: const Icon(Icons.check_circle_outline, color: Colors.green, size: 16), onPressed: () => _updateStatus(item['id'], 'approved'), padding: EdgeInsets.zero, constraints: const BoxConstraints()),
                    IconButton(icon: const Icon(Icons.cancel_outlined, color: Colors.red, size: 16), onPressed: () => _updateStatus(item['id'], 'cancelled'), padding: EdgeInsets.zero, constraints: const BoxConstraints()),
                  ],
                ],
              );
              break;
            default:
              content = const Text('');
          }
          return SizedBox(width: (800 - 32) / columns.length, child: content);
        }).toList(),
      ),
    );
  }
}
