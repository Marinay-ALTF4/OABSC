import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

/// Notification & Alerts section widget matching the website's notification area
class NotificationSection extends StatelessWidget {
  final List<Map<String, dynamic>> notifications;
  final VoidCallback? onMarkAllRead;
  final Function(int)? onDelete;
  final Function(Map<String, dynamic>)? onView;

  const NotificationSection({
    super.key,
    this.notifications = const [],
    this.onMarkAllRead,
    this.onDelete,
    this.onView,
  });

  String _formatDate(String? dateStr) {
    if (dateStr == null || dateStr.isEmpty) return 'Just now';
    try {
      final dt = DateTime.parse(dateStr).toLocal();
      final months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      final m = months[dt.month - 1];
      final d = dt.day;
      final hr = dt.hour == 0 ? 12 : (dt.hour > 12 ? dt.hour - 12 : dt.hour);
      final min = dt.minute.toString().padLeft(2, '0');
      final ampm = dt.hour >= 12 ? 'PM' : 'AM';
      return '$m $d, $hr:$min $ampm';
    } catch (_) {
      return dateStr;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Header
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Notifications',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w700,
                color: AppColors.textPrimary,
              ),
            ),
            GestureDetector(
              onTap: onMarkAllRead,
              child: const Text(
                'Mark all read',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF3B82F6),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: AppSpacing.sm),
        
        if (notifications.isEmpty) ...[
          const SizedBox(height: AppSpacing.lg),
          const Text(
            'No notifications right now',
            style: TextStyle(fontSize: 13, color: AppColors.textHint),
          ),
        ],

        // Notifications list
        ...notifications.map((notif) {
          final id = notif['id'] is int ? notif['id'] as int : int.tryParse(notif['id']?.toString() ?? '0') ?? 0;
          final title = (notif['title'] ?? 'Notification').toString();
          final body = (notif['body'] ?? '').toString();
          final type = (notif['type'] ?? 'info').toString();
          final dateStr = (notif['created_at'] ?? '').toString();
          final isReadRaw = notif['is_read'];
          final isRead = isReadRaw == 1 || isReadRaw == '1' || isReadRaw == true;
          
          IconData iconData = Icons.info_outline;
          Color iconColor = const Color(0xFF3B82F6); // Blue
          if (type == 'appointment') {
             iconData = Icons.event_available;
             iconColor = const Color(0xFF10B981); // Emerald green
          }

          return Container(
            padding: const EdgeInsets.symmetric(vertical: 12),
            decoration: const BoxDecoration(
              border: Border(bottom: BorderSide(color: AppColors.border, width: 0.5)),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 36,
                  height: 36,
                  decoration: BoxDecoration(
                    color: iconColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(iconData, color: iconColor, size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Flexible(child: Text(title, style: TextStyle(fontWeight: isRead ? FontWeight.w500 : FontWeight.w700, fontSize: 13, color: AppColors.textPrimary))),
                          if (!isRead) ...[
                            const SizedBox(width: 6),
                            Container(width: 6, height: 6, decoration: const BoxDecoration(color: Colors.blue, shape: BoxShape.circle)),
                          ],
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(body, style: TextStyle(fontSize: 12, color: isRead ? AppColors.textSecondary : AppColors.textPrimary), maxLines: 2, overflow: TextOverflow.ellipsis),
                      const SizedBox(height: 4),
                      Text(_formatDate(dateStr), style: const TextStyle(fontSize: 11, color: AppColors.textHint)),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    GestureDetector(
                      onTap: () => onView?.call(notif),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: const Color(0xFF4F46E5), // Indigo blue
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: const Text('View', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600)),
                      ),
                    ),
                    const SizedBox(width: 6),
                    GestureDetector(
                      onTap: () => onDelete?.call(id),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF43F5E), // Rose red
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: const Icon(Icons.delete_outline, color: Colors.white, size: 15),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        }),
      ],
    );
  }
}
