import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';

class DoctorPrescriptionsView extends StatefulWidget {
  final VoidCallback onBack;

  const DoctorPrescriptionsView({super.key, required this.onBack});

  @override
  State<DoctorPrescriptionsView> createState() => _DoctorPrescriptionsViewState();
}

class _DoctorPrescriptionsViewState extends State<DoctorPrescriptionsView> {
  final ApiService _apiService = ApiService();
  final AuthService _authService = AuthService();
  
  final _medicineController = TextEditingController();
  final _dosageController = TextEditingController();
  final _frequencyController = TextEditingController();
  final _durationController = TextEditingController();
  final _instructionsController = TextEditingController();
  String? _selectedPatient;
  List<Map<String, dynamic>> _savedPrescriptions = [];
  List<Map<String, dynamic>> _patients = [];
  bool _isLoading = true;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  Future<void> _fetchData() async {
    setState(() => _isLoading = true);
    try {
      final userId = await _authService.getSavedUserId();
      
      // Fetch prescriptions
      final resp = await _apiService.get('prescriptions?user_id=$userId');
      if (resp['success'] == true) {
        _savedPrescriptions = List<Map<String, dynamic>>.from(resp['prescriptions'] ?? []);
      }

      // Fetch patients for dropdown
      final patientsResp = await _apiService.get('patients');
      if (patientsResp['success'] == true) {
        _patients = List<Map<String, dynamic>>.from(patientsResp['patients'] ?? []);
      }
    } catch (e) {
      debugPrint('Error fetching prescriptions: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _savePrescription() async {
    if (_medicineController.text.isEmpty || _dosageController.text.isEmpty || _selectedPatient == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please select a patient and fill in medicine/dosage')));
      return;
    }

    setState(() => _isSaving = true);
    try {
      final userId = await _authService.getSavedUserId();
      final response = await _apiService.post('prescriptions', body: {
        'doctor_id': userId,
        'patient_name': _selectedPatient,
        'medication': _medicineController.text.trim(),
        'dosage': '${_dosageController.text} - ${_frequencyController.text} - ${_durationController.text}',
        'instructions': _instructionsController.text.trim(),
      });

      if (response['success'] == true) {
        _medicineController.clear();
        _dosageController.clear();
        _frequencyController.clear();
        _durationController.clear();
        _instructionsController.clear();
        _selectedPatient = null;
        _fetchData();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Prescription saved successfully'), backgroundColor: AppColors.success));
        }
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e'), backgroundColor: AppColors.error));
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  Future<void> _deletePrescription(dynamic id) async {
    try {
      final response = await _apiService.delete('prescriptions/$id');
      if (response['success'] == true) {
        _fetchData();
      }
    } catch (e) {
      debugPrint('Error deleting prescription: $e');
    }
  }

  @override
  void dispose() {
    _medicineController.dispose();
    _dosageController.dispose();
    _frequencyController.dispose();
    _durationController.dispose();
    _instructionsController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildHeader(),
          const SizedBox(height: AppSpacing.xxl),
          LayoutBuilder(
            builder: (context, constraints) {
              if (constraints.maxWidth > 800) {
                return Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(flex: 4, child: _buildNewPrescriptionForm()),
                    const SizedBox(width: AppSpacing.xl),
                    Expanded(flex: 6, child: _buildSavedPrescriptionsList()),
                  ],
                );
              }
              return Column(
                children: [
                  _buildNewPrescriptionForm(),
                  const SizedBox(height: AppSpacing.xl),
                  _buildSavedPrescriptionsList(),
                ],
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Prescriptions',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 4),
              const Text(
                'Create and manage prescriptions linked to your patients.',
                style: TextStyle(
                  fontSize: 13,
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),
        ),
        OutlinedButton(
          onPressed: widget.onBack,
          style: OutlinedButton.styleFrom(
            side: const BorderSide(color: AppColors.border),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          ),
          child: const Text(
            'Back to Dashboard',
            style: TextStyle(fontSize: 11, color: AppColors.textPrimary, fontWeight: FontWeight.w600),
          ),
        ),
      ],
    );
  }

  Widget _buildNewPrescriptionForm() {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.xl),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.add_moderator_outlined, size: 20, color: AppColors.accentLight),
              SizedBox(width: 12),
              Text('New Prescription', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
            ],
          ),
          const SizedBox(height: 24),
          _buildLabel('Patient'),
          DropdownButtonFormField<String>(
            initialValue: _selectedPatient,
            decoration: const InputDecoration(
              contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            ),
            hint: const Text('Select patient...', style: TextStyle(fontSize: 14)),
            items: const [], // Empty for mock
            onChanged: (val) => setState(() => _selectedPatient = val),
          ),
          const SizedBox(height: 16),
          _buildLabel('Medicine'),
          TextField(
            controller: _medicineController,
            decoration: const InputDecoration(
              hintText: 'Medicine name',
              contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 12),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildLabel('Dosage'),
                    TextField(
                      controller: _dosageController,
                      decoration: const InputDecoration(hintText: 'e.g. 500mg', isDense: true),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildLabel('Frequency'),
                    TextField(
                      controller: _frequencyController,
                      decoration: const InputDecoration(hintText: 'e.g. 2x/day', isDense: true),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildLabel('Duration'),
                    TextField(
                      controller: _durationController,
                      decoration: const InputDecoration(hintText: 'e.g. 7 days', isDense: true),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          _buildLabel('Instructions (optional)'),
          TextField(
            controller: _instructionsController,
            maxLines: 4,
            decoration: const InputDecoration(
              hintText: 'Take after meals, avoid alcohol, etc.',
              contentPadding: EdgeInsets.all(12),
            ),
          ),
          _buildLabel('Select Patient'),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: AppColors.border),
            ),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                isExpanded: true,
                value: _selectedPatient,
                hint: const Text('Choose a patient...', style: TextStyle(fontSize: 13)),
                items: _patients.map((p) => DropdownMenuItem(
                  value: p['name'] as String,
                  child: Text(p['name'] as String, style: const TextStyle(fontSize: 13)),
                )).toList(),
                onChanged: (val) => setState(() => _selectedPatient = val),
              ),
            ),
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _isSaving ? null : _savePrescription,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.accent,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
              child: _isSaving 
                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                : const Text('Save Prescription', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Colors.white)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSavedPrescriptionsList() {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(AppSpacing.lg),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Icon(Icons.assignment_turned_in_outlined, size: 20, color: AppColors.accentLight),
                    SizedBox(width: 12),
                    Text('Saved Prescriptions', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: AppColors.accent,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    _savedPrescriptions.length.toString(),
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.white),
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1),
          if (_isLoading)
            const Padding(padding: EdgeInsets.all(40), child: Center(child: CircularProgressIndicator()))
          else if (_savedPrescriptions.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 100),
              child: Center(
                child: Column(
                  children: [
                    Icon(Icons.assignment_outlined, size: 32, color: Colors.grey.withValues(alpha: 0.2)),
                    const SizedBox(height: 16),
                    const Text(
                      'No prescriptions yet.',
                      style: TextStyle(fontSize: 14, color: AppColors.textSecondary, fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
              ),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: _savedPrescriptions.length,
              separatorBuilder: (context, index) => const Divider(height: 1),
              itemBuilder: (context, index) {
                final presc = _savedPrescriptions[index];
                return ListTile(
                  title: Text(presc['medication'] ?? 'Medicine', style: const TextStyle(fontWeight: FontWeight.w700)),
                  subtitle: Text(
                    '${presc['patient_name']} • ${presc['dosage']}',
                    style: const TextStyle(fontSize: 12),
                  ),
                  trailing: IconButton(
                    icon: const Icon(Icons.delete_outline, color: Colors.red, size: 20),
                    onPressed: () => _deletePrescription(presc['id']),
                  ),
                  onTap: () {
                    showDialog(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        title: Text(presc['medication'] ?? 'Prescription'),
                        content: Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Patient: ${presc['patient_name']}'),
                            Text('Dosage: ${presc['dosage']}'),
                            const SizedBox(height: 8),
                            Text('Instructions: ${presc['instructions'] ?? 'None'}'),
                          ],
                        ),
                        actions: [TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Close'))],
                      ),
                    );
                  },
                );
              },
            ),
        ],
      ),
    );
  }

  Widget _buildLabel(String label) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0),
      child: Text(
        label,
        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
      ),
    );
  }
}
