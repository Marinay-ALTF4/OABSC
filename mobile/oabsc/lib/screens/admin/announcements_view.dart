import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../services/api_service.dart';
import '../../services/auth_service.dart';

class AnnouncementsView extends StatefulWidget {
  const AnnouncementsView({super.key});
  @override
  State<AnnouncementsView> createState() => _AnnouncementsViewState();
}

class _AnnouncementsViewState extends State<AnnouncementsView> {
  final _api = ApiService();
  final _auth = AuthService();
  bool _loading = true;
  List _announcements = [];

  final _titleCtrl = TextEditingController();
  final _contentCtrl = TextEditingController();
  bool _posting = false;

  @override
  void initState() { super.initState(); _fetch(); }

  @override
  void dispose() { _titleCtrl.dispose(); _contentCtrl.dispose(); super.dispose(); }

  Future<void> _fetch() async {
    setState(() => _loading = true);
    try {
      final r = await _api.get('admin/announcements');
      if (mounted && r['success'] == true) setState(() => _announcements = List.from(r['announcements'] ?? []));
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  Future<void> _post() async {
    final title = _titleCtrl.text.trim();
    final content = _contentCtrl.text.trim();
    if (title.isEmpty || content.isEmpty) return;
    setState(() => _posting = true);
    try {
      final uid = await _auth.getSavedUserId();
      await _api.post('admin/announcements', body: {'title': title, 'content': content, 'user_id': uid ?? 0});
      _titleCtrl.clear();
      _contentCtrl.clear();
      if (mounted) Navigator.pop(context);
      _fetch();
    } catch (_) {}
    if (mounted) setState(() => _posting = false);
  }

  Future<void> _delete(int id) async {
    try {
      await _api.delete('admin/announcements/$id');
      _fetch();
    } catch (_) {}
  }

  void _showNewDialog() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
        child: Container(
          padding: const EdgeInsets.all(20),
          decoration: const BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [
              const Expanded(child: Text('New Announcement', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700, color: AppColors.textPrimary))),
              IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(context), color: AppColors.textSecondary),
            ]),
            const SizedBox(height: 12),
            TextField(
              controller: _titleCtrl,
              decoration: const InputDecoration(labelText: 'Title', hintText: 'Announcement title'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _contentCtrl,
              maxLines: 4,
              decoration: const InputDecoration(labelText: 'Content', hintText: 'Write your announcement...', alignLabelWithHint: true),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _posting ? null : _post,
                icon: _posting ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Icon(Icons.send_rounded, size: 16),
                label: Text(_posting ? 'Posting...' : 'Post Announcement'),
              ),
            ),
          ]),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Column(children: [
      // Header
      Padding(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
        child: Row(children: [
          const Icon(Icons.campaign_outlined, size: 22, color: AppColors.accent),
          const SizedBox(width: 8),
          const Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('Announcements', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
            Text('Post and manage clinic announcements for all users.', style: TextStyle(fontSize: 12, color: AppColors.textSecondary)),
          ])),
          ElevatedButton.icon(
            onPressed: _showNewDialog,
            icon: const Icon(Icons.add, size: 16),
            label: const Text('New Announcement'),
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              textStyle: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
            ),
          ),
        ]),
      ),
      const SizedBox(height: 12),
      Expanded(
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _announcements.isEmpty
                ? Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                    Icon(Icons.campaign_outlined, size: 60, color: AppColors.textHint.withValues(alpha: 0.4)),
                    const SizedBox(height: 12),
                    const Text('No announcements yet', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: AppColors.textSecondary)),
                    const SizedBox(height: 4),
                    const Text('Click "New Announcement" to post one.', style: TextStyle(fontSize: 13, color: AppColors.textHint)),
                  ]))
                : RefreshIndicator(
                    onRefresh: _fetch,
                    child: ListView.separated(
                      padding: const EdgeInsets.all(16),
                      physics: const AlwaysScrollableScrollPhysics(),
                      itemCount: _announcements.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 10),
                      itemBuilder: (_, i) {
                        final a = _announcements[i] as Map;
                        final id = int.tryParse(a['id']?.toString() ?? '0') ?? 0;
                        return Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: AppColors.surface,
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: AppColors.border),
                            boxShadow: const [BoxShadow(color: AppColors.cardShadow, blurRadius: 6, offset: Offset(0,2))],
                          ),
                          child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                            Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(color: AppColors.iconBlueBg, borderRadius: BorderRadius.circular(8)),
                              child: const Icon(Icons.campaign_outlined, size: 20, color: AppColors.accent),
                            ),
                            const SizedBox(width: 12),
                            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              Text((a['title'] ?? '').toString(), style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                              const SizedBox(height: 4),
                              Text((a['content'] ?? '').toString(), style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                              const SizedBox(height: 8),
                              Row(children: [
                                const Icon(Icons.person_outline, size: 12, color: AppColors.textHint),
                                const SizedBox(width: 4),
                                Text((a['created_by_name'] ?? 'Admin').toString(), style: const TextStyle(fontSize: 11, color: AppColors.textHint)),
                                const SizedBox(width: 12),
                                const Icon(Icons.schedule_outlined, size: 12, color: AppColors.textHint),
                                const SizedBox(width: 4),
                                Text(_fmt(a['created_at']?.toString()), style: const TextStyle(fontSize: 11, color: AppColors.textHint)),
                              ]),
                            ])),
                            IconButton(
                              icon: const Icon(Icons.delete_outline_rounded, size: 18, color: AppColors.error),
                              onPressed: () => _confirmDelete(context, id),
                              splashRadius: 18,
                            ),
                          ]),
                        );
                      },
                    ),
                  ),
      ),
    ]);
  }

  void _confirmDelete(BuildContext ctx, int id) {
    showDialog(context: ctx, builder: (_) => AlertDialog(
      title: const Text('Delete Announcement'),
      content: const Text('Are you sure you want to delete this announcement?'),
      actions: [
        TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
        TextButton(
          onPressed: () { Navigator.pop(ctx); _delete(id); },
          child: const Text('Delete', style: TextStyle(color: AppColors.error)),
        ),
      ],
    ));
  }

  String _fmt(String? dt) {
    if (dt == null || dt.isEmpty) return '—';
    try {
      final d = DateTime.parse(dt);
      return '${d.month}/${d.day}/${d.year}';
    } catch (_) { return dt; }
  }
}
