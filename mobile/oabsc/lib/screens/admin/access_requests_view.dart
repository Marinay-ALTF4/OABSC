import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';

class AccessRequestsView extends StatefulWidget {
  const AccessRequestsView({super.key});
  @override
  State<AccessRequestsView> createState() => _AccessRequestsViewState();
}

class _AccessRequestsViewState extends State<AccessRequestsView> {
  final _api = ApiService();
  bool _loading = true;
  List _pending = [], _all = [];

  @override
  void initState() {
    super.initState();
    _fetch();
  }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    try {
      final r = await _api.get('admin/access-requests');
      if (mounted && r['success'] == true) {
        setState(() {
          _pending = List.from(r['pending'] ?? []);
          _all = List.from(r['all'] ?? []);
        });
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  Future<void> _act(int id, String action) async {
    try {
      await _api.post('admin/access-requests/approve', {
        'id': id,
        'action': action,
      });
      _fetch();
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _fetch,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              children: const [
                Icon(
                  Icons.check_circle_outline_rounded,
                  size: 22,
                  color: AppColors.accent,
                ),
                SizedBox(width: 8),
                Text(
                  'Access Requests',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                    color: AppColors.textPrimary,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 4),
            const Text(
              'Review and manage pending access requests from users.',
              style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
            ),
            const SizedBox(height: 20),

            if (_loading)
              const Center(child: CircularProgressIndicator())
            else ...[
              // Pending section
              const Text(
                'PENDING REQUESTS',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textSecondary,
                  letterSpacing: 1.2,
                ),
              ),
              const SizedBox(height: 8),
              if (_pending.isEmpty)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: const Color(0xFFCCE4ED),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Text(
                    'No pending access requests.',
                    style: TextStyle(fontSize: 14, color: Color(0xFF1E5A6E)),
                  ),
                )
              else
                Column(
                  children: _pending
                      .map((r) => _PendingCard(req: r as Map, onAction: _act))
                      .toList(),
                ),

              const SizedBox(height: 24),

              // History table
              const Text(
                'REQUEST HISTORY',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textSecondary,
                  letterSpacing: 1.2,
                ),
              ),
              const SizedBox(height: 8),
              Container(
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: AppColors.border),
                ),
                child: Column(
                  children: [
                    // Header
                    Container(
                      padding: const EdgeInsets.symmetric(
                        vertical: 10,
                        horizontal: 12,
                      ),
                      decoration: const BoxDecoration(
                        color: Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.vertical(
                          top: Radius.circular(10),
                        ),
                      ),
                      child: Row(
                        children: const [
                          SizedBox(width: 36, child: Text('#', style: _hStyle)),
                          SizedBox(
                            width: 120,
                            child: Text('USER', style: _hStyle),
                          ),
                          SizedBox(
                            width: 160,
                            child: Text('EMAIL', style: _hStyle),
                          ),
                          SizedBox(
                            width: 120,
                            child: Text('RESOURCE', style: _hStyle),
                          ),
                          SizedBox(
                            width: 90,
                            child: Text('STATUS', style: _hStyle),
                          ),
                        ],
                      ),
                    ),
                    const Divider(height: 1),
                    if (_all.isEmpty)
                      const Padding(
                        padding: EdgeInsets.all(20),
                        child: Text(
                          'No requests found.',
                          style: TextStyle(
                            color: AppColors.textHint,
                            fontSize: 14,
                          ),
                        ),
                      )
                    else
                      ..._all.asMap().entries.map((e) {
                        final i = e.key;
                        final req = e.value as Map;
                        final status = (req['status'] ?? '').toString();
                        Color sc;
                        switch (status) {
                          case 'approved':
                            sc = const Color(0xFF059669);
                            break;
                          case 'rejected':
                            sc = AppColors.error;
                            break;
                          default:
                            sc = const Color(0xFFF59E0B);
                        }
                        return Column(
                          children: [
                            Padding(
                              padding: const EdgeInsets.symmetric(
                                vertical: 10,
                                horizontal: 12,
                              ),
                              child: SingleChildScrollView(
                                scrollDirection: Axis.horizontal,
                                child: Row(
                                  children: [
                                    SizedBox(
                                      width: 36,
                                      child: Text(
                                        '${i + 1}',
                                        style: const TextStyle(
                                          fontSize: 13,
                                          color: AppColors.textSecondary,
                                        ),
                                      ),
                                    ),
                                    SizedBox(
                                      width: 120,
                                      child: Text(
                                        (req['user_name'] ?? '—').toString(),
                                        style: const TextStyle(
                                          fontSize: 13,
                                          fontWeight: FontWeight.w500,
                                          color: AppColors.textPrimary,
                                        ),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                    SizedBox(
                                      width: 160,
                                      child: Text(
                                        (req['user_email'] ?? '—').toString(),
                                        style: const TextStyle(
                                          fontSize: 12,
                                          color: AppColors.textSecondary,
                                        ),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                    SizedBox(
                                      width: 120,
                                      child: Text(
                                        (req['resource'] ?? '—').toString(),
                                        style: const TextStyle(
                                          fontSize: 12,
                                          color: AppColors.accent,
                                        ),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                    SizedBox(
                                      width: 90,
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 8,
                                          vertical: 3,
                                        ),
                                        decoration: BoxDecoration(
                                          color: sc.withValues(alpha: 0.1),
                                          borderRadius: BorderRadius.circular(
                                            12,
                                          ),
                                        ),
                                        child: Text(
                                          status,
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.w600,
                                            color: sc,
                                          ),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                            if (i < _all.length - 1)
                              const Divider(
                                height: 1,
                                indent: 12,
                                endIndent: 12,
                              ),
                          ],
                        );
                      }),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  static const _hStyle = TextStyle(
    fontSize: 10,
    fontWeight: FontWeight.w700,
    color: AppColors.textSecondary,
    letterSpacing: 0.5,
  );
}

class _PendingCard extends StatelessWidget {
  final Map req;
  final Future<void> Function(int id, String action) onAction;
  const _PendingCard({required this.req, required this.onAction});

  @override
  Widget build(BuildContext context) {
    final id = int.tryParse(req['id']?.toString() ?? '0') ?? 0;
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.border),
        boxShadow: const [
          BoxShadow(
            color: AppColors.cardShadow,
            blurRadius: 6,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  (req['user_name'] ?? '—').toString(),
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: AppColors.textPrimary,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  (req['user_email'] ?? '').toString(),
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppColors.textSecondary,
                  ),
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(
                      Icons.lock_outline,
                      size: 12,
                      color: AppColors.textSecondary,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      (req['resource'] ?? '—').toString(),
                      style: const TextStyle(
                        fontSize: 12,
                        color: AppColors.accent,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          Row(
            children: [
              TextButton(
                onPressed: () => onAction(id, 'approve'),
                style: TextButton.styleFrom(
                  backgroundColor: const Color(
                    0xFF059669,
                  ).withValues(alpha: 0.1),
                  foregroundColor: const Color(0xFF059669),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(6),
                  ),
                ),
                child: const Text(
                  'Approve',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
                ),
              ),
              const SizedBox(width: 8),
              TextButton(
                onPressed: () => onAction(id, 'reject'),
                style: TextButton.styleFrom(
                  backgroundColor: AppColors.errorLight,
                  foregroundColor: AppColors.error,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(6),
                  ),
                ),
                child: const Text(
                  'Reject',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
