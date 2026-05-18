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

  static const _days = ['Mon','Tue','Wed','Thu','Fri','Sat'];
  static const _daysFull = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

  @override
  void initState() { super.initState(); _fetch(); }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    try {
      final r = await _api.get('admin/doctor-schedules');
      if (mounted && r['success'] == true) setState(() => _doctors = List.from(r['doctors'] ?? []));
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  Map<String, Map<String, dynamic>> _schedMap(List s) {
    final m = <String, Map<String, dynamic>>{};
    for (final e in s) {
      m[(e['day'] ?? '').toString()] = {'available': e['is_available'].toString() == '1', 'start': e['start_time'] ?? '', 'end': e['end_time'] ?? ''};
    }
    return m;
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _fetch,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Text('Availability Schedule', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
          const SizedBox(height: 4),
          const Text('Weekly availability of all registered doctors.', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
          const SizedBox(height: 14),
          // Legend
          Row(children: [
            _dot(const Color(0xFF059669), 'Available'),
            const SizedBox(width: 16),
            _dot(const Color(0xFFEF4444), 'Unavailable'),
            const SizedBox(width: 16),
            _dot(AppColors.textHint, 'Day Off'),
          ]),
          const SizedBox(height: 12),
          if (_loading) const Center(child: CircularProgressIndicator())
          else if (_doctors.isEmpty) const Center(child: Padding(padding: EdgeInsets.only(top: 48), child: Text('No doctors registered.', style: TextStyle(color: AppColors.textHint))))
          else ...[
            // Grid
            Container(
              decoration: BoxDecoration(color: AppColors.surface, borderRadius: BorderRadius.circular(10), border: Border.all(color: AppColors.border)),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Column(children: [
                  // Header
                  Container(color: const Color(0xFFF8FAFC), padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                    child: Row(children: [
                      const SizedBox(width: 160, child: Text('DOCTOR', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.textSecondary, letterSpacing: 0.5))),
                      ..._days.map((d) => SizedBox(width: 80, child: Text(d, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.textSecondary, letterSpacing: 0.5)))),
                    ]),
                  ),
                  const Divider(height: 1),
                  ..._doctors.asMap().entries.map((e) {
                    final doc = e.value as Map;
                    final sm = _schedMap(List.from(doc['schedules'] ?? []));
                    final name = (doc['name'] ?? '—').toString();
                    final spec = (doc['specialization'] ?? '').toString();
                    final init = name.isNotEmpty ? name[0].toUpperCase() : '?';
                    return Column(children: [
                      Padding(padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                        child: Row(children: [
                          SizedBox(width: 160, child: Row(children: [
                            CircleAvatar(radius: 15, backgroundColor: AppColors.iconBlueBg, child: Text(init, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.accent))),
                            const SizedBox(width: 8),
                            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              Text(name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textPrimary), overflow: TextOverflow.ellipsis),
                              if (spec.isNotEmpty) Text(spec, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary), overflow: TextOverflow.ellipsis),
                            ])),
                          ])),
                          ..._daysFull.map((day) {
                            final avail = sm[day]?['available'] == true;
                            return SizedBox(width: 80, child: Text(avail ? 'Available' : 'Day Off', style: TextStyle(fontSize: 11, color: avail ? const Color(0xFF059669) : AppColors.textHint)));
                          }),
                        ]),
                      ),
                      if (e.key < _doctors.length - 1) const Divider(height: 1),
                    ]);
                  }),
                ]),
              ),
            ),
            const SizedBox(height: 24),
            const Text('DOCTOR SCHEDULE DETAILS', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textSecondary, letterSpacing: 1.2)),
            const SizedBox(height: 12),
            // Detail cards
            // Detail cards
            LayoutBuilder(
              builder: (context, constraints) {
                // Calculate width to maximize space (2 columns on small screens, 3+ on larger)
                final crossAxisCount = constraints.maxWidth > 800 ? 4 : (constraints.maxWidth > 500 ? 3 : 2);
                final spacing = 12.0;
                final width = (constraints.maxWidth - ((crossAxisCount - 1) * spacing)) / crossAxisCount;

                return Wrap(
                  spacing: spacing,
                  runSpacing: spacing,
                  children: _doctors.map((doc) {
                    final d = doc as Map;
                    final sm = _schedMap(List.from(d['schedules'] ?? []));
                    final name = (d['name'] ?? '—').toString();
                    final spec = (d['specialization'] ?? 'Doctor').toString();
                    final phone = (d['phone'] ?? '').toString();
                    final init = name.isNotEmpty ? name[0].toUpperCase() : '?';
                    final abbr = ['Mon','Tue','Wed','Thu','Fri','Sat'];
                    final workDays = _daysFull.where((dy) => sm[dy]?['available'] == true).length;
                    final todayIdx = DateTime.now().weekday - 1;

                    return Container(
                      width: width,
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(color: AppColors.surface, borderRadius: BorderRadius.circular(10), border: Border.all(color: AppColors.border),
                        boxShadow: const [BoxShadow(color: AppColors.cardShadow, blurRadius: 6, offset: Offset(0,2))]),
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        Row(children: [
                          CircleAvatar(radius: 18, backgroundColor: AppColors.iconBlueBg, child: Text(init, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.accent))),
                          const SizedBox(width: 8),
                          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                            Text(name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.textPrimary), overflow: TextOverflow.ellipsis),
                            Text(spec, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary), overflow: TextOverflow.ellipsis),
                          ])),
                        ]),
                        const SizedBox(height: 12),
                        ..._daysFull.asMap().entries.map((e) {
                          final day = e.value;
                          final a = abbr[e.key];
                          final s = sm[day];
                          final avail = s?['available'] == true;
                          final isToday = e.key == todayIdx;
                          return Padding(padding: const EdgeInsets.symmetric(vertical: 2),
                            child: Row(children: [
                              SizedBox(width: 30, child: Text(a, style: TextStyle(fontSize: 12, fontWeight: isToday ? FontWeight.w700 : FontWeight.w400, color: isToday ? AppColors.accent : AppColors.textSecondary))),
                              Expanded(child: Text(avail ? (s?['start'] != '' ? '${s!['start']} – ${s['end']}' : 'Available') : 'Day Off',
                                style: TextStyle(fontSize: 12, color: avail ? const Color(0xFF059669) : AppColors.textHint), textAlign: TextAlign.right)),
                            ]),
                          );
                        }),
                        const SizedBox(height: 8),
                        const Divider(height: 1),
                        const SizedBox(height: 6),
                        Row(children: [
                          const Icon(Icons.calendar_today_outlined, size: 11, color: AppColors.textSecondary),
                          const SizedBox(width: 4),
                          Text('$workDays days/week', style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                          if (phone.isNotEmpty) ...[
                            const SizedBox(width: 6),
                            const Icon(Icons.phone_outlined, size: 11, color: AppColors.textSecondary),
                            const SizedBox(width: 4),
                            Expanded(child: Text(phone, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary), overflow: TextOverflow.ellipsis)),
                          ],
                        ]),
                      ]),
                    );
                  }).toList(),
                );
              }
            ),
          ],
        ]),
      ),
    );
  }

  Widget _dot(Color color, String label) => Row(children: [
    Container(width: 10, height: 10, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
    const SizedBox(width: 4),
    Text(label, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
  ]);
}
