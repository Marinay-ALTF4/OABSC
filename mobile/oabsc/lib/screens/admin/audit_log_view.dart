import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../services/api_service.dart';

// Colors from web
const _bg       = Color(0xFFEDF2F7);
const _white    = Colors.white;
const _navy     = Color(0xFF0F172A);
const _slate    = Color(0xFF64748B);
const _slate3   = Color(0xFF334155);
const _border   = Color(0xFFE2E8F0);
const _panelHdr = Color(0xFFF8FAFC);

// badge bg / text
const _bSuccessBg = Color(0xFFD1FAE5); const _bSuccessFg = Color(0xFF065F46);
const _bDangerBg  = Color(0xFFFEE2E2); const _bDangerFg  = Color(0xFFDC2626);
const _bWarnBg    = Color(0xFFFEF3C7); const _bWarnFg    = Color(0xFFD97706);
const _bSecBg     = Color(0xFFF1F5F9); const _bSecFg     = Color(0xFF475569);
const _bInfoBg    = Color(0xFFDBEAFE); const _bInfoFg    = Color(0xFF1E40AF);

class AuditLogView extends StatefulWidget {
  const AuditLogView({super.key});
  @override
  State<AuditLogView> createState() => _AuditLogViewState();
}

class _AuditLogViewState extends State<AuditLogView> {
  final _api = ApiService();
  bool _loading = true;
  Map<String, dynamic> _data = {};

  @override
  void initState() { super.initState(); _fetch(); }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    try {
      final r = await _api.get('admin/audit-log');
      if (mounted && r['success'] == true) setState(() => _data = Map.from(r));
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  int _success7d() {
    final s = _data['summary'] as List?;
    if (s == null) return 0;
    for (final e in s) {
      if (e['event_type'] == 'login_success') return int.tryParse(e['count']?.toString() ?? '0') ?? 0;
    }
    return 0;
  }

  @override
  Widget build(BuildContext context) {
    final now = DateFormat('MMMM d, yyyy').format(DateTime.now());
    
    return Container(
      color: _bg,
      child: RefreshIndicator(
        onRefresh: _fetch,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            // ── Header ────────────────────────────────────────
            Row(children: const [
              Icon(Icons.security_rounded, size: 20, color: _navy),
              SizedBox(width: 8),
              Text('Security Audit Log', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: _navy)),
            ]),
            const SizedBox(height: 2),
            Text('Last 7 days summary · $now', style: const TextStyle(fontSize: 12, color: _slate)),
            const SizedBox(height: 16),

            if (_loading) const Center(child: Padding(padding: EdgeInsets.all(40), child: CircularProgressIndicator()))
            else ...[
              // ── 4 Stat cards ──────────────────────────────
              LayoutBuilder(builder: (ctx, bc) {
                final w = (bc.maxWidth - 36) / 4;
                final cw = w < 120 ? (bc.maxWidth - 12) / 2 : w;
                return Wrap(spacing: 12, runSpacing: 12, children: [
                  SizedBox(width: cw, child: _StatCard(
                    icon: Icons.login_rounded, bg: const Color(0xFFE0E7FF), fg: const Color(0xFF4F46E5),
                    val: _success7d(), lbl: 'SUCCESSFUL LOGINS (7D)')),
                  SizedBox(width: cw, child: _StatCard(
                    icon: Icons.close_rounded, bg: _bDangerBg, fg: _bDangerFg,
                    val: int.tryParse(_data['failed24']?.toString() ?? '') ?? 0, lbl: 'FAILED LOGINS (24H)')),
                  SizedBox(width: cw, child: _StatCard(
                    icon: Icons.warning_amber_rounded, bg: _bWarnBg, fg: _bWarnFg,
                    val: int.tryParse(_data['suspicious']?.toString() ?? '') ?? 0, lbl: 'SUSPICIOUS (24H)')),
                  SizedBox(width: cw, child: _StatCard(
                    icon: Icons.people_outline_rounded, bg: _bSuccessBg, fg: _bSuccessFg,
                    val: (_data['sessions'] as List?)?.length ?? 0, lbl: 'ACTIVE SESSIONS (8H)')),
                ]);
              }),
              const SizedBox(height: 20),

              // ── Active Sessions ──────────────────────────────
              _Panel(
                header: Row(children: const [
                  Icon(Icons.people_outline_rounded, size: 14, color: _navy),
                  SizedBox(width: 6),
                  Text('Active Sessions (last 8 hours)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _navy)),
                ]),
                child: _buildSessionsTable(),
              ),
              const SizedBox(height: 20),

              // ── Recent Activity ──────────────────────────────
              _Panel(
                header: Row(children: const [
                  Icon(Icons.list_alt_rounded, size: 14, color: _navy),
                  SizedBox(width: 6),
                  Text('Recent Activity Log (last 200 events)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _navy)),
                ]),
                child: _buildActivityTable(),
              ),
            ],
          ]),
        ),
      ),
    );
  }

  Widget _buildSessionsTable() {
    final List sessions = _data['sessions'] ?? [];
    if (sessions.isEmpty) return const Padding(padding: EdgeInsets.all(20), child: Text('No active sessions.', style: TextStyle(color: _slate, fontSize: 13)));

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: DataTable(
        headingRowHeight: 36,
        dataRowMinHeight: 36,
        dataRowMaxHeight: 44,
        horizontalMargin: 14,
        columnSpacing: 24,
        headingRowColor: WidgetStateProperty.all(_panelHdr),
        dividerThickness: 0.8,
        columns: const [
          DataColumn(label: Text('#',          style: _thStyle)),
          DataColumn(label: Text('NAME',       style: _thStyle)),
          DataColumn(label: Text('EMAIL',      style: _thStyle)),
          DataColumn(label: Text('ROLE',       style: _thStyle)),
          DataColumn(label: Text('LAST LOGIN', style: _thStyle)),
        ],
        rows: sessions.asMap().entries.map((e) {
          final i = e.key;
          final s = e.value as Map;
          return DataRow(cells: [
            DataCell(Text('${i+1}', style: const TextStyle(fontSize: 12, color: _slate))),
            DataCell(Text((s['name'] ?? '—').toString(), style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: _slate3))),
            DataCell(Text((s['email'] ?? '—').toString(), style: const TextStyle(fontSize: 12, color: _slate))),
            DataCell(_RoleBadge((s['role'] ?? '').toString())),
            DataCell(Text((s['last_login_at'] ?? '—').toString(), style: const TextStyle(fontSize: 11, color: _slate))),
          ]);
        }).toList(),
      ),
    );
  }

  Widget _buildActivityTable() {
    final List events = _data['events'] ?? [];
    if (events.isEmpty) return const Padding(padding: EdgeInsets.all(20), child: Text('No recent activity.', style: TextStyle(color: _slate, fontSize: 13)));

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: DataTable(
        headingRowHeight: 36,
        dataRowMinHeight: 36,
        dataRowMaxHeight: 44,
        horizontalMargin: 14,
        columnSpacing: 24,
        headingRowColor: WidgetStateProperty.all(_panelHdr),
        dividerThickness: 0.8,
        columns: const [
          DataColumn(label: Text('#',               style: _thStyle)),
          DataColumn(label: Text('TIME',            style: _thStyle)),
          DataColumn(label: Text('EVENT',           style: _thStyle)),
          DataColumn(label: Text('USER ID',         style: _thStyle)),
          DataColumn(label: Text('EMAIL ATTEMPTED', style: _thStyle)),
          DataColumn(label: Text('REASON',          style: _thStyle)),
        ],
        rows: events.asMap().entries.map((e) {
          final i = e.key;
          final ev = e.value as Map;
          return DataRow(cells: [
            DataCell(Text('${i+1}', style: const TextStyle(fontSize: 12, color: _slate))),
            DataCell(Text((ev['created_at'] ?? '—').toString(), style: const TextStyle(fontSize: 11, color: _slate))),
            DataCell(_EventBadge((ev['event_type'] ?? '').toString())),
            DataCell(Text((ev['user_id'] ?? '—').toString(), style: const TextStyle(fontSize: 12, color: _slate3))),
            DataCell(Text((ev['email_attempted'] ?? '—').toString(), style: const TextStyle(fontSize: 11, color: _slate))),
            DataCell(Text((ev['reason_code'] ?? '—').toString(), style: const TextStyle(fontSize: 12, color: _slate))),
          ]);
        }).toList(),
      ),
    );
  }
}

const _thStyle = TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Color(0xFF64748B), letterSpacing: 0.5);

class _Panel extends StatelessWidget {
  final Widget header;
  final Widget child;
  const _Panel({required this.header, required this.child});

  @override
  Widget build(BuildContext context) => Container(
    decoration: BoxDecoration(color: _white, borderRadius: BorderRadius.circular(10), border: Border.all(color: _border),
      boxShadow: const [BoxShadow(color: Color(0x050F172A), blurRadius: 4, offset: Offset(0, 1))]),
    clipBehavior: Clip.hardEdge,
    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Container(width: double.infinity, padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16), color: _panelHdr, child: header),
      const Divider(height: 1, color: Color(0xFFF1F5F9)),
      Padding(padding: const EdgeInsets.all(8), child: child),
    ]),
  );
}

class _StatCard extends StatelessWidget {
  final IconData icon;
  final Color bg, fg;
  final int val;
  final String lbl;
  const _StatCard({required this.icon, required this.bg, required this.fg, required this.val, required this.lbl});

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.all(14),
    decoration: BoxDecoration(color: _white, borderRadius: BorderRadius.circular(10), border: Border.all(color: _border),
      boxShadow: const [BoxShadow(color: Color(0x050F172A), blurRadius: 4, offset: Offset(0, 1))]),
    child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
      Container(width: 32, height: 32, decoration: BoxDecoration(color: bg, shape: BoxShape.circle),
        child: Icon(icon, size: 16, color: fg)),
      const SizedBox(height: 12),
      Text('$val', style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w700, color: _navy, height: 1)),
      const SizedBox(height: 4),
      Text(lbl, style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w600, color: _slate, letterSpacing: 0.8), maxLines: 2, overflow: TextOverflow.ellipsis),
    ]),
  );
}

class _EventBadge extends StatelessWidget {
  final String type;
  const _EventBadge(this.type);
  @override
  Widget build(BuildContext context) {
    Color bg, fg;
    if (type.contains('success') || type == 'logout') { bg = _bSuccessBg; fg = _bSuccessFg; }
    else if (type.contains('fail'))                    { bg = _bDangerBg;  fg = _bDangerFg;  }
    else if (type.contains('lock') || type.contains('suspicious')) { bg = _bWarnBg; fg = _bWarnFg; }
    else                                               { bg = _bSecBg;    fg = _bSecFg;     }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(999)),
      child: Text(type.toUpperCase(), style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: fg, letterSpacing: 0.5)),
    );
  }
}

class _RoleBadge extends StatelessWidget {
  final String role;
  const _RoleBadge(this.role);
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(color: _bInfoBg, borderRadius: BorderRadius.circular(999)),
      child: Text(role.toUpperCase(), style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: _bInfoFg, letterSpacing: 0.5)),
    );
  }
}
