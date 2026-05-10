import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

class DoctorNotesView extends StatefulWidget {
  final VoidCallback onBack;

  const DoctorNotesView({super.key, required this.onBack});

  @override
  State<DoctorNotesView> createState() => _DoctorNotesViewState();
}

class _DoctorNotesViewState extends State<DoctorNotesView> {
  final _titleController = TextEditingController();
  final _noteController = TextEditingController();
  String? _selectedPatient;

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
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () {},
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.accent,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
              child: const Text('Save Note', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700)),
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
                  child: const Text(
                    '0',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.white),
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1),
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 80),
            child: Center(
              child: Column(
                children: [
                  Icon(Icons.notes_rounded, size: 28, color: Colors.grey.withValues(alpha: 0.2)),
                  const SizedBox(height: 16),
                  const Text(
                    'No notes yet.',
                    style: TextStyle(
                      fontSize: 14,
                      color: AppColors.textSecondary,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
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
