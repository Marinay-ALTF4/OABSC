import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';
import '../../widgets/stat_card.dart';

class DoctorSchedulesView extends StatefulWidget {
  const DoctorSchedulesView({super.key});
  @override
  State<DoctorSchedulesView> createState() => _DoctorSchedulesViewState();
}

class _DoctorSchedulesViewState extends State<DoctorSchedulesView> {
  final _api = ApiService();
  bool _loading = true;
  List _doctors = [];

  static const _days = ['MON','TUE','WED','THU','FRI','SAT','SUN'];
  static const _daysFull = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

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

  void _showDoctorSchedule(BuildContext context, Map doc) {
    final sm = _schedMap(List.from(doc['schedules'] ?? []));
    final name = (doc['name'] ?? '—').toString();
    final spec = (doc['specialization'] ?? '').toString();
    final phone = (doc['phone'] ?? '').toString();
    final init = name.isNotEmpty ? name.split(' ').map((e) => e[0]).take(2).join().toUpperCase() : '?';
    final workDays = _daysFull.where((dy) => sm[dy]?['available'] == true).length;

    showDialog(
      context: context,
      builder: (context) => Dialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        child: Container(
          width: 400,
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  CircleAvatar(
                    radius: 20,
                    backgroundColor: const Color(0xFFFEE2E2), // Light red as in screenshot
                    child: Text(init, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFFEF4444))),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(name, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                        Text(spec, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, size: 20, color: AppColors.textSecondary),
                    onPressed: () => Navigator.pop(context),
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              const Text('WEEKLY SCHEDULE', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.textSecondary, letterSpacing: 1.0)),
              const SizedBox(height: 12),
              // Schedule List
              ..._daysFull.map((day) {
                final s = sm[day];
                final avail = s?['available'] == true;
                return Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(day, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.textPrimary)),
                      if (avail)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(color: const Color(0xFFD1FAE5), borderRadius: BorderRadius.circular(4)), // Light green
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.center,
                            children: [
                              const Text('Available', style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: Color(0xFF059669))),
                              if (s!['start'] != '') Text('${s['start']} - ${s['end']}', style: const TextStyle(fontSize: 9, color: Color(0xFF059669))),
                            ],
                          ),
                        )
                      else
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(4)), // Light gray
                          child: const Text('Day Off', style: TextStyle(fontSize: 10, color: AppColors.textHint)),
                        )
                    ],
                  ),
                );
              }),
              const SizedBox(height: 12),
              const Divider(height: 1),
              const SizedBox(height: 16),
              // Footer info
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(color: const Color(0xFFEFF6FF), borderRadius: BorderRadius.circular(4)),
                    child: Row(
                      children: [
                        const Icon(Icons.calendar_today_outlined, size: 12, color: AppColors.primary),
                        const SizedBox(width: 4),
                        Text('$workDays days/week', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.primary)),
                      ],
                    ),
                  ),
                  if (phone.isNotEmpty) ...[
                    const SizedBox(width: 12),
                    const Icon(Icons.phone_outlined, size: 12, color: AppColors.textSecondary),
                    const SizedBox(width: 4),
                    Text(phone, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                  ],
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    int totalDocs = _doctors.length;
    int docsWithSched = 0;
    int totalAvailDays = 0;

    for (var doc in _doctors) {
      final sm = _schedMap(List.from(doc['schedules'] ?? []));
      int availDays = _daysFull.where((dy) => sm[dy]?['available'] == true).length;
      if (availDays > 0) docsWithSched++;
      totalAvailDays += availDays;
    }
    
    String avgDays = totalDocs > 0 ? (totalAvailDays / totalDocs).toStringAsFixed(1) : '0';
    if (avgDays.endsWith('.0')) avgDays = avgDays.substring(0, avgDays.length - 2);

    return RefreshIndicator(
      onRefresh: _fetch,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(
            children: [
              const Icon(Icons.calendar_today_outlined, size: 22, color: AppColors.accent),
              const SizedBox(width: 8),
              const Text('Doctor Schedules', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
            ],
          ),
          const SizedBox(height: 4),
          const Text('Weekly availability of all registered doctors.', style: TextStyle(fontSize: 13, color: AppColors.textSecondary)),
          const SizedBox(height: 24),
          
          // Stat cards
          LayoutBuilder(builder: (context, constraints) {
            final cols = constraints.maxWidth > 800 ? 4 : (constraints.maxWidth > 500 ? 2 : 1);
            final w = (constraints.maxWidth - (cols - 1) * 16) / cols;
            return Wrap(spacing: 16, runSpacing: 16, children: [
              SizedBox(width: w, child: StatCard(icon: Icons.people_alt, iconColor: AppColors.primary, iconBgColor: const Color(0xFFEFF6FF), count: '$totalDocs', label: 'Total Doctors')),
              SizedBox(width: w, child: StatCard(icon: Icons.check_circle_outline, iconColor: const Color(0xFF059669), iconBgColor: const Color(0xFFD1FAE5), count: '$docsWithSched', label: 'With Schedule Set')),
              SizedBox(width: w, child: StatCard(icon: Icons.access_time, iconColor: const Color(0xFF7C3AED), iconBgColor: const Color(0xFFEDE9FE), count: '$totalAvailDays', label: 'Total Available Days')),
              SizedBox(width: w, child: StatCard(icon: Icons.bar_chart, iconColor: const Color(0xFFD97706), iconBgColor: const Color(0xFFFEF3C7), count: avgDays, label: 'Avg Days / Doctor')),
            ]);
          }),
          const SizedBox(height: 24),

          // Legend
          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              _dot(const Color(0xFF059669), 'Available'),
              const SizedBox(width: 16),
              _dot(const Color(0xFFEF4444), 'Unavailable'),
              const SizedBox(width: 16),
              _dot(AppColors.textHint, 'Day Off'),
            ]
          ),
          const SizedBox(height: 12),

          if (_loading) const Center(child: CircularProgressIndicator())
          else if (_doctors.isEmpty) const Center(child: Padding(padding: EdgeInsets.only(top: 48), child: Text('No doctors registered.', style: TextStyle(color: AppColors.textHint))))
          else ...[
            // Grid
            Container(
              decoration: BoxDecoration(color: AppColors.surface, borderRadius: BorderRadius.circular(10), border: Border.all(color: AppColors.border)),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                  // Header
                  Container(color: const Color(0xFFF8FAFC), padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                    child: Row(children: [
                      const SizedBox(width: 200, child: Text('DOCTOR', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.textSecondary, letterSpacing: 1.0))),
                      ..._days.map((d) => SizedBox(width: 100, child: Text(d, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.textSecondary, letterSpacing: 1.0)))),
                      const SizedBox(width: 80, child: Text('', style: TextStyle(fontSize: 10))), // Action col
                    ]),
                  ),
                  const Divider(height: 1),
                  ..._doctors.asMap().entries.map((e) {
                    final doc = e.value as Map;
                    final sm = _schedMap(List.from(doc['schedules'] ?? []));
                    final name = (doc['name'] ?? '—').toString();
                    final spec = (doc['specialization'] ?? '').toString();
                    final init = name.isNotEmpty ? name.split(' ').map((e) => e[0]).take(2).join().toUpperCase() : '?';
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                      Padding(padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                        child: Row(
                          children: [
                          SizedBox(width: 200, child: Row(children: [
                            CircleAvatar(radius: 16, backgroundColor: const Color(0xFFFEE2E2), child: Text(init, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Color(0xFFEF4444)))),
                            const SizedBox(width: 10),
                            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              Text(name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textPrimary), overflow: TextOverflow.ellipsis),
                              if (spec.isNotEmpty) Text(spec, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary), overflow: TextOverflow.ellipsis),
                            ])),
                          ])),
                          ..._daysFull.map((day) {
                            final avail = sm[day]?['available'] == true;
                            if (avail) {
                              return SizedBox(width: 100, child: Align(
                                alignment: Alignment.centerLeft,
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
                                  decoration: BoxDecoration(color: const Color(0xFFD1FAE5), borderRadius: BorderRadius.circular(4)),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.center,
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Text('Available', style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: Color(0xFF059669))),
                                      if (sm[day]!['start'] != '') Text('${sm[day]!['start']} - ${sm[day]!['end']}', style: const TextStyle(fontSize: 8, color: Color(0xFF059669))),
                                    ],
                                  ),
                                ),
                              ));
                            } else {
                              return SizedBox(width: 100, child: Align(
                                alignment: Alignment.centerLeft,
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(color: const Color(0xFFF1F5F9), borderRadius: BorderRadius.circular(12)),
                                  child: const Text('Day Off', style: TextStyle(fontSize: 10, color: AppColors.textHint)),
                                ),
                              ));
                            }
                          }),
                          SizedBox(width: 80, child: Align(
                            alignment: Alignment.centerRight,
                            child: OutlinedButton(
                              onPressed: () => _showDoctorSchedule(context, doc),
                              style: OutlinedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                minimumSize: const Size(0, 32),
                                side: const BorderSide(color: AppColors.primary),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.arrow_forward, size: 12, color: AppColors.primary),
                                  SizedBox(width: 4),
                                  Text('View', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.primary)),
                                ],
                              ),
                            ),
                          )),
                        ]),
                      ),
                      if (e.key < _doctors.length - 1) const Divider(height: 1),
                    ]);
                  }),
                ]),
              ),
            ),
          ],
        ]),
      ),
    );
  }

  Widget _dot(Color color, String label) => Row(children: [
    Container(width: 8, height: 8, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
    const SizedBox(width: 6),
    Text(label, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.textSecondary)),
  ]);
}
