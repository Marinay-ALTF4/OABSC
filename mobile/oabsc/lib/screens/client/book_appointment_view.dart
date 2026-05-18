import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';

class BookAppointmentView extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback onViewAppointments;

  const BookAppointmentView({
    super.key,
    required this.onBack,
    required this.onViewAppointments,
  });

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

  @override
  void initState() {
    super.initState();
    _fetchDoctors();
  }

  Future<void> _fetchDoctors() async {
    try {
      final response = await _apiService.get('doctors');
      if (response['success'] != false && response['doctors'] != null) {
        final List<dynamic> doctorList = response['doctors'];
        if (mounted) {
          setState(() {
            _doctors = doctorList.map((doc) {
              final name = doc['name'] as String? ?? 'Dr. Unknown';
              final parts = name.replaceAll('Dr. ', '').split(' ');
              final initials = parts.length > 1 
                  ? '${parts[0][0]}${parts[1][0]}'.toUpperCase()
                  : parts.isNotEmpty && parts[0].isNotEmpty ? parts[0].substring(0, 2).toUpperCase() : 'DR';
              
              return {
                'id': doc['id'].toString(),
                'name': name,
                'specialty': doc['specialization'] as String? ?? 'Specialist',
                'experience': '${doc['experience'] ?? 'N/A'} experience',
                'initials': initials,
              };
            }).toList();
            _isLoadingDoctors = false;
          });
        }
      } else {
        if (mounted) setState(() => _isLoadingDoctors = false);
      }
    } catch (e) {
      if (mounted) setState(() => _isLoadingDoctors = false);
    }
  }

  final List<String> _timeSlots = [
    '09:00', '09:30', '10:00', '10:30',
    '11:00', '11:30', '13:00', '13:30',
    '14:00', '14:30', '15:00', '15:30',
    '16:00', '16:30',
  ];

  Future<void> _submitAppointment() async {
    if (_selectedDoctor == null || _selectedTime == null || _selectedDate == null || _reasonController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please select a doctor, date, time, and provide a reason.'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    setState(() => _isSubmitting = true);
    
    try {
      final userId = await AuthService().getSavedUserId();
      final dateParts = _selectedDate!.split('/');
      final formattedDate = '${dateParts[2]}-${dateParts[1]}-${dateParts[0]}';

      final response = await _apiService.post('appointments', {
        'user_id': userId,
        'doctor_id': _selectedDoctorId,
        'doctor_name': _selectedDoctor,
        'appointment_date': formattedDate,
        'appointment_time': _selectedTime,
        'reason': _reasonController.text.trim(),
      });

      if (mounted) {
        setState(() => _isSubmitting = false);
        
        if (response['success'] == true) {
          showDialog(
            context: context,
            builder: (ctx) => AlertDialog(
              title: const Text('Success'),
              content: const Text('Appointment submitted successfully.'),
              actions: [
                TextButton(
                  onPressed: () {
                    Navigator.of(ctx).pop(); // Close dialog
                    widget.onViewAppointments(); // Go to my appointments
                  },
                  child: const Text('View Appointments'),
                ),
              ],
            ),
          );
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(response['message'] ?? 'Failed to book appointment.'),
              backgroundColor: AppColors.error,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isSubmitting = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error: $e'),
            backgroundColor: AppColors.error,
          ),
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
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Book New Appointment',
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w700,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Select doctor, date, and time slot, then confirm your booking.',
                      style: TextStyle(
                        fontSize: 13,
                        color: AppColors.textSecondary.withValues(alpha: 0.8),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              OutlinedButton(
                onPressed: widget.onViewAppointments,
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.textPrimary,
                  side: const BorderSide(color: AppColors.border),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                ),
                child: const Text('My Appointments', style: TextStyle(fontSize: 12)),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.xxl),

          // Main Form Card
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
                const Text(
                  'Select Your Doctor',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textPrimary,
                  ),
                ),
                const SizedBox(height: AppSpacing.md),
                
                // Doctor Selection Horizontal List
                SizedBox(
                  height: 180,
                  child: _isLoadingDoctors
                    ? const Center(child: CircularProgressIndicator())
                    : _doctors.isEmpty
                        ? const Center(child: Text('No doctors available.'))
                        : ListView.separated(
                            scrollDirection: Axis.horizontal,
                            itemCount: _doctors.length,
                            separatorBuilder: (context, index) => const SizedBox(width: 12),
                            itemBuilder: (context, index) {
                              final doc = _doctors[index];
                              final isSelected = _selectedDoctor == doc['name'];
                              return _buildDoctorCard(doc, isSelected);
                            },
                          ),
                ),
                
                const SizedBox(height: AppSpacing.xl),
                
                // Date Picker
                const Text(
                  'Date',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textPrimary,
                  ),
                ),
                const SizedBox(height: AppSpacing.sm),
                TextField(
                  readOnly: true,
                  controller: TextEditingController(text: _selectedDate),
                  decoration: const InputDecoration(
                    hintText: 'dd/mm/yyyy',
                    suffixIcon: Icon(Icons.calendar_today_outlined, size: 20),
                    fillColor: Color(0xFFF8FAFC),
                  ),
                  onTap: () async {
                    final date = await showDatePicker(
                      context: context,
                      initialDate: DateTime.now().add(const Duration(days: 1)),
                      firstDate: DateTime.now(),
                      lastDate: DateTime.now().add(const Duration(days: 365)),
                    );
                    if (date != null && mounted) {
                      setState(() {
                        _selectedDate = '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}';
                      });
                    }
                  },
                ),
                
                const SizedBox(height: AppSpacing.xl),
                
                // Time Slots
                const Text(
                  'Available Time Slots',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textPrimary,
                  ),
                ),
                const SizedBox(height: AppSpacing.md),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: _timeSlots.map((time) {
                    final isSelected = _selectedTime == time;
                    return InkWell(
                      onTap: () => setState(() => _selectedTime = time),
                      child: Container(
                        width: (MediaQuery.of(context).size.width - 64 - 24) / 4,
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        decoration: BoxDecoration(
                          color: isSelected ? AppColors.accent : Colors.white,
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(
                            color: isSelected ? AppColors.accent : AppColors.border,
                          ),
                        ),
                        child: Text(
                          time,
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: isSelected ? FontWeight.w600 : FontWeight.w500,
                            color: isSelected ? Colors.white : AppColors.textPrimary,
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Booked slots are disabled automatically.',
                  style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
                ),
                
                const SizedBox(height: AppSpacing.xl),
                
                // Reason for Visit
                const Text(
                  'Reason for Visit',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textPrimary,
                  ),
                ),
                const SizedBox(height: AppSpacing.sm),
                TextField(
                  controller: _reasonController,
                  maxLines: 4,
                  decoration: InputDecoration(
                    hintText: 'Describe your concern or reason for consultation',
                    fillColor: const Color(0xFFF8FAFC),
                  ),
                ),
                
                const SizedBox(height: AppSpacing.xl),
                
                // Summary Card
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
                      const Text(
                        'Appointment Summary',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 12),
                      _buildSummaryRow('Doctor:', _selectedDoctor ?? '-'),
                      _buildSummaryRow('Date:', _selectedDate ?? '-'),
                      _buildSummaryRow('Time:', _selectedTime ?? '-'),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          const Icon(Icons.location_on_outlined, size: 14, color: Colors.red),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(
                              'Location: ${_selectedDoctor != null ? "Clinic Main Office" : "Select a doctor to see location"}',
                              style: const TextStyle(fontSize: 11, color: AppColors.accent),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(height: AppSpacing.xl),
                
                // Actions
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton(
                        onPressed: _isSubmitting ? null : _submitAppointment,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.accent,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                        ),
                        child: _isSubmitting 
                            ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                            : const Text('Submit Appointment', style: TextStyle(color: Colors.white)),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Center(
                  child: TextButton(
                    onPressed: widget.onBack,
                    child: const Text(
                      'Back to Dashboard',
                      style: TextStyle(color: AppColors.accent),
                    ),
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
        width: 140,
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: isSelected ? AppColors.accent : AppColors.border,
            width: isSelected ? 2 : 1,
          ),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircleAvatar(
              radius: 24,
              backgroundColor: const Color(0xFF4F46E5),
              child: Text(
                doc['initials']!,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 18,
                ),
              ),
            ),
            const SizedBox(height: 12),
            Text(
              doc['name']!,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: AppColors.textPrimary,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 2),
            Text(
              doc['specialty']!,
              style: const TextStyle(
                fontSize: 11,
                color: AppColors.accent,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              doc['experience']!,
              style: TextStyle(
                fontSize: 10,
                color: AppColors.textSecondary.withValues(alpha: 0.7),
              ),
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(4),
                border: Border.all(color: AppColors.border),
              ),
              child: const Text(
                'View Profile',
                style: TextStyle(fontSize: 10, fontWeight: FontWeight.w500),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Row(
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(width: 8),
          Text(
            value,
            style: const TextStyle(
              fontSize: 12,
              color: AppColors.textSecondary,
            ),
          ),
        ],
      ),
    );
  }
}
