import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';

class DoctorAppointmentsView extends StatefulWidget {
  final VoidCallback onBack;
  
  const DoctorAppointmentsView({super.key, required this.onBack});

  @override
  State<DoctorAppointmentsView> createState() => _DoctorAppointmentsViewState();
}

class _DoctorAppointmentsViewState extends State<DoctorAppointmentsView> {
  final ApiService _apiService = ApiService();
  final AuthService _authService = AuthService();
  int _activeTabIndex = 3; // 'All'
  List<Map<String, dynamic>> _allAppointments = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchAppointments();
  }

  Future<void> _fetchAppointments() async {
    setState(() => _isLoading = true);
    try {
      final userId = await _authService.getSavedUserId();
      final response = await _apiService.get('appointments?user_id=$userId&role=doctor');
      if (response['success'] == true || response['appointments'] != null) {
        setState(() {
          _allAppointments = List<Map<String, dynamic>>.from(response['appointments'] ?? []);
        });
      }
    } catch (e) {
      debugPrint('Error fetching doctor appointments: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _updateStatus(dynamic id, String status) async {
    try {
      final response = await _apiService.post('appointments/update-status', body: {
        'id': id,
        'status': status,
      });
      if (response['success'] == true) {
        _fetchAppointments();
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

  List<Map<String, dynamic>> get _filteredAppointments {
    final now = DateTime.now();
    final todayStr = '${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}';
    
    switch (_activeTabIndex) {
      case 0: // Upcoming (Approved & After Today)
        return _allAppointments.where((a) {
          final date = a['date'] ?? '';
          final status = (a['status'] ?? '').toString().toLowerCase();
          return (status == 'approved' || status == 'pending') && date.compareTo(todayStr) > 0;
        }).toList();
      case 1: // Today
        return _allAppointments.where((a) => a['date'] == todayStr).toList();
      case 2: // Past
        return _allAppointments.where((a) {
          final date = a['date'] ?? '';
          final status = (a['status'] ?? '').toString().toLowerCase();
          return date.compareTo(todayStr) < 0 || status == 'completed' || status == 'cancelled';
        }).toList();
      default: // All
        return _allAppointments;
    }
  }

  final List<String> _tabs = ['Upcoming', 'Today', 'Past', 'All'];

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header section
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
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
                  const Text(
                    'View and manage your patient appointments.',
                    style: TextStyle(
                      fontSize: 13,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ],
              ),
              OutlinedButton(
                onPressed: widget.onBack,
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  minimumSize: Size.zero,
                  side: const BorderSide(color: AppColors.border),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                ),
                child: const Text(
                  'Back',
                  style: TextStyle(fontSize: 12, color: AppColors.textPrimary, fontWeight: FontWeight.w500),
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.xl),

          // Custom Tab Bar
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: List.generate(_tabs.length, (index) {
                final bool isActive = _activeTabIndex == index;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: InkWell(
                    onTap: () => setState(() => _activeTabIndex = index),
                    borderRadius: BorderRadius.circular(6),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      decoration: BoxDecoration(
                        color: isActive ? AppColors.accent : Colors.white,
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(
                          color: isActive ? AppColors.accent : AppColors.border,
                        ),
                      ),
                      child: Text(
                        _tabs[index],
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w500,
                          color: isActive ? Colors.white : AppColors.textSecondary,
                        ),
                      ),
                    ),
                  ),
                );
              }),
            ),
          ),
          const SizedBox(height: AppSpacing.xl),

          // Main content container
          Container(
            width: double.infinity,
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.border),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.02),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              children: [
                if (_isLoading)
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 60),
                    child: Center(child: CircularProgressIndicator()),
                  )
                else if (_filteredAppointments.isEmpty)
                  _buildEmptyState()
                else
                  _buildAppointmentList(),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 60),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.grey.withOpacity(0.05),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: AppColors.border),
            ),
            child: Icon(
              Icons.calendar_today_outlined,
              size: 24,
              color: Colors.grey.withOpacity(0.6),
            ),
          ),
          const SizedBox(height: 16),
          const Text(
            'No appointments found.',
            style: TextStyle(
              fontSize: 14,
              color: AppColors.textSecondary,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAppointmentList() {
    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: _filteredAppointments.length,
      separatorBuilder: (context, index) => const Divider(height: 1, color: AppColors.border),
      itemBuilder: (context, index) {
        final appt = _filteredAppointments[index];
        final status = (appt['status'] ?? 'pending').toString().toLowerCase();
        
        Color statusColor = AppColors.warning;
        if (status == 'approved') statusColor = Colors.green;
        if (status == 'completed') statusColor = AppColors.primary;
        if (status == 'cancelled') statusColor = Colors.red;

        return Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      appt['patient_name']?.isNotEmpty == true ? appt['patient_name'] : 'Patient #${appt['id']}',
                      style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
                    ),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        const Icon(Icons.access_time, size: 14, color: AppColors.textSecondary),
                        const SizedBox(width: 4),
                        Text('${appt['date']} at ${appt['time']}', style: const TextStyle(fontSize: 12)),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Reason: ${appt['notes'] ?? 'No reason provided'}',
                      style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 12),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: statusColor.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      status.toUpperCase(),
                      style: TextStyle(color: statusColor, fontSize: 10, fontWeight: FontWeight.w800),
                    ),
                  ),
                  if (status != 'cancelled' && status != 'completed') ...[
                    const SizedBox(height: 12),
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        if (status == 'pending')
                          IconButton(
                            icon: const Icon(Icons.check_circle_outline, color: Colors.green, size: 22),
                            onPressed: () => _updateStatus(appt['id'], 'approved'),
                            constraints: const BoxConstraints(),
                            padding: EdgeInsets.zero,
                            tooltip: 'Approve',
                          ),
                        if (status == 'approved')
                          IconButton(
                            icon: const Icon(Icons.done_all, color: AppColors.primary, size: 22),
                            onPressed: () => _updateStatus(appt['id'], 'completed'),
                            constraints: const BoxConstraints(),
                            padding: EdgeInsets.zero,
                            tooltip: 'Mark Complete',
                          ),
                        const SizedBox(width: 12),
                        IconButton(
                          icon: const Icon(Icons.cancel_outlined, color: Colors.red, size: 22),
                          onPressed: () => _updateStatus(appt['id'], 'cancelled'),
                          constraints: const BoxConstraints(),
                          padding: EdgeInsets.zero,
                          tooltip: 'Cancel',
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ],
          ),
        );
      },
    );
  }
}
