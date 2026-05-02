/// User model for the clinic appointment system
class UserModel {
  final int? id;
  final String name;
  final String email;
  final String role;
  final String? clinicAccessCode;
  final String? token;

  UserModel({
    this.id,
    required this.name,
    required this.email,
    required this.role,
    this.clinicAccessCode,
    this.token,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] as int?,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      role: json['role'] as String? ?? '',
      clinicAccessCode: json['clinic_access_code'] as String?,
      token: json['token'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'clinic_access_code': clinicAccessCode,
      'token': token,
    };
  }
}
