import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';

class DoctorSchedulesView extends StatefulWidget {
  const DoctorSchedulesView({super.key});

  @override
  State<DoctorSchedulesView> createState() => _DoctorSchedulesViewState();
}

class _DoctorSchedulesViewState extends State<DoctorSchedulesView> {
  final _api = ApiService();
  bool _loading = true;
  List _doctors = [];

  static const _days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  static const _daysFull = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

  @override
  void initState() {
    super.initState();
    _fetch();
  }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    try {
      final r = await _api.get('admin/doctor-schedules');
      if (mounted && r['success'] == true) {
        setState(() => _doctors = List.from(r['doctors'] ?? []));
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  Map<String, Map<String, dynamic>> _schedMap(List s) {
    final m = <String, Map<String, dynamic>>{};
    for (final e in s) {
      m[(e['day'] ?? '').toString()] = {
        'available': e['is_available'].toString() == '1',
        'start': e['start_time'] ?? '',
        'end': e['end_time'] ?? ''
      };
    }
    return m;
  }

  Widget _dot(Color color, String label) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(width: 8, height: 8, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
        const SizedBox(width: 6),
        Text(label, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary, fontWeight: FontWeight.w500)),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _fetch,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header section
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: const Color(0xFFE6F7EE),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(
                    Icons.schedule_rounded,
                    color: Color(0xFF166534),
                    size: 20,
                  ),
                ),
                const SizedBox(width: 12),
                const Text(
                  'Doctor Schedules',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF166534),
                  ),
                ),
              ],
            ),
            const SizedBox(height: AppSpacing.xl),

            // Legend
            Row(
              children: [
                _dot(const Color(0xFF059669), 'Available'),
                const SizedBox(width: 16),
                _dot(const Color(0xFFEF4444), 'Unavailable'),
                const SizedBox(width: 16),
                _dot(AppColors.textHint, 'Day Off'),
              ],
            ),
            const SizedBox(height: 16),

            if (_loading)
              const Center(child: Padding(padding: EdgeInsets.symmetric(vertical: 40), child: CircularProgressIndicator()))
            else if (_doctors.isEmpty)
              const Center(
                child: Padding(
                  padding: EdgeInsets.only(top: 48),
                  child: Text('No doctors registered.', style: TextStyle(color: AppColors.textHint)),
                ),
              )
            else ...[
              // Grid Container
              Container(
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: AppColors.border),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.02),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Column(
                    children: [
                      // Header
                      Container(
                        color: const Color(0xFFF0FDF4),
                        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                        child: Row(
                          children: [
                            const SizedBox(
                              width: 160,
                              child: Text(
                                'DOCTOR',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w700,
                                  color: Color(0xFF166534),
                                  letterSpacing: 0.5,
                                ),
                              ),
                            ),
                            ..._days.map((d) => SizedBox(
                                  width: 80,
                                  child: Text(
                                    d,
                                    style: const TextStyle(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w700,
                                      color: Color(0xFF166534),
                                      letterSpacing: 0.5,
                                    ),
                                  ),
                                )),
                          ],
                        ),
                      ),
                      const Divider(height: 1),
                      // Rows
                      ..._doctors.asMap().entries.map((e) {
                        final idx = e.key;
                        final doc = e.value as Map;
                        final sm = _schedMap(List.from(doc['schedules'] ?? []));
                        final name = (doc['name'] ?? '—').toString();
                        final spec = (doc['specialization'] ?? '').toString();
                        final init = name.isNotEmpty ? name[0].toUpperCase() : '?';

                        return Column(
                          children: [
                            Padding(
                              padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                              child: Row(
                                children: [
                                  SizedBox(
                                    width: 160,
                                    child: Row(
                                      children: [
                                        CircleAvatar(
                                          radius: 15,
                                          backgroundColor: const Color(0xFFE6F7EE),
                                          child: Text(
                                            init,
                                            style: const TextStyle(
                                              fontSize: 12,
                                              fontWeight: FontWeight.w700,
                                              color: Color(0xFF166534),
                                            ),
                                          ),
                                        ),
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                name,
                                                style: const TextStyle(
                                                  fontSize: 13,
                                                  fontWeight: FontWeight.w600,
                                                  color: AppColors.textPrimary,
                                                ),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                              if (spec.isNotEmpty)
                                                Text(
                                                  spec,
                                                  style: const TextStyle(
                                                    fontSize: 11,
                                                    color: AppColors.textSecondary,
                                                  ),
                                                  overflow: TextOverflow.ellipsis,
                                                ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  ..._daysFull.map((day) {
                                    final avail = sm[day]?['available'] == true;
                                    return SizedBox(
                                      width: 80,
                                      child: Text(
                                        avail ? 'Available' : 'Day Off',
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: avail ? const Color(0xFF059669) : AppColors.textHint,
                                          fontWeight: avail ? FontWeight.w600 : FontWeight.normal,
                                        ),
                                      ),
                                    );
                                  }),
                                ],
                              ),
                            ),
                            if (idx < _doctors.length - 1) const Divider(height: 1),
                          ],
                        );
                      }),
                    ],
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
