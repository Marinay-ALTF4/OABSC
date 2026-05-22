import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';

class BookAppointmentView extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback onViewAppointments;
  const BookAppointmentView({super.key, required this.onBack, required this.onViewAppointments});
  @override
  State<BookAppointmentView> createState() => _BookAppointmentViewState();
}

class _BookAppointmentViewState extends State<BookAppointmentView> {
  String? _selectedDoctor;
  String? _selectedDoctorId;
  String? _selectedTime;
  String? _selectedDate;
  bool _isSubmitting = false;
  final TextEditingController _reasonController = TextEditingController();
  final ApiService _apiService = ApiService();
  List<Map<String, String>> _doctors = [];
  bool _isLoadingDoctors = true;

  final List<String> _timeSlots = [
    '09:00','09:30','10:00','10:30',
    '11:00','11:30','13:00','13:30',
    '14:00','14:30','15:00','15:30',
    '16:00','16:30',
  ];

  @override
  void initState() {
    super.initState();
    _fetchDoctors();
  }

  @override
  void dispose() {
    _reasonController.dispose();
    super.dispose();
  }

  Future<void> _fetchDoctors() async {
    try {
      final response = await _apiService.get('doctors');
      if (response['doctors'] != null) {
        final List<dynamic> list = response['doctors'];
        if (mounted) {
          setState(() {
            _doctors = list.map((doc) {
              final name = doc['name'] as String? ?? 'Dr. Unknown';
              final parts = name.replaceAll('Dr. ', '').split(' ');
              final initials = parts.length > 1
                  ? '${parts[0][0]}${parts[1][0]}'.toUpperCase()
                  : parts.isNotEmpty && parts[0].isNotEmpty
                      ? parts[0].substring(0, 2).toUpperCase()
                      : 'DR';
              return {
                'id': doc['id'].toString(),
                'name': name,
                'specialty': doc['specialization'] as String? ?? 'Specialist',
                'experience': doc['experience'] != null ? '${doc['experience']} experience' : 'N/A experience',
                'initials': initials,
              };
            }).toList();
            _isLoadingDoctors = false;
          });
        }
      } else {
        if (mounted) setState(() => _isLoadingDoctors = false);
      }
    } catch (_) {
      if (mounted) setState(() => _isLoadingDoctors = false);
    }
  }

  Future<void> _submitAppointment() async {
    if (_selectedDoctor == null || _selectedTime == null || _selectedDate == null || _reasonController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select a doctor, date, time, and provide a reason.'), backgroundColor: AppColors.error),
      );
      return;
    }
    setState(() => _isSubmitting = true);
    try {
      final userId = await AuthService().getSavedUserId();
      final parts = _selectedDate!.split('/');
      final formatted = '${parts[2]}-${parts[1]}-${parts[0]}';
      final response = await _apiService.post('appointments', {
        'user_id': userId,
        'doctor_id': _selectedDoctorId,
        'doctor_name': _selectedDoctor,
        'appointment_date': formatted,
        'appointment_time': _selectedTime,
        'reason': _reasonController.text.trim(),
      });
      if (mounted) {
        setState(() => _isSubmitting = false);
        if (response['success'] == true) {
          showDialog(
            context: context,
            builder: (ctx) => AlertDialog(
              title: const Text('Success!'),
              content: const Text('Your appointment has been submitted successfully.'),
              actions: [
                TextButton(onPressed: () { Navigator.pop(ctx); widget.onViewAppointments(); }, child: const Text('View Appointments')),
              ],
            ),
          );
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(response['message'] ?? 'Failed to book appointment.'), backgroundColor: AppColors.error),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isSubmitting = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: AppColors.error),
        );
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
          // Header
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('BOOK', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.textSecondary, letterSpacing: 1.2)),
                    const SizedBox(height: 2),
                    const Text('Book New Appointment', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
                    const SizedBox(height: 4),
                    const Text('Select doctor, date, and time slot, then confirm your booking.', style: TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              TextButton.icon(
                onPressed: widget.onViewAppointments,
                icon: const Icon(Icons.arrow_back, size: 13),
                label: const Text('My Appointments', style: TextStyle(fontSize: 11)),
                style: TextButton.styleFrom(foregroundColor: AppColors.accent),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.xl),

          // Main form card
          Container(
            padding: const EdgeInsets.all(AppSpacing.xl),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.border),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Select Your Doctor', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                const SizedBox(height: AppSpacing.md),

                // Doctor grid
                if (_isLoadingDoctors)
                  const Center(child: Padding(padding: EdgeInsets.all(24), child: CircularProgressIndicator()))
                else if (_doctors.isEmpty)
                  const Center(child: Padding(padding: EdgeInsets.all(24), child: Text('No doctors available.')))
                else
                  LayoutBuilder(
                    builder: (ctx, constraints) {
                      final cardWidth = (constraints.maxWidth - 12) / 2;
                      return Wrap(
                        spacing: 12,
                        runSpacing: 12,
                        children: _doctors.map((doc) => SizedBox(
                          width: cardWidth,
                          child: _buildDoctorCard(doc, _selectedDoctor == doc['name']),
                        )).toList(),
                      );
                    },
                  ),

                const SizedBox(height: AppSpacing.xl),

                // Date picker
                const Text('Date', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                const SizedBox(height: AppSpacing.sm),
                GestureDetector(
                  onTap: () async {
                    final date = await showDatePicker(
                      context: context,
                      initialDate: DateTime.now().add(const Duration(days: 1)),
                      firstDate: DateTime.now(),
                      lastDate: DateTime.now().add(const Duration(days: 365)),
                    );
                    if (date != null && mounted) {
                      setState(() {
                        _selectedDate = '${date.day.toString().padLeft(2,'0')}/${date.month.toString().padLeft(2,'0')}/${date.year}';
                      });
                    }
                  },
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      border: Border.all(color: AppColors.inputBorder),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      children: [
                        Expanded(
                          child: Text(
                            _selectedDate ?? 'dd/mm/yyyy',
                            style: TextStyle(color: _selectedDate == null ? AppColors.textHint : AppColors.textPrimary, fontSize: 14),
                          ),
                        ),
                        const Icon(Icons.calendar_today_outlined, size: 18, color: AppColors.textSecondary),
                      ],
                    ),
                  ),
                ),

                const SizedBox(height: AppSpacing.xl),

                // Time slots
                const Text('Available Time Slots', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                const SizedBox(height: AppSpacing.md),
                LayoutBuilder(
                  builder: (ctx, constraints) {
                    final slotWidth = (constraints.maxWidth - 24) / 4;
                    return Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: _timeSlots.map((time) {
                        final isSelected = _selectedTime == time;
                        return GestureDetector(
                          onTap: () => setState(() => _selectedTime = time),
                          child: Container(
                            width: slotWidth,
                            padding: const EdgeInsets.symmetric(vertical: 9),
                            decoration: BoxDecoration(
                              color: isSelected ? AppColors.accent : Colors.white,
                              borderRadius: BorderRadius.circular(6),
                              border: Border.all(color: isSelected ? AppColors.accent : AppColors.border),
                            ),
                            child: Text(
                              time,
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: isSelected ? FontWeight.w600 : FontWeight.w500,
                                color: isSelected ? Colors.white : AppColors.accent,
                              ),
                            ),
                          ),
                        );
                      }).toList(),
                    );
                  },
                ),
                const SizedBox(height: 6),
                const Text('Booked slots are disabled automatically.', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),

                const SizedBox(height: AppSpacing.xl),

                // Reason
                const Text('Reason for Visit', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                const SizedBox(height: AppSpacing.sm),
                TextField(
                  controller: _reasonController,
                  maxLines: 4,
                  decoration: const InputDecoration(
                    hintText: 'Describe your concern or reason for consultation',
                    fillColor: Color(0xFFF8FAFC),
                  ),
                ),

                const SizedBox(height: AppSpacing.xl),

                // Summary
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(AppSpacing.lg),
                  decoration: BoxDecoration(
                    color: const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: const Color(0xFFDBEAFE)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Appointment Summary', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                      const SizedBox(height: 12),
                      _summaryRow('Doctor:', _selectedDoctor ?? '-'),
                      _summaryRow('Date:', _selectedDate ?? '-'),
                      _summaryRow('Time:', _selectedTime ?? '-'),
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          const Icon(Icons.location_on_outlined, size: 14, color: Colors.red),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(
                              'Location: ${_selectedDoctor != null ? 'Clinic Main Office' : 'Select a doctor to see location'}',
                              style: const TextStyle(fontSize: 11, color: AppColors.accent),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: AppSpacing.xl),

                // Submit
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _isSubmitting ? null : _submitAppointment,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF4F46E5), // Indigo matching web
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    child: _isSubmitting
                        ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Text('Submit Appointment', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                  ),
                ),
                const SizedBox(height: 10),
                Center(
                  child: TextButton(
                    onPressed: widget.onBack,
                    child: const Text('Back to Dashboard', style: TextStyle(color: AppColors.accent)),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDoctorCard(Map<String, String> doc, bool isSelected) {
    return GestureDetector(
      onTap: () => setState(() {
        _selectedDoctor = doc['name'];
        _selectedDoctorId = doc['id'];
      }),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: isSelected ? AppColors.accent : AppColors.border, width: isSelected ? 2 : 1),
          boxShadow: [
            BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 4, offset: const Offset(0, 2)),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircleAvatar(
              radius: 26,
              backgroundColor: const Color(0xFF4F46E5),
              child: Text(doc['initials']!, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18)),
            ),
            const SizedBox(height: 10),
            Text(doc['name']!, textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.textPrimary),
              maxLines: 2, overflow: TextOverflow.ellipsis),
            const SizedBox(height: 2),
            Text(doc['specialty']!, style: const TextStyle(fontSize: 11, color: AppColors.accent, fontWeight: FontWeight.w500)),
            const SizedBox(height: 3),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.access_time, size: 10, color: AppColors.textSecondary.withValues(alpha: 0.7)),
                const SizedBox(width: 3),
                Flexible(child: Text(doc['experience']!, style: TextStyle(fontSize: 10, color: AppColors.textSecondary.withValues(alpha: 0.7)), overflow: TextOverflow.ellipsis)),
              ],
            ),
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(4), 
                border: Border.all(color: const Color(0xFF6366F1).withValues(alpha: 0.3)),
                color: const Color(0xFFEEF2FF),
              ),
              child: const Text('View Profile', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: Color(0xFF6366F1))),
            ),
          ],
        ),
      ),
    );
  }

  Widget _summaryRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Row(
        children: [
          Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
          const SizedBox(width: 8),
          Text(value, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
        ],
      ),
    );
  }
}
