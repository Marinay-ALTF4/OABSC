/// Notification model for the clinic appointment system
class NotificationModel {
  final int? id;
  final String message;
  final bool isRead;
  final DateTime? createdAt;

  NotificationModel({
    this.id,
    required this.message,
    this.isRead = false,
    this.createdAt,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: json['id'] as int?,
      message: json['message'] as String? ?? '',
      isRead: json['is_read'] == 1 || json['is_read'] == true,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'] as String)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'message': message,
      'is_read': isRead ? 1 : 0,
      'created_at': createdAt?.toIso8601String(),
    };
  }
}
