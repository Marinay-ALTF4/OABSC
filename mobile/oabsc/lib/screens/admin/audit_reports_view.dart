import 'dart:io';
import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:intl/intl.dart';
import 'package:path_provider/path_provider.dart';
import 'package:share_plus/share_plus.dart';
import '../../services/api_service.dart';

// ─── Exact colors from the web CSS ────────────────────────────
const _bg       = Color(0xFFEDF2F7);
const _white    = Colors.white;
const _navy     = Color(0xFF0F172A);
const _teal     = Color(0xFF2A6A7E);
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

class AuditReportsView extends StatefulWidget {
  const AuditReportsView({super.key});
  @override
  State<AuditReportsView> createState() => _AuditReportsViewState();
}

class _AuditReportsViewState extends State<AuditReportsView> {
  final _api = ApiService();
  bool _loading = true;
  String _filter = 'weekly';
  Map<String, dynamic> _data = {};

  @override
  void initState() { super.initState(); _fetch(); }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    try {
      final r = await _api.get('admin/audit-reports?filter=$_filter');
      if (mounted && r['success'] == true) setState(() => _data = Map.from(r));
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  int _s(String k) => int.tryParse(_data[k]?.toString() ?? '0') ?? 0;
  List _events()   => List.from(_data['events'] ?? []);
  List<String> _chartLabels()  => List<String>.from(_data['chart_labels']  ?? []);
  List<int>    _chartSuccess() => List<dynamic>.from(_data['chart_success'] ?? []).map((e) => int.tryParse(e?.toString() ?? '0') ?? 0).toList();
  List<int>    _chartFailed()  => List<dynamic>.from(_data['chart_failed']  ?? []).map((e) => int.tryParse(e?.toString() ?? '0') ?? 0).toList();

  Future<void> _exportCsv() async {
    try {
      // Build CSV locally from current data
      // Build CSV locally from current data
      final sb = StringBuffer();
      sb.writeln('AUDIT REPORT - ${_filter.toUpperCase()}');
      sb.writeln('Generated,${DateFormat('yyyy-MM-dd HH:mm:ss').format(DateTime.now())}');
      sb.writeln();
      sb.writeln('SUMMARY');
      sb.writeln('Metric,Count');
      sb.writeln('Successful Logins,${_s('total_success')}');
      sb.writeln('Failed Logins,${_s('total_failed')}');
      sb.writeln('Locked Accounts,${_s('total_locked')}');
      sb.writeln('Suspicious Activity,${_s('total_suspicious')}');
      sb.writeln('MFA Successes,${_s('total_mfa_success')}');
      sb.writeln('MFA Failures,${_s('total_mfa_failed')}');
      sb.writeln('Logouts,${_s('total_logout')}');
      sb.writeln('Active Sessions,${_s('active_sessions')}');
      sb.writeln('Security Alerts Sent,${_s('alert_count')}');
      sb.writeln();
      sb.writeln('EVENT LOG');
      sb.writeln('#,Timestamp,Event Type,User ID,Email Attempted,Reason');
      final evs = _events();
      for (int i = 0; i < evs.length; i++) {
        final e = evs[i] as Map;
        sb.writeln('${i+1},${e['created_at']??''},${e['event_type']??''},${e['user_id']??''},${e['email_attempted']??''},${e['reason_code']??''}');
      }
      final dir  = await getTemporaryDirectory();
      final file = File('${dir.path}/audit_report_${_filter}_${DateTime.now().millisecondsSinceEpoch}.csv');
      await file.writeAsString(sb.toString());
      await Share.shareXFiles([XFile(file.path)], text: 'Audit Report - ${_filter.toUpperCase()}');
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Export failed: $e')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final now = DateFormat('MMMM d, y h:mm a').format(DateTime.now());
    return Container(
      color: _bg,
      child: RefreshIndicator(
        onRefresh: _fetch,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            // ── Header row ────────────────────────────────────
            Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Row(children: const [
                  Icon(Icons.bar_chart_rounded, size: 20, color: _navy),
                  SizedBox(width: 6),
                  Text('Security Audit Reports', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: _navy)),
                ]),
                const SizedBox(height: 2),
                Text('Generated: $now · Period: ${_filter[0].toUpperCase()}${_filter.substring(1)}',
                    style: const TextStyle(fontSize: 12, color: _slate)),
              ])),
              const SizedBox(width: 8),
              // Export CSV
              ElevatedButton.icon(
                onPressed: _exportCsv,
                icon: const Icon(Icons.download_rounded, size: 14),
                label: const Text('Export CSV', style: TextStyle(fontSize: 12)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF059669),
                  foregroundColor: _white,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  elevation: 0,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
              ),
            ]),
            const SizedBox(height: 10),
            // ── Filter buttons ────────────────────────────────
            Wrap(spacing: 6, runSpacing: 6, children: [
              _FilterBtn('Daily',   'daily',   _filter, () { setState(() => _filter = 'daily');   _fetch(); }),
              _FilterBtn('Weekly',  'weekly',  _filter, () { setState(() => _filter = 'weekly');  _fetch(); }),
              _FilterBtn('Monthly', 'monthly', _filter, () { setState(() => _filter = 'monthly'); _fetch(); }),
            ]),
            const SizedBox(height: 16),

            if (_loading) const Center(child: Padding(padding: EdgeInsets.symmetric(vertical: 60), child: CircularProgressIndicator()))
            else ...[
              // ── 6 Stat cards ──────────────────────────────
              _StatCardGrid(children: [
                _AuditStatCard(icon: Icons.login_rounded,           bg: _bSuccessBg, fg: _bSuccessFg, val: _s('total_success'),    lbl: 'Successful Logins'),
                _AuditStatCard(icon: Icons.close_rounded,           bg: _bDangerBg,  fg: _bDangerFg,  val: _s('total_failed'),     lbl: 'Failed Logins'),
                _AuditStatCard(icon: Icons.lock_rounded,            bg: _bWarnBg,    fg: _bWarnFg,    val: _s('total_locked'),     lbl: 'Locked Accounts'),
                _AuditStatCard(icon: Icons.warning_amber_rounded,   bg: const Color(0xFFFFEDD5), fg: const Color(0xFFEA580C), val: _s('total_suspicious'),   lbl: 'Suspicious Activity'),
                _AuditStatCard(icon: Icons.shield_outlined,         bg: _bInfoBg,    fg: _bInfoFg,    val: _s('total_mfa_success'), lbl: 'MFA Successes'),
                _AuditStatCard(icon: Icons.people_outline_rounded,  bg: const Color(0xFFEDE9FE), fg: const Color(0xFF6D28D9), val: _s('active_sessions'),   lbl: 'Active Sessions'),
              ]),
              const SizedBox(height: 16),

              // ── Bar chart ─────────────────────────────────
              _Panel(
                header: Row(children: [
                  const Icon(Icons.bar_chart_rounded, size: 14, color: _navy),
                  const SizedBox(width: 6),
                  Text('Login Activity — ${_filter[0].toUpperCase()}${_filter.substring(1)} Breakdown',
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _navy)),
                ]),
                child: SizedBox(
                  height: 220,
                  child: _buildBarChart(),
                ),
              ),
              const SizedBox(height: 16),

              // ── MFA + Pie row ─────────────────────────────
              LayoutBuilder(builder: (ctx, bc) {
                final wide = bc.maxWidth > 500;
                final mfa  = _buildMfaPanel();
                final pie  = _buildPiePanel();
                if (wide) {
                  return IntrinsicHeight(
                    child: Row(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
                      Expanded(child: mfa),
                      const SizedBox(width: 12),
                      Expanded(child: pie),
                    ]),
                  );
                }
                return Column(children: [mfa, const SizedBox(height: 12), pie]);
              }),
              const SizedBox(height: 16),

              // ── Event log ─────────────────────────────────
              _buildEventLog(),
            ],
          ]),
        ),
      ),
    );
  }

  // ── Bar chart ──────────────────────────────────────────────
  Widget _buildBarChart() {
    final labels  = _chartLabels();
    final success = _chartSuccess();
    final failed  = _chartFailed();
    if (labels.isEmpty) return const Center(child: Text('No chart data.', style: TextStyle(color: _slate)));

    final maxY = ([...success, ...failed].fold(0, (a, b) => a > b ? a : b) + 2).toDouble();

    return Padding(
      padding: const EdgeInsets.fromLTRB(4, 12, 12, 4),
      child: BarChart(
        BarChartData(
          maxY: maxY < 2 ? 5 : maxY,
          barTouchData: BarTouchData(enabled: true),
          titlesData: FlTitlesData(
            bottomTitles: AxisTitles(sideTitles: SideTitles(
              showTitles: true, reservedSize: 28,
              getTitlesWidget: (v, _) {
                final i = v.toInt();
                if (i < 0 || i >= labels.length) return const SizedBox.shrink();
                return Padding(
                  padding: const EdgeInsets.only(top: 4),
                  child: Text(labels[i], style: const TextStyle(fontSize: 9, color: _slate)),
                );
              },
            )),
            leftTitles: AxisTitles(sideTitles: SideTitles(
              showTitles: true, reservedSize: 28,
              getTitlesWidget: (v, _) => Text(v.toInt().toString(), style: const TextStyle(fontSize: 9, color: _slate)),
            )),
            topTitles:   const AxisTitles(sideTitles: SideTitles(showTitles: false)),
            rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          ),
          gridData: FlGridData(
            show: true,
            drawVerticalLine: false,
            getDrawingHorizontalLine: (_) => FlLine(color: _border, strokeWidth: 0.8),
          ),
          borderData: FlBorderData(show: false),
          barGroups: List.generate(labels.length, (i) => BarChartGroupData(
            x: i,
            barsSpace: 4,
            barRods: [
              BarChartRodData(toY: (i < success.length ? success[i] : 0).toDouble(), color: const Color(0xFF10B981), width: 10, borderRadius: const BorderRadius.vertical(top: Radius.circular(4))),
              BarChartRodData(toY: (i < failed.length  ? failed[i]  : 0).toDouble(), color: const Color(0xFFEF4444), width: 10, borderRadius: const BorderRadius.vertical(top: Radius.circular(4))),
            ],
          )),
        ),
      ),
    );
  }

  // ── MFA panel ──────────────────────────────────────────────
  Widget _buildMfaPanel() => _Panel(
    header: Row(children: const [
      Icon(Icons.shield_outlined, size: 14, color: _navy),
      SizedBox(width: 6),
      Text('MFA Statistics', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _navy)),
    ]),
    child: Table(
      children: [
        _mfaRow('MFA Successes',       _s('total_mfa_success').toString(), const Color(0xFF059669)),
        _mfaRow('MFA Failures',        _s('total_mfa_failed').toString(),  const Color(0xFFDC2626)),
        _mfaRow('Logouts',             _s('total_logout').toString(),      _navy),
        _mfaRow('Security Alerts Sent',_s('alert_count').toString(),       const Color(0xFFD97706)),
      ],
      columnWidths: const {0: FlexColumnWidth(3), 1: FlexColumnWidth(1)},
    ),
  );

  TableRow _mfaRow(String label, String val, Color valColor) => TableRow(
    decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: Color(0xFFF1F5F9)))),
    children: [
      Padding(padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 14),
        child: Text(label, style: const TextStyle(fontSize: 13, color: _slate3))),
      Padding(padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 14),
        child: Text(val, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: valColor))),
    ],
  );

  // ── Pie/Doughnut panel ────────────────────────────────────
  Widget _buildPiePanel() {
    final vals = [
      _s('total_success').toDouble(),
      _s('total_failed').toDouble(),
      _s('total_locked').toDouble(),
      _s('total_suspicious').toDouble(),
      _s('total_mfa_success').toDouble(),
      _s('total_mfa_failed').toDouble(),
    ];
    const colors = [Color(0xFF10B981), Color(0xFFEF4444), Color(0xFFF59E0B), Color(0xFFF97316), Color(0xFF3B82F6), Color(0xFF8B5CF6)];
    const lbls   = ['Success', 'Failed', 'Locked', 'Suspicious', 'MFA OK', 'MFA Fail'];
    final total  = vals.fold(0.0, (a, b) => a + b);

    return _Panel(
      header: Row(children: const [
        Icon(Icons.pie_chart_outline_rounded, size: 14, color: _navy),
        SizedBox(width: 6),
        Text('Event Distribution', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _navy)),
      ]),
      child: Row(children: [
        SizedBox(
          width: 140, height: 140,
          child: total == 0
              ? const Center(child: Text('No data', style: TextStyle(color: _slate, fontSize: 12)))
              : PieChart(PieChartData(
                  sectionsSpace: 2,
                  centerSpaceRadius: 34,
                  sections: List.generate(vals.length, (i) => PieChartSectionData(
                    value: vals[i],
                    color: colors[i],
                    radius: 40,
                    title: '',
                    showTitle: false,
                  )),
                )),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: List.generate(lbls.length, (i) => Padding(
              padding: const EdgeInsets.symmetric(vertical: 3),
              child: Row(children: [
                Container(width: 10, height: 10, decoration: BoxDecoration(color: colors[i], shape: BoxShape.circle)),
                const SizedBox(width: 6),
                Expanded(child: Text(lbls[i], style: const TextStyle(fontSize: 11, color: _slate))),
                Text(vals[i].toInt().toString(), style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: _navy)),
              ]),
            )),
          ),
        ),
      ]),
    );
  }

  // ── Event log table ────────────────────────────────────────
  Widget _buildEventLog() {
    final evs = _events();
    return _Panel(
      header: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
        Row(children: const [
          Icon(Icons.list_alt_rounded, size: 14, color: _navy),
          SizedBox(width: 6),
          Text('Event Log (last 100 events)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _navy)),
        ]),
        Text('Since ${_fmtSince()}', style: const TextStyle(fontSize: 11, color: _slate)),
      ]),
      child: evs.isEmpty
          ? const Padding(padding: EdgeInsets.all(20), child: Text('No events recorded for this period.', style: TextStyle(color: _slate, fontSize: 13)))
          : SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: DataTable(
                headingRowHeight: 36,
                dataRowMinHeight: 36,
                dataRowMaxHeight: 44,
                horizontalMargin: 14,
                columnSpacing: 16,
                headingRowColor: WidgetStateProperty.all(_panelHdr),
                dividerThickness: 0.8,
                columns: const [
                  DataColumn(label: Text('#',              style: _thStyle)),
                  DataColumn(label: Text('TIMESTAMP',      style: _thStyle)),
                  DataColumn(label: Text('EVENT',          style: _thStyle)),
                  DataColumn(label: Text('USER ID',        style: _thStyle)),
                  DataColumn(label: Text('EMAIL ATTEMPTED',style: _thStyle)),
                  DataColumn(label: Text('REASON',         style: _thStyle)),
                ],
                rows: evs.asMap().entries.map((e) {
                  final i   = e.key;
                  final ev  = e.value as Map;
                  final typ = (ev['event_type'] ?? '').toString();
                  return DataRow(cells: [
                    DataCell(Text('${i+1}', style: const TextStyle(fontSize: 12, color: _slate))),
                    DataCell(Text((ev['created_at'] ?? '—').toString(), style: const TextStyle(fontSize: 11, color: _slate))),
                    DataCell(_EventBadge(typ)),
                    DataCell(Text((ev['user_id'] ?? '—').toString(), style: const TextStyle(fontSize: 12, color: _slate3))),
                    DataCell(ConstrainedBox(constraints: const BoxConstraints(maxWidth: 180),
                      child: Text((ev['email_attempted'] ?? '—').toString(), style: const TextStyle(fontSize: 11, color: _slate3), overflow: TextOverflow.ellipsis))),
                    DataCell(Text((ev['reason_code'] ?? '—').toString(), style: const TextStyle(fontSize: 12, color: _slate))),
                  ]);
                }).toList(),
              ),
            ),
    );
  }

  String _fmtSince() {
    final since = (_data['since'] ?? '').toString();
    if (since.isEmpty) return '—';
    try { return DateFormat('MMM d, y h:mm a').format(DateTime.parse(since)); } catch (_) { return since; }
  }

  static const _thStyle = TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: _slate, letterSpacing: 0.5);
}

// ── Reusable panel widget (ar-panel) ─────────────────────────
class _Panel extends StatelessWidget {
  final Widget header;
  final Widget child;
  const _Panel({required this.header, required this.child});

  @override
  Widget build(BuildContext context) => Container(
    decoration: BoxDecoration(color: _white, borderRadius: BorderRadius.circular(14), border: Border.all(color: _border),
      boxShadow: const [BoxShadow(color: Color(0x0D0F172A), blurRadius: 4, offset: Offset(0, 1))]),
    clipBehavior: Clip.hardEdge,
    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
        color: _panelHdr,
        child: header,
      ),
      const Divider(height: 1, color: Color(0xFFF1F5F9)),
      Padding(padding: const EdgeInsets.all(12), child: child),
    ]),
  );
}

// ── 2×3 grid of stat cards ────────────────────────────────────
class _StatCardGrid extends StatelessWidget {
  final List<Widget> children;
  const _StatCardGrid({required this.children});

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(builder: (ctx, bc) {
      final cols = bc.maxWidth > 420 ? 3 : 2;
      final gap  = 10.0;
      final w    = (bc.maxWidth - gap * (cols - 1)) / cols;
      return Wrap(spacing: gap, runSpacing: gap,
        children: children.map((c) => SizedBox(width: w, child: c)).toList());
    });
  }
}

// ── Single stat card (ar-card) ────────────────────────────────
class _AuditStatCard extends StatelessWidget {
  final IconData icon;
  final Color bg, fg;
  final int val;
  final String lbl;
  const _AuditStatCard({required this.icon, required this.bg, required this.fg, required this.val, required this.lbl});

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.all(14),
    decoration: BoxDecoration(color: _white, borderRadius: BorderRadius.circular(14), border: Border.all(color: _border),
      boxShadow: const [BoxShadow(color: Color(0x0D0F172A), blurRadius: 4, offset: Offset(0, 1))]),
    child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
      Container(width: 38, height: 38, decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(10)),
        child: Icon(icon, size: 18, color: fg)),
      const SizedBox(height: 8),
      Text('$val', style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w700, color: _navy, height: 1)),
      const SizedBox(height: 4),
      Text(lbl, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: _slate, letterSpacing: 0.8), maxLines: 2, overflow: TextOverflow.ellipsis),
    ]),
  );
}

// ── Filter button ─────────────────────────────────────────────
class _FilterBtn extends StatelessWidget {
  final String label, value, current;
  final VoidCallback onTap;
  const _FilterBtn(this.label, this.value, this.current, this.onTap);

  @override
  Widget build(BuildContext context) {
    final active = value == current;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
        decoration: BoxDecoration(
          color: active ? _teal : _white,
          border: Border.all(color: active ? _teal : _border),
          borderRadius: BorderRadius.circular(7),
        ),
        child: Text(label, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: active ? _white : _slate)),
      ),
    );
  }
}

// ── Event type badge ──────────────────────────────────────────
class _EventBadge extends StatelessWidget {
  final String type;
  const _EventBadge(this.type);

  @override
  Widget build(BuildContext context) {
    Color bg, fg;
    if (type.contains('success') || type == 'logout') { bg = _bSuccessBg; fg = _bSuccessFg; }
    else if (type.contains('fail'))                    { bg = _bDangerBg;  fg = _bDangerFg;  }
    else if (type.contains('lock') || type.contains('suspicious')) { bg = _bWarnBg; fg = _bWarnFg; }
    else if (type.contains('modified') || type.contains('deleted') || type.contains('restored')) { bg = _bInfoBg; fg = _bInfoFg; }
    else                                               { bg = _bSecBg;    fg = _bSecFg;     }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(999)),
      child: Text(type.toUpperCase(), style: TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: fg, letterSpacing: 0.5)),
    );
  }
}
