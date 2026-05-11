import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';

class DoctorNotesView extends StatefulWidget {
  final VoidCallback onBack;

  const DoctorNotesView({super.key, required this.onBack});

  @override
  State<DoctorNotesView> createState() => _DoctorNotesViewState();
}

class _DoctorNotesViewState extends State<DoctorNotesView> {
  final ApiService _apiService = ApiService();
  final AuthService _authService = AuthService();
  final _titleController = TextEditingController();
  final _noteController = TextEditingController();
  String? _selectedPatient;
  List<Map<String, dynamic>> _savedNotes = [];
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
      
      // Fetch notes
      final notesResp = await _apiService.get('notes?user_id=$userId');
      if (notesResp['success'] == true) {
        _savedNotes = List<Map<String, dynamic>>.from(notesResp['notes'] ?? []);
      }

      // Fetch patients for dropdown
      final patientsResp = await _apiService.get('patients');
      if (patientsResp['success'] == true) {
        _patients = List<Map<String, dynamic>>.from(patientsResp['patients'] ?? []);
      }
    } catch (e) {
      debugPrint('Error fetching notes: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _saveNote() async {
    if (_titleController.text.isEmpty || _noteController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please fill in all fields')));
      return;
    }

    setState(() => _isSaving = true);
    try {
      final userId = await _authService.getSavedUserId();
      final response = await _apiService.post('notes', body: {
        'doctor_id': userId,
        'patient_name': _selectedPatient,
        'title': _titleController.text.trim(),
        'content': _noteController.text.trim(),
      });

      if (response['success'] == true) {
        _titleController.clear();
        _noteController.clear();
        _selectedPatient = null;
        _fetchData();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Note saved successfully'), backgroundColor: AppColors.success));
        }
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e'), backgroundColor: AppColors.error));
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  Future<void> _deleteNote(dynamic id) async {
    try {
      final response = await _apiService.delete('notes/$id');
      if (response['success'] == true) {
        _fetchData();
      }
    } catch (e) {
      debugPrint('Error deleting note: $e');
    }
  }

  @override
  void dispose() {
    _titleController.dispose();
    _noteController.dispose();
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
                    Expanded(flex: 4, child: _buildNewNoteForm()),
                    const SizedBox(width: AppSpacing.xl),
                    Expanded(flex: 6, child: _buildSavedNotesList()),
                  ],
                );
              }
              return Column(
                children: [
                  _buildNewNoteForm(),
                  const SizedBox(height: AppSpacing.xl),
                  _buildSavedNotesList(),
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
                'Write Notes',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 4),
              const Text(
                'Create private doctor notes and tag them to your patients.',
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

  Widget _buildNewNoteForm() {
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
              Icon(Icons.add_box_outlined, size: 20, color: AppColors.accentLight),
              SizedBox(width: 12),
              Text('New Note', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
            ],
          ),
          const SizedBox(height: 24),
          _buildLabel('Title'),
          TextField(
            controller: _titleController,
            decoration: const InputDecoration(
              hintText: 'Note title',
              contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 12),
            ),
          ),
          const SizedBox(height: 16),
          _buildLabel('Patient (optional)'),
          DropdownButtonFormField<String>(
            initialValue: _selectedPatient,
            decoration: const InputDecoration(
              contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            ),
            hint: const Text('No specific patient', style: TextStyle(fontSize: 14)),
            items: const [], // Empty for mock
            onChanged: (val) => setState(() => _selectedPatient = val),
          ),
          const SizedBox(height: 16),
          _buildLabel('Note'),
          TextField(
            controller: _noteController,
            maxLines: 8,
            decoration: const InputDecoration(
              hintText: 'Enter note content...',
              contentPadding: EdgeInsets.all(12),
            ),
          ),
          _buildLabel('Select Patient (Optional)'),
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
              onPressed: _isSaving ? null : _saveNote,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.accent,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
              child: _isSaving 
                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                : const Text('Save Note', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Colors.white)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSavedNotesList() {
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
                    Icon(Icons.notes_rounded, size: 20, color: AppColors.accentLight),
                    SizedBox(width: 12),
                    Text('Saved Notes', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: AppColors.accent,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    _savedNotes.length.toString(),
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.white),
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1),
          if (_isLoading)
            const Padding(padding: EdgeInsets.all(40), child: Center(child: CircularProgressIndicator()))
          else if (_savedNotes.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 80),
              child: Center(
                child: Column(
                  children: [
                    Icon(Icons.notes_rounded, size: 28, color: Colors.grey.withValues(alpha: 0.2)),
                    const SizedBox(height: 16),
                    const Text(
                      'No notes yet.',
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
              itemCount: _savedNotes.length,
              separatorBuilder: (context, index) => const Divider(height: 1),
              itemBuilder: (context, index) {
                final note = _savedNotes[index];
                return ListTile(
                  title: Text(note['title'] ?? 'Untitled Note', style: const TextStyle(fontWeight: FontWeight.w700)),
                  subtitle: Text(
                    '${note['patient_name'] ?? 'General'} • ${note['created_at']?.split(' ')[0] ?? ''}',
                    style: const TextStyle(fontSize: 12),
                  ),
                  trailing: IconButton(
                    icon: const Icon(Icons.delete_outline, color: Colors.red, size: 20),
                    onPressed: () => _deleteNote(note['id']),
                  ),
                  onTap: () {
                    showDialog(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        title: Text(note['title'] ?? 'Note'),
                        content: Text(note['content'] ?? ''),
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
