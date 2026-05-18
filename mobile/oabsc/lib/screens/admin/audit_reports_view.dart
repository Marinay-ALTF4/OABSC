import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';

class AuditReportsView extends StatefulWidget {
  const AuditReportsView({super.key});
  @override
  State<AuditReportsView> createState() => _AuditReportsViewState();
}

class _AuditReportsViewState extends State<AuditReportsView> {
  final _api = ApiService();
  bool _loading = true;
  Map _stats = {};
  List _events = [];

  @override
  void initState() { super.initState(); _fetch(); }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    try {
      final r = await _api.get('admin/audit-reports');
      if (mounted && r['success'] == true) {
        setState(() {
          _stats = Map.from(r);
          _events = List.from(r['events'] ?? []);
        });
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _fetch,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          // Header
          Row(children: const [
            Icon(Icons.bar_chart_rounded, size: 22, color: AppColors.accent),
            SizedBox(width: 8),
            Text('Security Audit Reports', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
          ]),
          const SizedBox(height: 4),
          Text('Generated: ${_fmtNow()} · Period: Weekly', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
          const SizedBox(height: 16),

          if (_loading) const Center(child: CircularProgressIndicator())
          else ...[
            // Stat cards
            Wrap(spacing: 10, runSpacing: 10,
              children: [
                _AuditCard(icon: Icons.login_rounded, color: const Color(0xFF059669), label: 'SUCCESSFUL LOGINS',   value: _stats['successful_logins']?.toString()  ?? '0'),
                _AuditCard(icon: Icons.close_rounded,  color: AppColors.error,          label: 'FAILED LOGINS',       value: _stats['failed_logins']?.toString()      ?? '0'),
                _AuditCard(icon: Icons.lock_rounded,   color: const Color(0xFFF59E0B), label: 'LOCKED ACCOUNTS',     value: _stats['locked_accounts']?.toString()    ?? '0'),
                _AuditCard(icon: Icons.warning_amber_rounded, color: AppColors.error,  label: 'SUSPICIOUS ACTIVITY', value: _stats['suspicious_activity']?.toString() ?? '0'),
                _AuditCard(icon: Icons.verified_rounded, color: const Color(0xFF2563EB), label: 'MFA SUCCESSES',     value: _stats['mfa_successes']?.toString()      ?? '0'),
                _AuditCard(icon: Icons.people_outline, color: const Color(0xFF7C3AED), label: 'ACTIVE SESSIONS',     value: _stats['active_sessions']?.toString()    ?? '0'),
              ],
            ),
            const SizedBox(height: 24),

            // Event log table
            Row(children: [
              const Expanded(child: Text('Event Log (last 100 events)', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.textPrimary))),
              Text(_fmtSince(), style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
            ]),
            const SizedBox(height: 8),
            Container(
              decoration: BoxDecoration(color: AppColors.surface, borderRadius: BorderRadius.circular(10), border: Border.all(color: AppColors.border)),
              child: Column(children: [
                // Header
                Container(
                  padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                  decoration: const BoxDecoration(color: Color(0xFFF8FAFC), borderRadius: BorderRadius.vertical(top: Radius.circular(10))),
                  child: Row(children: const [
                    SizedBox(width: 36, child: Text('#', style: _hStyle)),
                    SizedBox(width: 150, child: Text('TIMESTAMP', style: _hStyle)),
                    SizedBox(width: 160, child: Text('EVENT', style: _hStyle)),
                    SizedBox(width: 70, child: Text('USER ID', style: _hStyle)),
                    SizedBox(width: 160, child: Text('EMAIL ATTEMPTED', style: _hStyle)),
                  ]),
                ),
                const Divider(height: 1),
                if (_events.isEmpty)
                  const Padding(padding: EdgeInsets.all(20), child: Text('No events found.', style: TextStyle(color: AppColors.textHint, fontSize: 14)))
                else
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Column(children: _events.asMap().entries.map((e) {
                      final i = e.key;
                      final ev = e.value as Map;
                      final evType = (ev['event_type'] ?? '').toString();
                      Color evColor;
                      if (evType.contains('success') || evType.contains('login_success')) evColor = const Color(0xFF059669);
                      else if (evType.contains('fail') || evType.contains('lock')) evColor = AppColors.error;
                      else evColor = AppColors.textSecondary;

                      return Column(children: [
                        Padding(padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
                          child: Row(children: [
                            SizedBox(width: 36, child: Text('${i+1}', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary))),
                            SizedBox(width: 150, child: Text(_fmtDt(ev['created_at']?.toString()), style: const TextStyle(fontSize: 12, color: AppColors.textSecondary))),
                            SizedBox(width: 160, child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(color: evColor.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
                              child: Text(evType.toUpperCase(), style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: evColor), overflow: TextOverflow.ellipsis),
                            )),
                            SizedBox(width: 70, child: Text((ev['user_id'] ?? '—').toString(), style: const TextStyle(fontSize: 12, color: AppColors.textSecondary))),
                            SizedBox(width: 160, child: Text((ev['email_attempted'] ?? '—').toString(), style: const TextStyle(fontSize: 11, color: AppColors.textSecondary), overflow: TextOverflow.ellipsis)),
                          ]),
                        ),
                        if (i < _events.length - 1) const Divider(height: 1, indent: 12, endIndent: 12),
                      ]);
                    }).toList()),
                  ),
              ]),
            ),
          ],
        ]),
      ),
    );
  }

  String _fmtNow() {
    final n = DateTime.now();
    return '${n.month}/${n.day}/${n.year}';
  }

  String _fmtSince() {
    final n = DateTime.now().subtract(const Duration(days: 7));
    return 'Since ${n.month}/${n.day}/${n.year}';
  }

  String _fmtDt(String? dt) {
    if (dt == null || dt.isEmpty) return '—';
    try { return dt.replaceFirst('T', ' ').substring(0, 19); } catch (_) { return dt; }
  }

  static const _hStyle = TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.textSecondary, letterSpacing: 0.5);
}

class _AuditCard extends StatelessWidget {
  final IconData icon;
  final Color color;
  final String label;
  final String value;
  const _AuditCard({required this.icon, required this.color, required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(builder: (ctx, _) {
      final w = (MediaQuery.of(ctx).size.width - 16*2 - 10*2) / 3;
      return Container(
        width: w < 90 ? 90 : w,
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface, borderRadius: BorderRadius.circular(10),
          border: Border.all(color: AppColors.border),
          boxShadow: const [BoxShadow(color: AppColors.cardShadow, blurRadius: 6, offset: Offset(0,2))],
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Container(padding: const EdgeInsets.all(8), decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)),
            child: Icon(icon, size: 20, color: color)),
          const SizedBox(height: 10),
          Text(value, style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.textPrimary)),
          const SizedBox(height: 4),
          Text(label, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.textSecondary, letterSpacing: 0.5), maxLines: 2, overflow: TextOverflow.ellipsis),
        ]),
      );
    });
  }
}
