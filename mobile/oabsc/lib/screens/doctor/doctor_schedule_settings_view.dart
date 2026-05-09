import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

class DoctorScheduleSettingsView extends StatefulWidget {
  final VoidCallback onBack;

  const DoctorScheduleSettingsView({super.key, required this.onBack});

  @override
  State<DoctorScheduleSettingsView> createState() => _DoctorScheduleSettingsViewState();
}

class _DoctorScheduleSettingsViewState extends State<DoctorScheduleSettingsView> {
  final List<String> _days = [
    'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
  ];

  late List<Map<String, dynamic>> _schedule;

  @override
  void initState() {
    super.initState();
    // Initialize with default values
    _schedule = _days.map((day) => {
      'day': day,
      'enabled': false,
      'startTime': const TimeOfDay(hour: 8, minute: 0),
      'endTime': const TimeOfDay(hour: 17, minute: 0),
    }).toList();
  }

  Future<void> _selectTime(BuildContext context, int index, bool isStartTime) async {
    final TimeOfDay? picked = await showTimePicker(
      context: context,
      initialTime: isStartTime ? _schedule[index]['startTime'] : _schedule[index]['endTime'],
    );
    if (picked != null) {
      setState(() {
        if (isStartTime) {
          _schedule[index]['startTime'] = picked;
        } else {
          _schedule[index]['endTime'] = picked;
        }
      });
    }
  }

  String _formatTime(TimeOfDay time) {
    final hour = time.hourOfPeriod == 0 ? 12 : time.hourOfPeriod;
    final minute = time.minute.toString().padLeft(2, '0');
    final period = time.period == DayPeriod.am ? 'am' : 'pm';
    return '${hour.toString().padLeft(2, '0')}:$minute $period';
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          _buildHeader(),
          const SizedBox(height: AppSpacing.xxl),
          Container(
            constraints: const BoxConstraints(maxWidth: 600),
            padding: const EdgeInsets.all(AppSpacing.xl),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.border),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.02),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                ...List.generate(_schedule.length, (index) => _buildDayRow(index)),
                const SizedBox(height: AppSpacing.xl),
                ElevatedButton(
                  onPressed: () {},
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF2563EB),
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  child: const Text('Save Schedule', style: TextStyle(fontWeight: FontWeight.w700)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      constraints: const BoxConstraints(maxWidth: 600),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'My Schedule Settings',
                  style: TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                    color: AppColors.textPrimary,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Set your available days and hours for appointments.',
                  style: TextStyle(
                    fontSize: 13,
                    color: AppColors.textSecondary.withValues(alpha: 0.8),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: AppSpacing.md),
          OutlinedButton(
            onPressed: widget.onBack,
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              minimumSize: Size.zero,
              side: const BorderSide(color: AppColors.border),
            ),
            child: const Text('Back', style: TextStyle(fontSize: 12, color: AppColors.textPrimary)),
          ),
        ],
      ),
    );
  }

  Widget _buildDayRow(int index) {
    final dayData = _schedule[index];
    final bool isEnabled = dayData['enabled'];

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: AppColors.border.withValues(alpha: 0.5)),
      ),
      child: LayoutBuilder(
        builder: (context, constraints) {
          final bool isVeryNarrow = constraints.maxWidth < 320;
          
          if (isVeryNarrow) {
            return Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    _buildCheckbox(index, isEnabled),
                    const SizedBox(width: 4),
                    Text(
                      dayData['day'],
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                        color: isEnabled ? AppColors.textPrimary : AppColors.textHint,
                      ),
                    ),
                  ],
                ),
                if (isEnabled) ...[
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const SizedBox(width: 32),
                      _buildTimePicker(index, true),
                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 4),
                        child: Text('to', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                      ),
                      _buildTimePicker(index, false),
                    ],
                  ),
                ],
              ],
            );
          }

          return Row(
            children: [
              _buildCheckbox(index, isEnabled),
              const SizedBox(width: 4),
              Expanded(
                flex: 3,
                child: Text(
                  dayData['day'],
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: isEnabled ? AppColors.textPrimary : AppColors.textHint,
                  ),
                ),
              ),
              Expanded(
                flex: 7,
                child: Opacity(
                  opacity: isEnabled ? 1.0 : 0.5,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      _buildTimePicker(index, true),
                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 6),
                        child: Text('to', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                      ),
                      _buildTimePicker(index, false),
                    ],
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildCheckbox(int index, bool isEnabled) {
    return Transform.scale(
      scale: 0.9,
      child: Checkbox(
        value: isEnabled,
        onChanged: (val) => setState(() => _schedule[index]['enabled'] = val),
        activeColor: const Color(0xFF2563EB),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
        visualDensity: VisualDensity.compact,
      ),
    );
  }

  Widget _buildTimePicker(int index, bool isStartTime) {
    final time = isStartTime ? _schedule[index]['startTime'] : _schedule[index]['endTime'];
    final bool isEnabled = _schedule[index]['enabled'];

    return InkWell(
      onTap: isEnabled ? () => _selectTime(context, index, isStartTime) : null,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(4),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              _formatTime(time),
              style: const TextStyle(fontSize: 11, color: AppColors.textPrimary),
            ),
            const SizedBox(width: 2),
            const Icon(Icons.access_time, size: 12, color: AppColors.textSecondary),
          ],
        ),
      ),
    );
  }
}
