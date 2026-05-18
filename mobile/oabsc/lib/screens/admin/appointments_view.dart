import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';

class AppointmentsView extends StatefulWidget {
  const AppointmentsView({super.key});
  @override
  State<AppointmentsView> createState() => _AppointmentsViewState();
}

class _AppointmentsViewState extends State<AppointmentsView>
    with SingleTickerProviderStateMixin {
  final _api = ApiService();
  late TabController _tabController;
  bool _loading = true;
  List _pending = [], _confirmed = [], _archived = [];
  int _totalActive = 0;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _fetch();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    try {
      final r = await _api.get('admin/appointments');
      if (mounted && r['success'] == true) {
        setState(() {
          _pending   = List.from(r['pending']   ?? []);
          _confirmed = List.from(r['confirmed'] ?? []);
          _archived  = List.from(r['archived']  ?? []);
          _totalActive = (_pending.length + _confirmed.length);
        });
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  Future<void> _updateStatus(int id, String status) async {
    try {
      await _api.post('admin/appointments/update-status',
          {'id': id, 'status': status});
      _fetch();
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Header
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(children: [
                const Icon(Icons.calendar_month_outlined,
                    size: 22, color: AppColors.accent),
                const SizedBox(width: 8),
                const Text('All Appointments',
                    style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w700,
                        color: AppColors.textPrimary)),
              ]),
              const SizedBox(height: 4),
              Text(
                '$_totalActive active appointment${_totalActive == 1 ? '' : 's'}',
                style: const TextStyle(
                    fontSize: 13, color: AppColors.accent),
              ),
              const SizedBox(height: 12),
              // Tabs
              TabBar(
                controller: _tabController,
                labelColor: AppColors.accent,
                unselectedLabelColor: AppColors.textSecondary,
                indicatorColor: AppColors.accent,
                indicatorWeight: 2.5,
                labelStyle: const TextStyle(
                    fontSize: 13, fontWeight: FontWeight.w600),
                tabs: [
                  Tab(text: 'Pending (${_pending.length})'),
                  Tab(text: 'Confirmed (${_confirmed.length})'),
                  Tab(text: 'Archive (${_archived.length})'),
                ],
              ),
              const Divider(height: 1),
            ],
          ),
        ),
        // Content
        Expanded(
          child: _loading
              ? const Center(child: CircularProgressIndicator())
              : TabBarView(
                  controller: _tabController,
                  children: [
                    _AppointmentTable(
                      rows: _pending,
                      onRefresh: _fetch,
                      onAction: _updateStatus,
                      tab: 'pending',
                    ),
                    _AppointmentTable(
                      rows: _confirmed,
                      onRefresh: _fetch,
                      onAction: _updateStatus,
                      tab: 'confirmed',
                    ),
                    _AppointmentTable(
                      rows: _archived,
                      onRefresh: _fetch,
                      onAction: _updateStatus,
                      tab: 'archived',
                    ),
                  ],
                ),
        ),
      ],
    );
  }
}

// ── Appointment table ─────────────────────────────────────────
class _AppointmentTable extends StatelessWidget {
  final List rows;
  final Future<void> Function() onRefresh;
  final Future<void> Function(int id, String status) onAction;
  final String tab;

  const _AppointmentTable({
    required this.rows,
    required this.onRefresh,
    required this.onAction,
    required this.tab,
  });

  @override
  Widget build(BuildContext context) {
    if (rows.isEmpty) {
      return RefreshIndicator(
        onRefresh: onRefresh,
        child: ListView(
          children: [
            const SizedBox(height: 60),
            Center(
              child: Text(
                'No ${tab == 'archived' ? 'archived' : tab} appointments.',
                style: const TextStyle(
                    color: AppColors.textHint, fontSize: 14),
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: onRefresh,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Table header
            Container(
              padding: const EdgeInsets.symmetric(
                  vertical: 10, horizontal: 12),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: const BorderRadius.vertical(
                    top: Radius.circular(8)),
                border: Border.all(color: AppColors.border),
              ),
              child: const Row(children: [
                _TH('#', 36),
                _TH('PATIENT', 120),
                _TH('DOCTOR', 100),
                _TH('DATE', 90),
                _TH('TIME', 70),
                _TH('STATUS', 90),
                _TH('ACTION', 100),
              ]),
            ),
            // Rows
            Container(
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: const BorderRadius.vertical(
                    bottom: Radius.circular(8)),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(
                children: rows.asMap().entries.map((e) {
                  final i = e.key;
                  final r = e.value as Map;
                  return _AppointmentRow(
                    index: i + 1,
                    row: r,
                    tab: tab,
                    onAction: onAction,
                    isLast: i == rows.length - 1,
                  );
                }).toList(),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _TH extends StatelessWidget {
  final String text;
  final double width;
  const _TH(this.text, this.width);

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: width,
      child: Text(text,
          style: const TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w700,
              color: AppColors.textSecondary,
              letterSpacing: 0.5)),
    );
  }
}

class _AppointmentRow extends StatelessWidget {
  final int index;
  final Map row;
  final String tab;
  final Future<void> Function(int id, String status) onAction;
  final bool isLast;

  const _AppointmentRow({
    required this.index,
    required this.row,
    required this.tab,
    required this.onAction,
    required this.isLast,
  });

  @override
  Widget build(BuildContext context) {
    final status = (row['status'] ?? '').toString();

    Color statusColor;
    switch (status) {
      case 'confirmed':
        statusColor = const Color(0xFF059669);
        break;
      case 'pending':
        statusColor = const Color(0xFFF59E0B);
        break;
      default:
        statusColor = AppColors.textSecondary;
    }

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(
              vertical: 10, horizontal: 12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              SizedBox(
                  width: 36,
                  child: Text('$index',
                      style: const TextStyle(
                          fontSize: 13,
                          color: AppColors.textSecondary))),
              SizedBox(
                  width: 120,
                  child: Text(
                    (row['patient_name'] ?? '—').toString(),
                    style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                        color: AppColors.textPrimary),
                    overflow: TextOverflow.ellipsis,
                  )),
              SizedBox(
                  width: 100,
                  child: Text(
                    (row['doctor_name'] ?? '—').toString(),
                    style: const TextStyle(
                        fontSize: 12,
                        color: AppColors.textSecondary),
                    overflow: TextOverflow.ellipsis,
                  )),
              SizedBox(
                  width: 90,
                  child: Text(
                    (row['appointment_date'] ?? '—').toString(),
                    style: const TextStyle(
                        fontSize: 12,
                        color: AppColors.textSecondary),
                  )),
              SizedBox(
                  width: 70,
                  child: Text(
                    _fmt(row['appointment_time']?.toString()),
                    style: const TextStyle(
                        fontSize: 12,
                        color: AppColors.textSecondary),
                  )),
              SizedBox(
                width: 90,
                child: Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    status,
                    style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: statusColor),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ),
              SizedBox(
                width: 100,
                child: tab == 'archived'
                    ? const SizedBox.shrink()
                    : _ActionMenu(
                        row: row,
                        tab: tab,
                        onAction: onAction,
                      ),
              ),
            ],
          ),
        ),
        if (!isLast)
          const Divider(height: 1, indent: 12, endIndent: 12),
      ],
    );
  }

  String _fmt(String? t) {
    if (t == null || t.isEmpty) return '—';
    final parts = t.split(':');
    if (parts.length < 2) return t;
    final h = int.tryParse(parts[0]) ?? 0;
    final m = parts[1];
    final ampm = h >= 12 ? 'PM' : 'AM';
    final h12 = h % 12 == 0 ? 12 : h % 12;
    return '$h12:$m $ampm';
  }
}

class _ActionMenu extends StatelessWidget {
  final Map row;
  final String tab;
  final Future<void> Function(int id, String status) onAction;

  const _ActionMenu(
      {required this.row,
      required this.tab,
      required this.onAction});

  @override
  Widget build(BuildContext context) {
    final id = int.tryParse(row['id']?.toString() ?? '0') ?? 0;
    final status = (row['status'] ?? '').toString();

    return PopupMenuButton<String>(
      icon: const Icon(Icons.more_horiz_rounded,
          size: 18, color: AppColors.textSecondary),
      shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8)),
      onSelected: (val) => onAction(id, val),
      itemBuilder: (_) => [
        if (status != 'confirmed')
          const PopupMenuItem(
            value: 'confirmed',
            child: Row(children: [
              Icon(Icons.check_circle_outline,
                  size: 16, color: Color(0xFF059669)),
              SizedBox(width: 8),
              Text('Confirm',
                  style: TextStyle(fontSize: 13)),
            ]),
          ),
        if (status != 'pending')
          const PopupMenuItem(
            value: 'pending',
            child: Row(children: [
              Icon(Icons.hourglass_top,
                  size: 16, color: Color(0xFFF59E0B)),
              SizedBox(width: 8),
              Text('Set Pending',
                  style: TextStyle(fontSize: 13)),
            ]),
          ),
        const PopupMenuItem(
          value: 'cancelled',
          child: Row(children: [
            Icon(Icons.cancel_outlined,
                size: 16, color: AppColors.error),
            SizedBox(width: 8),
            Text('Cancel',
                style: TextStyle(
                    fontSize: 13, color: AppColors.error)),
          ]),
        ),
      ],
    );
  }
}
